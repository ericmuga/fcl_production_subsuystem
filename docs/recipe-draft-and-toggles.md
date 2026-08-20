# Stuffing Production Orders — Test / Live Toggles

How to run the stuffing production order generation against test data, and how to
move it to live when it is signed off.

There are **two independent switches**. They are separate on purpose: you can read
test recipes while writing nowhere near BC, and you can prove the write path
separately from the recipe data.

| Switch | Question it answers | Values |
|---|---|---|
| `RECIPE_DATA_TABLE` | Which recipes do we generate **from**? | `RecipeData` (live) / `recipe_data_draft` (test) |
| `PRODUCTION_ORDERS_TARGET` | Where do generated orders get **written**? | `local` (safe) / `production_data` (BC) |

Both live in `.env`. Neither requires a code change.

---

## 1. The switches

### `RECIPE_DATA_TABLE` — the recipe source

```dotenv
# Test: read the editable draft copy
RECIPE_DATA_TABLE=recipe_data_draft

# Live: read the real recipe table
RECIPE_DATA_TABLE=RecipeData
```

Config: `config/recipes.php`. Read by `SausageController::recipeTable()`, which
feeds every recipe lookup the generation makes — the packing-route graph, the
packed-item dropdown on the stuffing screen, and the recipe lines each order is
built from.

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

| `RECIPE_DATA_TABLE` | `PRODUCTION_ORDERS_TARGET` | What it is for |
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

1. Confirm sign-off against `RECIPE_DATA_TABLE=RecipeData` +
   `PRODUCTION_ORDERS_TARGET=local`, so live recipes are proven before anything
   reaches BC.
2. Apply the migrations intended for this release — see the release checklist in
   `docs/change-request-stuffing-production-orders.md`.
3. Set both switches to live values in the server `.env`:
   ```dotenv
   RECIPE_DATA_TABLE=RecipeData
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
`RECIPE_DATA_TABLE=RecipeData`.

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
