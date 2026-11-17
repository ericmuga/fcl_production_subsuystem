<?php

namespace App\Services\BcPlans;

/**
 * Maps one order of BC's POST /api/v1/bc/released-orders (codeunit 51519110 "WMS Released Order Push") to the plan
 * shape PlanSync stores (see PlanSource). Quantities are in the item's base unit (kg).
 *
 * order: order_no, status, pushed, firm_planned_no, item_no, description, location_code, due_date, base_uom,
 *        planned_qty_base, executed_qty_base, quantity_base, finished_qty_base, batch_size_base, routing_locations[],
 *        components[] (plan_line_no, line_no, item_no, description, uom, qty_per, expected_qty, remaining_qty,
 *        location_code, flushing_method, is_by_product), batches[] (batch_code, plan_line_no, batch_no, item_no,
 *        variant_code, location_code, due_date, batch_uom, quantity, base_uom, quantity_base, executed_qty_base, status)
 */
class ReleasedOrderPayload
{
    public const BATCH_STATUSES = ['open', 'sent', 'in_progress', 'completed', 'cancelled'];

    public static function rules(): array
    {
        return [
            'orders' => 'required|array|min:1|max:200',
            'orders.*.order_no' => 'required|string|max:20',
            'orders.*.item_no' => 'required|string|max:20',
            'orders.*.description' => 'nullable|string|max:100',
            'orders.*.location_code' => 'nullable|string|max:10',
            'orders.*.due_date' => 'required|date',
            'orders.*.firm_planned_no' => 'nullable|string|max:20',
            'orders.*.pushed' => 'nullable|boolean',
            'orders.*.base_uom' => 'nullable|string|max:10',
            'orders.*.quantity_base' => 'required|numeric',
            'orders.*.planned_qty_base' => 'nullable|numeric',
            'orders.*.executed_qty_base' => 'nullable|numeric',
            'orders.*.batch_size_base' => 'nullable|numeric|min:0',
            'orders.*.routing_locations' => 'nullable|array',
            'orders.*.components' => 'nullable|array',
            'orders.*.components.*.item_no' => 'required|string|max:20',
            'orders.*.components.*.line_no' => 'required|integer',
            'orders.*.batches' => 'nullable|array',
            'orders.*.batches.*.batch_code' => 'required|string|max:30',
            'orders.*.batches.*.batch_no' => 'required|integer',
            'orders.*.batches.*.quantity_base' => 'required|numeric',
            'orders.*.batches.*.status' => 'nullable|in:' . implode(',', self::BATCH_STATUSES),
        ];
    }

    public static function toPlan(array $o, ?string $sentAt = null): array
    {
        $num = fn($v) => round((float) ($v ?? 0), 5);
        $date = fn($v) => $v ? substr((string) $v, 0, 10) : null;
        $executed = $num($o['executed_qty_base'] ?? 0);
        // Pushed orders: BC froze the plan quantity at push time; otherwise what is on the lines plus what was executed.
        $planned = !empty($o['pushed']) && (float) ($o['planned_qty_base'] ?? 0) > 0
            ? $num($o['planned_qty_base'])
            : $num($o['quantity_base']) + $executed;
        $batches = [];
        foreach ($o['batches'] ?? [] as $b) {
            $status = $b['status'] ?? 'sent';
            if ($status === 'open') {
                continue; // cut but not pushed
            }
            $batches[] = [
                'batch_code' => $b['batch_code'],
                'plan_line_no' => (int) ($b['plan_line_no'] ?? 10000),
                'batch_no' => (int) $b['batch_no'],
                'item_no' => $b['item_no'] ?? $o['item_no'],
                'variant_code' => ($b['variant_code'] ?? '') ?: null,
                'location_code' => $b['location_code'] ?? ($o['location_code'] ?? null),
                'due_date' => $date($b['due_date'] ?? $o['due_date']),
                'batch_uom' => $b['batch_uom'] ?? null,
                'quantity' => $num($b['quantity'] ?? 0),
                'base_uom' => $b['base_uom'] ?? ($o['base_uom'] ?? null),
                'quantity_base' => $num($b['quantity_base']),
                'bc_executed_qty_base' => $num($b['executed_qty_base'] ?? 0),
                'bc_status' => $status,
                'pushed_at' => null, // kept out of the change hash: re-sending an unchanged order changes nothing
                'bc_completed_at' => null,
            ];
        }
        [$section, $sections] = SqlPlanSource::sectionsFor(array_values(array_filter($o['routing_locations'] ?? [])), $o['location_code'] ?? null);
        $batchSize = (float) ($o['batch_size_base'] ?? 0);
        return [
            'plan_no' => $o['order_no'],
            'section' => $section,
            'sections' => $sections,
            'item_no' => $o['item_no'],
            'description' => (string) ($o['description'] ?? ''),
            'location_code' => (string) ($o['location_code'] ?? ''),
            'plan_date' => $date($o['due_date']),
            'uom' => $o['base_uom'] ?? 'KG',
            'batch_uom' => $batches[0]['batch_uom'] ?? ($batchSize > 0 ? 'BATCH' : null),
            'batch_size_base' => $batchSize > 0 ? $batchSize : null,
            'released_from' => ($o['firm_planned_no'] ?? '') ?: null,
            'planned_qty' => $planned,
            'executed_qty' => $executed,
            'remaining_qty' => round(max($planned - $executed, 0), 5),
            'status' => !empty($o['pushed']) ? 'open' : 'released',
            'bc_modified_at' => $sentAt,
            'closed_at' => null,
            'closed_by' => null,
            'batches' => $batches,
            'components' => array_map(fn($c) => [
                'plan_line_no' => (int) ($c['plan_line_no'] ?? 10000),
                'line_no' => (int) $c['line_no'],
                'item_no' => $c['item_no'],
                'description' => (string) ($c['description'] ?? ''),
                'uom' => $c['uom'] ?? null,
                'qty_per' => $num($c['qty_per'] ?? 0),
                'expected_qty' => $num($c['expected_qty'] ?? 0),
                'remaining_qty' => $num($c['remaining_qty'] ?? 0),
                'location_code' => $c['location_code'] ?? null,
                'flushing_method' => $c['flushing_method'] ?? null,
                'is_by_product' => !empty($c['is_by_product']) || (float) ($c['qty_per'] ?? 0) < 0,
            ], $o['components'] ?? []),
        ];
    }
}
