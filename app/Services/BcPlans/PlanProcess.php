<?php

namespace App\Services\BcPlans;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The process step of a plan inside its section (Sausage: Packing, Stuffing, Chopping, ...). Once BC plans the WIP
 * items too, one section carries orders for several steps; the step lets the floor pick the orders for its station.
 *
 * The step is the recipe table's process for the plan's output item (the process the recipe makes it with); when the
 * item has no recipe, the plan's location decides (bc_plan.process_by_location).
 */
class PlanProcess
{
    /** Separates section and step in a filter value: "Sausage:Stuffing". */
    public const SEP = ':';

    /** output item => process, loaded once per request / command (the recipe table has no index on output_item). */
    private static ?array $map = null;

    public static function for(string $itemNo, ?string $locationCode): ?string
    {
        return self::recipeMap()[$itemNo] ?? self::fromLocation($locationCode);
    }

    /** Forget the loaded recipe map (tests, long-running workers after a recipe import). */
    public static function flush(): void
    {
        self::$map = null;
    }

    private static function recipeMap(): array
    {
        if (self::$map !== null) {
            return self::$map;
        }
        self::$map = [];
        $table = config('production_orders.recipe_table', 'RecipeData');
        try {
            // Most frequent process per output item (an item made by several recipes takes its usual process).
            $best = [];
            foreach (DB::table($table)->whereNotNull('process')->where('process', '<>', '')
                ->groupBy('output_item', 'process')->select('output_item', 'process', DB::raw('COUNT(*) AS n'))->get() as $r) {
                if (!isset($best[$r->output_item]) || $r->n > $best[$r->output_item][1]) {
                    $best[$r->output_item] = [self::normalise($r->process), (int) $r->n];
                }
            }
            self::$map = array_map(fn($b) => $b[0], $best);
        } catch (\Illuminate\Database\QueryException $e) {
            // no recipe table: locations only
        }
        return self::$map;
    }

    private static function fromLocation(?string $locationCode): ?string
    {
        $map = config('bc_plan.process_by_location', []);
        return $locationCode !== null && isset($map[$locationCode]) ? $map[$locationCode] : null;
    }

    /** "chopping " -> "Chopping"; the recipe table's spelling otherwise kept. */
    public static function normalise(?string $process): ?string
    {
        $p = trim((string) $process);
        return $p === '' ? null : mb_strtoupper(mb_substr($p, 0, 1)) . mb_substr($p, 1);
    }

    /**
     * Section filter options: each section, then its steps ("Sausage - Packing") in the configured order
     * (bc_plan.process_order), then any other step plans carry, alphabetically.
     *
     * @return array<int, array{value: string, label: string, step: bool}>
     */
    public static function options(): array
    {
        $sections = array_values(array_unique(array_values(config('bc_plan.sections', []))));
        $order = config('bc_plan.process_order', []);
        $seen = [];
        try {
            if (Schema::hasColumn('bc_plan_orders', 'process')) {
                foreach (DB::table('bc_plan_orders')->whereNotNull('process')->whereNotNull('section')
                    ->whereIn('status', ['firm_planned', 'released', 'open', 'closing'])
                    ->distinct()->get(['section', 'process']) as $r) {
                    $seen[$r->section][] = $r->process;
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // no table yet: sections only
        }

        $out = [];
        foreach ($sections as $s) {
            $out[] = ['value' => $s, 'label' => $s, 'step' => false];
            $configured = $order[$s] ?? [];
            $extra = array_diff(array_unique($seen[$s] ?? []), $configured);
            sort($extra);
            foreach (array_merge($configured, $extra) as $p) {
                $out[] = ['value' => $s . self::SEP . $p, 'label' => $s . ' - ' . $p, 'step' => true];
            }
        }
        return $out;
    }

    /** Apply a section filter value ("Sausage" or "Sausage:Stuffing"; "all"/"" = no filter) to a plan query. */
    public static function filter($query, ?string $value, string $alias = 'p')
    {
        if ($value === null || $value === '' || $value === 'all') {
            return $query;
        }
        [$section, $process] = array_pad(explode(self::SEP, $value, 2), 2, null);
        $query->where($alias . '.sections', 'like', '%' . $section . '%');
        if ($process !== null && $process !== '') {
            $query->where($alias . '.process', $process);
        }
        return $query;
    }

    /** "Sausage:Stuffing" -> "Sausage" (vessels and scales stay per section). */
    public static function sectionOf(?string $value): ?string
    {
        return $value === null ? null : explode(self::SEP, $value, 2)[0];
    }
}
