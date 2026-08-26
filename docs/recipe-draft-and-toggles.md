# Stuffing Production Orders — Test / Live Toggles

How to run the stuffing production order generation against test data, and how to
move it to live when it is signed off.

There are **two independent switches**. They are separate on purpose: you can read
test recipes while writing nowhere near BC, and you can prove the write path
separately from the recipe data.

| Switch | Question it answers | Values |
|---|---|---|
| `PRODUCTION_ORDERS_RECIPE_TABLE` | Which recipes do we generate **from**? | `RecipeData` (live) / `recipe_data_draft` (test) |
| `PRODUCTION_ORDERS_TARGET` | Where do generated orders get **written**? | `local` (safe) / `production_data` (BC) |

Both live in `.env`. Neither requires a code change.

---

## 1. The switches

### `PRODUCTION_ORDERS_RECIPE_TABLE` — the recipe source

```dotenv
# Test: read the editable draft copy
PRODUCTION_ORDERS_RECIPE_TABLE=recipe_data_draft

# Live: read the real recipe table
PRODUCTION_ORDERS_RECIPE_TABLE=RecipeData
```

Config: `config/production_orders.php`. Read by `SausageController::recipeTable()`, which
feeds every recipe lookup the generation makes — the packing-route graph, the
packed-item dropdown on the stuffing screen, and the recipe lines each order is
built from.

`RECIPE_DATA_TABLE` remains as a legacy fallback, but new deployments should use
`PRODUCTION_ORDERS_RECIPE_TABLE` so the recipe source for this function is clearly
separate from the production-order write target.

`RecipeData` is **never written to** by this feature under either setting.

### `PRODUCTION_ORDERS_TARGET` — the write target

```dotenv
# Test: write only to our own generated_production_orders table
PRODUCTION_ORDERS_TARGET=local

# Live: also insert into the BC ProductionData table
PRODUCTION_ORDERS_TARGET=production_data
PRODUCTION_DATA_TABLE=ProductionData
```

Config: `config/production_orders.php`.

`generated_production_orders` is written in **both** modes — it holds the link back
to the weighing (`idt_transfer_id`, `weighed_item`, `packed_item`, `step`) that
ProductionData has no columns for, and it is what the on-screen panel reads.

---

## 2. Applying a change

The recipe graph is cached for 10 hours, so a switch is not live until the caches
are dropped:

```bash
php artisan config:clear
php artisan cache:clear
```

On IIS, recycle the app pool afterwards if `config:cache` is in use.

> Editing recipes through the **Recipe Data (Draft)** screen clears these caches
> automatically. Only a `.env` change needs the commands above.

---

## 3. The four combinations

| `PRODUCTION_ORDERS_RECIPE_TABLE` | `PRODUCTION_ORDERS_TARGET` | What it is for |
|---|---|---|
| `recipe_data_draft` | `local` | **Full sandbox.** Test recipes, nothing reaches BC. Start here. |
| `recipe_data_draft` | `production_data` | Proving the BC write path with test recipes. Writes real rows to BC — use deliberately. |
| `RecipeData` | `local` | Live recipes, no BC write. Good final rehearsal before go-live. |
| `RecipeData` | `production_data` | **Go-live.** |

---

## 4. The draft recipe table

`recipe_data_draft` mirrors `RecipeData` column for column, including which columns
allow blanks — so anything valid in the draft is valid in live.

Screen: **Data Management → Recipe Data (Draft)** on the Sausage menu
(`/recipe-draft`). The banner at the top always states which table weighings are
currently reading, so it is never a guess.

### What the screen does

| Action | Effect |
|---|---|
| **New Line / edit / delete** | Single-row CRUD against the draft. |
| **Copy from Live** | Replaces the whole draft with a fresh copy of `RecipeData`. |
| **Download Excel** | Exports what the current filters select, in the layout the upload reads. |
| **Upload Excel** | Loads a sheet into the draft — *Replace* or *Merge*. |
| **Clear Draft** | Empties the draft table. |

None of these touch `RecipeData`.

### Filling the draft

The draft is filled from live by `App\Services\RecipeDraftReplicator`, which is the
single implementation behind all three ways of triggering it:

| Trigger | When | Behaviour |
|---|---|---|
| Migration | Automatically, as `recipe_data_draft` is created | Fills it only if empty |
| `php artisan recipes:replicate-draft` | On demand | Replaces the draft, prompting first |
| **Copy from Live** button | On demand | Replaces the draft |

```bash
# Replace the draft with a fresh copy of live (prompts if the draft has rows)
php artisan recipes:replicate-draft

# Never discard work in progress - copies only when the draft is empty
php artisan recipes:replicate-draft --if-empty

# No prompt, for unattended deploys
php artisan recipes:replicate-draft --force
```

The copy runs in a transaction, so a failure part way through cannot leave the
draft holding half a recipe set. Recipe caches are cleared automatically
afterwards. `RecipeData` is only ever read.

> **On deployment the draft fills itself.** `php artisan migrate` creates the table
> and immediately seeds it from live — around 11 seconds for 4,674 lines — printing
> `Seeded recipe_data_draft with N line(s) from RecipeData.` No second command is
> needed.
>
> The seed is guarded on both sides: it only runs when the draft is empty, and a
> failure is logged rather than thrown, so a data problem can never fail the schema
> migration. If it is skipped for any reason, run
> `php artisan recipes:replicate-draft` afterwards.

### Excel round trip

Download → edit in Excel → upload. Two layouts are accepted, detected from the
header row:

- **With `ID` first column** (what Download produces). In *Merge* mode the ID
  matches rows back to existing lines, so you can change a handful of rows and
  upload just those.
- **Starting at `Process`** — a plain `RecipeData`-shaped sheet. Every row is
  treated as new.

Column order:

```
ID | Process | Output Item | Recipe | Output Item Description | Output Item UOM |
Batch Size | Output Item Location | Input Item | Input Item Description |
Input Item UOM | Input Item Qty Per | Input Item Location | Process Code |
No Series | Routing
```

**Upload modes**

- **Replace** — clears the draft, then loads the sheet.
- **Merge** — updates rows by ID, inserts the rest. Rows without an ID are added.

**Validation** — the whole upload is rejected, and *nothing* is written, if any row
has a blank `Process`. Rows with a blank Output or Input Item are loaded but
reported as a warning: live itself carries eight such rows (the deboning step
definitions), so a faithful copy of live has to be allowed through.

**What the round trip normalises** — verified against all 4,674 live rows:

- Trailing spaces are trimmed from descriptions.
- The literal text `NULL` becomes a real null.
- Nothing else changes. Codes, quantities, locations and recipes come back
  identical, and a second download/upload produces no further change.

> Codes are written to the sheet as **text** deliberately. Left to Excel's own type
> detection, a recipe like `1230E05` is read as scientific notation and returns as
> `123000000` — silently destroying the code. Keep them as text if you rebuild a
> sheet by hand.

---

## 5. Going live

1. Confirm sign-off against `PRODUCTION_ORDERS_RECIPE_TABLE=RecipeData` +
   `PRODUCTION_ORDERS_TARGET=local`, so live recipes are proven before anything
   reaches BC.
2. Apply the migrations intended for this release — see the release checklist in
   `docs/change-request-stuffing-production-orders.md`.
3. Set both switches to live values in the server `.env`:
   ```dotenv
   PRODUCTION_ORDERS_RECIPE_TABLE=RecipeData
   PRODUCTION_ORDERS_TARGET=production_data
   PRODUCTION_DATA_TABLE=ProductionData
   ```
4. `php artisan config:clear && php artisan cache:clear`, then recycle the app pool.
5. Weigh one item and confirm the order appears both in the on-screen panel and in
   `ProductionData`.

### Rolling back

Set `PRODUCTION_ORDERS_TARGET=local` and clear the caches. Generation continues into
`generated_production_orders` and stops reaching BC. No code change, no deployment.

The draft table can be left in place — it costs nothing when
`PRODUCTION_ORDERS_RECIPE_TABLE=RecipeData`.

---

## 6. Where to look when something is wrong

Generation runs **after** the response is sent, so the operator never waits for it
and nothing about it appears on screen. Results go to the log:

```
storage/logs/laravel.log
```

- `Stuffing production orders: N production order(s) generated, M lines` — success.
- `Stuffing production orders: No recipe route from X to Y - skipped.` — the item
  had no recipe path to the chosen packed item.
- `Stuffing production order generation failed: ...` — an exception; the weight was
  still saved.
- `Recipe {code} has no usable quantity for {item}` — a gap in the recipe data; no
  orders were written for that weighing.

Orders are also visible on the stuffing screen under **Generated Production
Orders** (last 2 days), as one flat table with filters for order no, process,
packed item, line type and status.

---

## 7. Catching up weighings that never got orders

Generation runs after the response is sent, so a weighing can end up with no orders
— the feature was switched off at the time, a recipe was missing, or the BC write
failed. `stuffing:backfill-orders` finds those and generates them after the fact.

```bash
# See what is missing without writing anything
php artisan stuffing:backfill-orders --from=2026-08-10 --dry-run

# Catch up a single day
php artisan stuffing:backfill-orders --from=2026-08-10 --to=2026-08-10

# Catch up from a date to today
php artisan stuffing:backfill-orders --from=2026-08-10
```

Both dates default to today. The command prints which recipe table and which write
target are in effect before it starts, and asks for confirmation when the target is
`production_data` — pass `--force` to skip the prompt in a scheduled run.

**It only ever adds.** Weighings that already have orders are filtered out, and
`generateProductionOrders` guards on the same condition, so running it twice over
the same range is a no-op rather than a source of duplicates.

Backfilled orders are stamped with the **time of the weighing**, not the time of the
run. An order caught up today for a weighing taken on the 10th carries the 10th as
its `transaction_date` and `created_at`, so it lands in the right period in the
export and in BC's `TransactionDate` — and it will *not* appear in the stuffing
screen's Generated Production Orders panel, which only shows the last 2 days. Use
the export to confirm a backfill landed.

### Exit codes

`0` when everything generated or was legitimately skipped, `1` when any weighing
threw. Failures are logged individually to `laravel.log` with their transfer id.

### A note on range size

`idt_transfers` holds ~1.3M rows. Until the
`2026_08_21_090000_add_lookup_indexes_to_idt_transfers_table` migration is applied,
any date range wider than a couple of days causes a full table scan and the command
will appear to hang. Apply that migration first — see the deployment note below.

---

## 8. Deploying the idt_transfers index migration

`2026_08_21_090000_add_lookup_indexes_to_idt_transfers_table` adds two indexes to
`idt_transfers`, which had only its clustered primary key despite being filtered by
date on the stuffing panel, the per-batch report and the IDT dashboards. Those
screens have been scanning all 1.3M rows on every load; this is what fixes that.

**Run it in a maintenance window, not during production.** The server is SQL Server
**Standard Edition**, where `CREATE INDEX` is offline only — it takes a schema
modification lock on `idt_transfers` for the whole build, blocking every read and
write to the table. On a running line that stalls the stuffing scale.

**Budget around 7-8 minutes.** It took 440 seconds against a 1.3M row copy on a
local SQL Server; live will differ with disk and load, but that is the order of
magnitude to plan the window around.

**Do not interrupt it.** Killing the build mid-flight rolls it back over the same
1.3M rows, holding locks while it unwinds — which takes longer than letting it
finish. Give it the time it needs.

Verify afterwards:

```sql
SELECT i.name, i.type_desc
FROM sys.indexes i
JOIN sys.objects o ON o.object_id = i.object_id
WHERE o.name = 'idt_transfers';
```

Expect `idt_transfers_product_created_idx` and `idt_transfers_created_at_idx`
alongside the clustered `PK__idt_tran__...`.
