<?php

namespace App\Services\BcPlans;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Moving a planned order to another location in WMS.
 * - location_code is the location in use: fulfilment, batching and plan matching all read it; bc_location_code is BC's.
 * - The section follows the new location when its prefix maps to one (config bc_plan.sections), otherwise BC's stays.
 * - Components at the order's BC location move with it; components BC put elsewhere stay there.
 * - Batches not yet done take the new location; started / completed WMS orders keep the location they were recorded at.
 * - The sync keeps the edit (it updates bc_location_code only) until it is reset.
 * BC is not changed: the execution orders WMS sends carry the new location, and BC posts output there.
 */
class PlanLocation
{
    public const EDITABLE = ['firm_planned', 'released', 'open'];

    /** BC locations for the picker, code => name (cached 10 min); falls back to the codes WMS already knows. */
    public static function options(): array
    {
        return Cache::remember('bc_plan.locations', 600, function () {
            $list = [];
            try {
                $source = PlanSync::make()->source();
                if (method_exists($source, 'bcLocations')) {
                    $list = $source->bcLocations();
                }
            } catch (\Throwable $e) {
                // BC not reachable: use what WMS has seen
            }
            if (!$list) {
                foreach (['bc_plan_orders' => 'bc_location_code', 'bc_plan_order_components' => 'location_code'] as $table => $col) {
                    foreach (DB::table($table)->whereNotNull($col)->where($col, '<>', '')->distinct()->pluck($col) as $code) {
                        $list[$code] = '';
                    }
                }
                ksort($list);
            }
            return $list;
        });
    }

    /** @return string|null error */
    public function change(string $planNo, string $location, ?int $userId): ?string
    {
        $location = strtoupper(trim($location));
        $plan = DB::table('bc_plan_orders')->where('plan_no', $planNo)->first();
        if (!$plan) {
            return "Order {$planNo} not found.";
        }
        if (!in_array($plan->status, self::EDITABLE, true)) {
            return "Order {$planNo} is " . str_replace('_', ' ', $plan->status) . ': its location can no longer be changed.';
        }
        $options = self::options();
        if ($options && !array_key_exists($location, $options)) {
            return "Location {$location} is not a BC location.";
        }
        $bcLocation = $plan->bc_location_code ?? $plan->location_code;
        if ($location === $bcLocation) {
            $this->reset($planNo);
            return null;
        }
        DB::transaction(function () use ($plan, $location, $userId) {
            DB::table('bc_plan_orders')->where('plan_no', $plan->plan_no)->update([
                'location_code' => $location,
                'section' => SqlPlanSource::sectionOf($location) ?? ($plan->bc_section ?? $plan->section),
                'location_overridden' => 1,
                'location_changed_by' => $userId,
                'location_changed_at' => now(),
                'updated_at' => now(),
            ]);
            self::follow($plan->plan_no);
        });
        return null;
    }

    public function reset(string $planNo): void
    {
        DB::transaction(function () use ($planNo) {
            DB::table('bc_plan_orders')->where('plan_no', $planNo)->update([
                'location_code' => DB::raw('COALESCE(bc_location_code, location_code)'),
                'section' => DB::raw('COALESCE(bc_section, section)'),
                'location_overridden' => 0,
                'location_changed_by' => null,
                'location_changed_at' => null,
                'updated_at' => now(),
            ]);
            self::follow($planNo);
        });
    }

    /** Components at the order's BC location and the batches not yet done take the order's location in use. */
    public static function follow(string $planNo): void
    {
        $plan = DB::table('bc_plan_orders')->where('plan_no', $planNo)->first(['location_code', 'bc_location_code']);
        if (!$plan) {
            return;
        }
        $bcLocation = $plan->bc_location_code ?? $plan->location_code;
        DB::table('bc_plan_order_components')->where('plan_no', $planNo)->update([
            'location_code' => DB::raw('CASE WHEN COALESCE(bc_location_code, location_code) = ' . DB::getPdo()->quote((string) $bcLocation)
                . ' THEN ' . DB::getPdo()->quote((string) $plan->location_code) . ' ELSE COALESCE(bc_location_code, location_code) END'),
        ]);
        DB::table('bc_plan_batches')->where('plan_no', $planNo)->whereIn('wms_status', ['pending', 'in_progress'])
            ->update(['location_code' => $plan->location_code]);
    }
}
