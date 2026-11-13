# BC ⇄ WMS production integration: phased plan

Source design: *BC ⇄ WMS Production Integration Map* (review draft, 23 Oct 2026). Step numbers (S1–S13) and check
points (CP1–CP28) refer to that document.

| | Branch / location |
|---|---|
| WMS (Laravel 8) | `fcl_production_subsuystem`, branch `feature/bc-mrp-plan-integration` |
| BC extension (FARMERSCHOICE, BC24) | `E:\FCL_SAAS_ISV_mrp` (git worktree of `E:\FCL_SAAS_ISV`), branch `feature/mrp-wms-plan-integration`, based on `isv/renumber-bc24` |
| Local WMS database | `localhost / fcl_wms_dev`, created from the migrations |
| Local BC data | `localhost / FCL`, a mirror of BC with the MRP pilot loaded, read through the WMS `bc240` connection |

## What exists today

- **WMS:**
  - Each weighing is captured in `beheading_data`, `butchery_data`, `deboned_data`, `idt_transfers` or `beef_slicing`.
  - Stuffing weighings already generate production orders (`generated_production_orders`) from `RecipeData`. Writing them to BC `ProductionData` is optional.
- **BC:**
  - Each WMS order lands in the staging tables `WMSProduction Order Header` and `WMS Production Journal Line`.
  - Codeunit 51519066 `WMSIntegrationsFinal` then:
    - creates a released production order, numbered from the WMS order no.;
    - writes the item journal lines;
    - posts them;
    - finishes the order.
- **SQL Agent job `ProcessProduction`:** 17 steps, every 5 minutes. It builds those orders from WMS weighings and WMS-held recipes.

The design keeps that route (CP17 fallback) and adds the plan around it.

## Contract (applies to every phase)

| Message | Direction | Key | Content |
|---|---|---|---|
| Plan order | BC → WMS | `planNo` (BC Released Prod. Order no.) | item, location, plan date, planned qty (original), remaining qty, executed qty, unit, status (Open / Closed), components (line no., item, qty per, expected qty, unit, location, by-product flag when qty per < 0) |
| Execution order | WMS → BC | `transactionId` (WMS-generated, unique, idempotency key, CP19/CP23) | execution order no. (WMS numbering kept), **planNo**, posting date/time, user, lines: line no. (BC component line no. when known, CP7 of §8), item, qty (net kg; pieces for packs, CP22), unit, location, bin, entry type (output / consumption; by-product = consumption with negative qty), batch no. (lot, CP26) |
| Status | BC → WMS | `transactionId` | Pending / Processing / Completed / **Held** (stock short, CP25) / Error + message |
| Close day | WMS → BC | `planNo` | closed by, closed at; BC finishes the plan, later execution orders for it are rejected (CP4) |

**Transport:**
- **Target:** BC API pages (`/api/farmerschoice/mrp/v1.0/...`), as in CP17.
- **Fallback:** the existing staging tables and SQL route.
- **Local development:** WMS reads the plan through the `bc240` SQL connection. The source is set by `BC_PLAN_SOURCE` (`sql` | `api`).

## Phases

### Phase 0: Foundations and quick fixes (week of 16 Nov)
- [x] Branches in both repos; local WMS database built from the migrations; AL compiler and BC 24.5 symbols working.
- [ ] FCL IT, on the SQL job (not in these repos):
  - fix the step chaining (§8 items 1–2);
  - add the Marination order + line duplicate guard (item 3);
  - move the job owner to a service account (item 12).
- [ ] Agree the contract above with Queue (next step 3 of the map).
- **Exit:** contract signed off; job fixes deployed.

### Phase 1: Plan out (BC → WMS), steps S3–S5
- **BC:**
  - plan fields on Production Order: WMS plan status, original planned qty, executed qty, published / closed stamps;
  - **Publish to WMS** and **Close WMS plan** actions;
  - codeunit `WMS Plan Management`;
  - API pages `wmsPlanOrders` and `wmsPlanComponents`.
- **WMS:**
  - `bc_plan_orders` and `bc_plan_order_components` tables;
  - `php artisan bc:sync-plans` (pull, idempotent upsert);
  - **Production plan** screen per section and location: target vs. produced so far.
- **Exit:**
  - planner publishes the pilot plans;
  - section screens show the day target;
  - re-syncs change nothing.

### Phase 2: Execution orders carry the plan (S6–S9)
- **BC:**
  - staging header gets `Plan Order No.` and a unique `WMS Transaction ID`;
  - status `Held` added;
  - API pages `wmsExecutionOrders` with `wmsExecutionLines` (deep insert);
  - a duplicate transaction ID is accepted and ignored (CP19/CP23);
  - an order for a closed plan is rejected (CP4).
- **WMS:**
  - every capture table and `generated_production_orders` gets `plan_order_no` and `transaction_id`;
  - the weighing screens pick the open plan for the item and location;
  - an item check against the plan output, its components and approved by-products (S7, CP6) warns and never blocks (CP24);
  - push via the API, with replay after an outage.
- **Consumption (CP21, D17):**
  - where only output is weighed, the bridge derives consumption from the BC BOM (now per batch);
  - WMS stops using `RecipeData` for those lines.
- **Exit:**
  - one pilot section (stuffing, already generating orders) pushes execution orders with plan no. through the API;
  - duplicates are ignored.

### Phase 3: Post, finish and net (S10–S11)
- **BC job queue (one transaction per order):**
  1. stock gate: if consumption isn't covered, the order is **Held** and retried (CP25, D18);
  2. post and finish, as today;
  3. **reduce the plan**:
     - executed qty += posted output;
     - plan line quantity = max(original − executed, 0);
     - output above plan is allowed (CP2, CP4).
- **Status:** posted / held / error goes back to WMS. Errors are owned by Section BA (CP20).
- **Report:** a daily exception report of warn-only weighings (CP24).
- **Exit:**
  - planning worksheet shows no double supply for the pilot items;
  - Held orders clear when stock posts.

### Phase 4: Close the day (S12)
- **WMS:** supervisor function **Close day** per plan or section → close message.
- **BC:**
  - plan finished; the shortfall drops out of supply (CP5);
  - later execution orders for that plan are rejected.
- **Next MRP run:** replans the shortfall (S13).
- **Exit:** the end-of-day close is run by supervisors for the pilot sections for one week.

### Phase 5: Splits, by-products and costing (CP7, CP9, CP11, CP12)
- **Execution orders for split processes** (beheading, breaking, deboning, slicing):
  - leading cut = output;
  - by-products = negative consumption against the BC component line;
  - all WMS-fed components set to Manual flushing.
- **Planning:** prove the negative-consumption supply behaviour (CP7, CRP scenario 9).
- **Costing:**
  - standard costs for by-products;
  - quarterly yield-based BOM versions from WMS actuals.
- **Exit:** CP7 confirmed by Queue on the BC build; cost of a split order checked by Costing.

### Phase 6: Cutover (CP27, CP28)
- **Order:** section by section — stuffing/packing, then mincing and cooking, curing and marination, deboning floor, then slicing.
- **For each section:**
  - parallel run against `ProcessProduction`;
  - switch off the matching job step;
  - one CRP scenario at festive peak volume.
- **Exit:** `ProcessProduction` is retired and WMS holds no recipes.

## Progress (13 Nov 2026)

| Phase | BC (`feature/mrp-wms-plan-integration`) | WMS (`feature/bc-mrp-plan-integration`) | State |
|---|---|---|---|
| 1 Plan out | Plan fields on Production Order (`Production Ext`, fields 51519100–107); enum 51519013 `WMS Plan Status`; codeunit 51519105 `WMS Plan Management`; API pages 51519378 `wmsPlanOrders` (with bound action `closePlan`) and 51519379 `wmsPlanComponents`; **Publish to WMS** / **Close WMS Plan** on Released Production Order(s) (page ext 51519118/119) | `bc_plan_orders`, `bc_plan_order_components`; `php artisan bc:sync-plans` (idempotent, `--dry-run`); `SqlPlanSource` (bc240, published or pilot mode) and `ApiPlanSource` (OAuth client credentials); **Production Plan** screen `/production-plan` with target, produced, posted, remaining and **Close day** | Built; BC compiles with no new warnings; WMS tests pass (17/17) |
| 2 Execution orders | Staging header: `Plan Order No.`, `WMS Transaction ID` (keyed), `Status Message`, `Plan Netted`; lines: `Batch No.`, `Plan Component Line No.`; status `Held`; API pages 51519380 `wmsExecutionOrders` + 51519381 `wmsExecutionLines` (deep insert; rejects duplicates with `WMS_DUPLICATE_TRANSACTION` and closed plans with `WMS_PLAN_CLOSED`) | `plan_order_no` + `transaction_id` on `generated_production_orders`, `idt_transfers`, `beheading_data`, `butchery_data`, `deboned_data`, `beef_slicing`; stuffing orders are stamped with the open plan and `GPO:<order no.>` | Data model and stuffing stamping built; push client and other sections still to do |
| 3 Netting | `NetExecutionOrder` called after an execution order is posted and finished (`postWMSProdutionOrder`); runs once per order | — | Built; stock gate (Held) still to do |

### Running it locally
- **WMS:**
  - Run `composer install`, then `php artisan migrate`. `.env` points at `fcl_wms_dev`, with `bc240` set to the local `FCL` mirror.
  - Run `php artisan bc:sync-plans --from=2026-11-16 --to=2026-11-22`.
  - Open `/production-plan`.
- **BC:**
  - Compile with the AL compiler. Symbols come from `E:\FCL_SAAS_ISV\.alpackages`.
  - Publish to a sandbox.
  - Release the MRP plan orders and use **Publish to WMS** on the Released Production Orders list.
  - Set `BC_PLAN_SOURCE=api` and the `BC_API_*` settings in WMS.
- **Mirror data:** neither SQL mirror has released MRP plan orders yet. The FCL2 planning worksheet has 6 lines that haven't been carried out. The sync correctly returns 0 until a planner releases plans.

### Deployment notes
- **WMS:** run the two new migrations before deploying the code. The stuffing generator writes `plan_order_no` and `transaction_id`. If the plan tables are missing, plan lookup falls back to "no plan".
- **BC:**
  - The new fields sit on the existing staging tables and on the `Production Order` extension, so this is a schema-additive upgrade of FARMERSCHOICE.
  - The API pages need an Azure AD app registration with the permission set `GeneratedPermission`.
