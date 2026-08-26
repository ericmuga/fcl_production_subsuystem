# Change Request — Automatic Production Orders from Stuffing Weighings

**For Business Analyst review and sign-off.**

| | |
|---|---|
| **Module** | Sausage → Receive IDT → Stuffing IDTs (`/stuffing-weights`) |
| **Branch** | `feature/chopping-qty-per-batch` |
| **Status** | Built, ready for UAT. Not yet enabled against BC. |
| **Target DB** | `calibra` (WMS), plus BC `ProductionData` at go-live |

---

## 1. Why this change

Today an operator weighs a stuffing batch, and the production orders that record
what was consumed and produced across the recipe chain are raised separately —
outside the WMS, from a SQL script. That is manual, delayed, and can be missed.

This change raises those production orders automatically, at the moment of
weighing, from the same recipe data BC uses.

**Business outcome:** every stuffing weighing produces its own complete set of
production orders, with no separate step and no script to run.

---

## 2. Scope

### In scope

1. Generate production orders automatically from a stuffing weighing.
2. Skip generation silently where no recipe exists.
3. Run generation in the background so it never delays the next weighing.
4. On-screen register of generated orders, with filters and Excel export.
5. An editable **draft** copy of the recipe table, with full maintenance screens and
   Excel upload/download, for testing before go-live.
6. Two configuration switches to run the whole thing in test or live mode.

### Out of scope

- Changing how BC consumes `ProductionData` once rows are written.
- Editing the live `RecipeData` table through the WMS. The new screens **only**
  ever write to the draft copy.
- Posting, reversing or correcting orders after generation.
- Any other weighing station (butchery, slaughter, chopping) — this is the stuffing
  station only.

---

## 3. The process, step by step

### As the operator experiences it

1. Operator opens **Sausage → Receive IDT → Stuffing IDTs**.
2. Selects the **Product** being weighed (the stuffing mix).
3. The **Stuffing for (Output)** list fills with only the packed items that product
   can actually end up as, taken from the recipe data.
4. Enters the **Batch No**, then presses **Weigh** (or ticks manual weight).
5. Presses **Save**.
6. The weight is saved and the screen confirms **"Receipt saved successfully"**
   immediately. The operator can weigh the next batch straight away.
7. The page refreshes, and the new production orders appear under **Generated
   Production Orders**.

The operator is never asked about production orders, never waits for them, and is
never shown an error about them. If they cannot be generated, the weight is still
saved.

### What the system does behind the weighing

| Step | Rule |
|---|---|
| 1 | Save the weighing to `idt_transfers`. **This always happens first and is never at risk.** |
| 2 | Check a recipe route exists from the weighed item to the chosen packed item. If not — **stop, silently**. |
| 3 | Check the item is not on the exclusion list. If it is — **stop, silently**. |
| 4 | Check orders were not already generated for this weighing. If they were — **stop**. |
| 5 | Release the screen back to the operator. Everything below runs after that. |
| 6 | Walk the recipe chain from the weighed item to the packed item — normally 2 steps (stuff, pack), 3 when the chain passes through smoking. |
| 7 | Raise one production order per step. |
| 8 | Write every order to `generated_production_orders` (always). |
| 9 | Write every order to BC `ProductionData` — **only when configured to do so**. |

---

## 4. Business rules

### 4.1 How quantities are calculated

Quantities follow the recipe yields. At each step:

```
scale factor = quantity carried in ÷ the recipe's "qty per" for that input
output quantity = recipe batch size × scale factor
each consumption quantity = that input's "qty per" × scale factor
```

The output quantity of one step becomes the quantity carried into the next.

**Worked example** — weigh 250 kg of a stuffing mix, recipe says 100 kg of that mix
per 100 kg batch:

- scale factor = 250 ÷ 100 = **2.5**
- output line = 100 × 2.5 = **250 kg** of the stuffed item
- an input at 0.7143 per batch = 0.7143 × 2.5 = **1.786 kg**

### 4.2 Order line structure

Each order has:

- **Line 1000** — the item produced (output). Carries that step's own recipe.
- **Lines 2000, 3000, …** — each item consumed (consumption). Carries the final
  packing recipe the whole chain is being produced for, matching how BC's own
  orders are structured.

### 4.3 Order numbering

`<prefix>_<recipe series>_<weighing id>` — for example `P19_1E05_812`.

- **Prefix** comes from the routing (Stuffing-2055 → P19, Packing-2055 → P20,
  Cont-Smoking → P35, and so on).
- **Recipe series** is the final packing recipe with the 1210/1220/1230/1240 series
  compressed to 1/2/3/4, as BC does.
- **Weighing id** ties the order back to the weighing it came from.

Every step of one chain shares a number and differs only in the prefix — which is
what the existing script relies on when it turns a P20 packing order into its P19
stuffing counterpart.

### 4.4 When nothing is generated

Generation is skipped, and **the weight is still saved**, when:

| Situation | Behaviour |
|---|---|
| No packed item selected | Skipped silently |
| Weight is zero or negative | Skipped silently |
| No recipe route from the weighed item to the packed item | Skipped silently, logged |
| Weighed or packed item is on the exclusion list | Skipped silently |
| Orders already exist for this weighing | Skipped — prevents duplicates |
| A recipe in the chain has no usable quantity | Nothing written for that weighing, logged as a warning |

**BA decision point:** items currently excluded are
`G2206, G2005, G1468, G2267, G2279, G2295, G2297, G2268, J31015806, G2210`.
This list was carried over from the existing SQL script. **Please confirm it is
still correct.**

### 4.5 Failure handling

The weighing is saved before generation is attempted, and generation runs after the
operator has been released. A gap in the recipe data, or a BC connection problem,
**cannot cost the operator their weight**. Failures are written to the application
log, not shown on screen.

**BA decision point:** operators no longer see any message about production orders.
Confirm this is acceptable, and agree **who monitors the log** and how often.

---

## 5. Screens

### 5.1 Generated Production Orders (on the stuffing screen)

A single flat table of every line generated in the last 2 days, newest weighing
first, then each order's steps and lines in sequence.

Filters: Order No, Process, Packed Item, Line Type, Status. Plus free-text search
and Excel/CSV/PDF export.

Columns: Order No, Step, Process, Routing, Line, Item, Description, Type, Quantity,
UOM, Location, Recipe, Ext. Doc, Batch, Weighed Item, Packed Item, Status, By,
Generated.

A separate **Export Generated Production Orders** panel exports any date range with
the same filters.

### 5.2 Recipe Data (Draft) — new screen

**Sausage → Data Management → Recipe Data (Draft)**

An editable copy of the recipe table for testing. It **never writes to live
`RecipeData`**.

The draft fills itself from live the moment the table is created on deployment, so
it is usable immediately and testing starts from the recipes as they really are.

| Action | Effect |
|---|---|
| New / Edit / Delete | Maintain single recipe lines |
| Copy from Live | Replace the draft with a fresh copy of live |
| Download Excel | Export what the filters select |
| Upload Excel | Load a sheet — Replace or Merge by ID |
| Clear Draft | Empty the draft |

A banner states which table weighings are currently reading, so it is never a guess.

**Upload safety:** if any row has a blank Process the whole file is rejected and
nothing is written — a bad file leaves the draft exactly as it was.

---

## 6. Data changes

| Table | Change | Note |
|---|---|---|
| `generated_production_orders` | Already exists (previous release) | The WMS register of what was generated |
| `recipe_data_draft` | **New** | Editable copy of `RecipeData`, same columns |
| `idt_transfers` | No change | |
| `RecipeData` | **Read only** | Never written to by this feature |
| `ProductionData` (BC) | Inserted into, at go-live only | Existing order/line combinations are skipped |

---

## 7. Configuration — test vs live

Two switches, both in `.env`, no code change to flip:

| Switch | Test | Live |
|---|---|---|
| `PRODUCTION_ORDERS_RECIPE_TABLE` | `recipe_data_draft` | `RecipeData` |
| `PRODUCTION_ORDERS_TARGET` | `local` | `production_data` |

Full detail: [`recipe-draft-and-toggles.md`](recipe-draft-and-toggles.md).

---

## 8. UAT test scenarios

Run with `PRODUCTION_ORDERS_RECIPE_TABLE=recipe_data_draft` and
`PRODUCTION_ORDERS_TARGET=local`.

| # | Scenario | Expected result |
|---|---|---|
| 1 | Weigh an item with a normal 2-step recipe route | Weight saved. 2 orders appear in the panel — one Stuffing, one Packing |
| 2 | Weigh an item whose chain passes through smoking | 3 orders appear, steps 1–3 |
| 3 | Check quantities on a known recipe | Output = batch size × scale; each consumption = qty per × scale |
| 4 | Weigh an item with no recipe route | Weight saved, no orders, no error shown to the operator |
| 5 | Weigh an excluded item (e.g. G2206) | Weight saved, no orders |
| 6 | **Timing:** weigh and save repeatedly, back to back | Save confirms immediately every time; no pause between weighings |
| 7 | Filter the panel by Process, then Packed Item | Only matching lines shown |
| 8 | Export the panel to Excel | File matches what is on screen |
| 9 | Recipe Data (Draft) → Copy from Live | Draft row count equals live row count |
| 10 | Draft → Download Excel, change one Batch Size, upload as **Merge** | That one line updated, row count unchanged |
| 11 | Draft → Download, upload as **Replace** | Row count unchanged, data unchanged |
| 12 | Upload a sheet with a blank Process | Upload rejected, error shown, draft unchanged |
| 13 | Edit a recipe in the draft, then weigh that item | New order reflects the edited recipe immediately |
| 14 | Add a new recipe line via New Line | Line saved and visible |
| 15 | Delete a recipe line | Line removed |
| 16 | Confirm no live change | `RecipeData` row count and contents unchanged throughout |

Then repeat scenarios 1–3 with `PRODUCTION_ORDERS_RECIPE_TABLE=RecipeData` and
`PRODUCTION_ORDERS_TARGET=local` as the final rehearsal on live recipes.

---

## 9. Release checklist

> **Verify the live migration state before deploying.** Run
> [`verify-live-migrations.sql`](verify-live-migrations.sql) on the live WMS
> database. It is read only, and returns exactly the migrations
> `php artisan migrate` would run.
>
> Checked 2026-08-18 against live (batch 37,
> `2025_02_19_131011_add_intake_item_to_beef_slicing_table`). Two migrations are
> pending, both `CREATE TABLE` of new tables — nothing is altered or dropped:
>
> | Migration | Creates |
> |---|---|
> | `2026_08_12_090000_create_generated_production_orders_table` | `generated_production_orders` |
> | `2026_08_17_100000_create_recipe_data_draft_table` | `recipe_data_draft` |
>
> Re-run the query on the day of deployment and confirm it returns **those two rows
> and nothing else**.

1. Back up the live WMS database.
2. Confirm the migration state as above.
3. Deploy the branch.
4. Run `php artisan migrate` — creates the two tables listed above, and fills
   `recipe_data_draft` from live `RecipeData` automatically. Expect
   `Seeded recipe_data_draft with 4,674 line(s) from RecipeData.` in the output
   (about 11 seconds). If that line does not appear, run
   `php artisan recipes:replicate-draft` before continuing.
5. `php artisan config:clear && php artisan cache:clear`; recycle the app pool.
6. Set the switches per section 7.
7. Weigh one item and confirm the order appears in the panel and in
   `ProductionData`.

### Known issue for the release

`database/migrations/2021_01_11_091503_scale_configs_table.php` was **edited in
place** on this branch (making `comport`, `baudrate` and `section` nullable). That
migration has already run on live, so `php artisan migrate` will **not** re-apply
it and the change will silently not reach live. If those columns need to be
nullable on live, it requires a **new** migration or a manual `ALTER TABLE`.

**BA decision point:** confirm whether that change is wanted in this release.

### Rollback

Set `PRODUCTION_ORDERS_TARGET=local` and clear the caches. Generation stops reaching
BC immediately, with no deployment and no code change. The `recipe_data_draft`
table can be left in place; it is inert while `PRODUCTION_ORDERS_RECIPE_TABLE=RecipeData`.

---

## 10. Open items for the BA

1. **Exclusion list** — is `G2206, G2005, G1468, G2267, G2279, G2295, G2297, G2268,
   J31015806, G2210` still correct?
2. **Silent failures** — operators see no message when orders cannot be generated.
   Who monitors the log, and how often?
3. **Panel retention** — the on-screen register shows the last 2 days. Is that
   enough for the floor?
4. **Draft screen access** — should Recipe Data (Draft) be restricted to a
   particular user group rather than everyone with a Sausage login?
5. **`scale_configs` migration** — see the known issue above.
6. **Order numbering** — confirm the `P19_1E05_812` format is what BC expects, and
   that reusing the weighing id keeps numbers unique over time.
