<?php

namespace App\Services\BcPlans;

/**
 * Where WMS reads BC's daily plans from. Every source returns the same normalised shape:
 *
 * [
 *   'plan_no' => 'RPO-001234', 'item_no' => 'J31015501', 'description' => '...', 'location_code' => '3535',
 *   'plan_date' => '2026-11-16', 'uom' => 'KG', 'planned_qty' => 9200.0, 'executed_qty' => 0.0,
 *   'remaining_qty' => 9200.0, 'status' => 'open'|'closed', 'bc_modified_at' => '2026-11-13 08:00:00',
 *   'closed_at' => null, 'closed_by' => null,
 *   'components' => [
 *     ['plan_line_no' => 10000, 'line_no' => 10000, 'item_no' => 'G2306', 'description' => '...', 'uom' => 'KG',
 *      'qty_per' => 80.808, 'expected_qty' => 9292.92, 'remaining_qty' => 9292.92, 'location_code' => '3535',
 *      'flushing_method' => 'Manual', 'is_by_product' => false],
 *   ],
 * ]
 */
interface PlanSource
{
    /** @return array<int, array> plans whose plan date falls in [from, to] */
    public function fetch(string $from, string $to): array;

    public function name(): string;

    /** Close the day for a plan in BC (integration map S12). */
    public function close(string $planNo): void;
}
