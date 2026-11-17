<?php

namespace App\Services\BcPlans;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Reads plans from the BC API pages published by the FARMERSCHOICE extension (CP17 target route):
 *   GET  {base}/{tenant}/{environment}/api/farmerschoice/mrp/v1.0/companies({companyId})/wmsPlanOrders
 *   POST .../wmsPlanOrders({id})/Microsoft.NAV.closePlan
 * Authentication: Azure AD client credentials (service-to-service app registered in BC).
 */
class ApiPlanSource implements PlanSource
{
    private $cfg;

    public function __construct(?array $config = null)
    {
        $this->cfg = $config ?? config('bc_plan.api');
        foreach (['tenant', 'company_id', 'client_id', 'client_secret'] as $key) {
            if (empty($this->cfg[$key])) {
                throw new RuntimeException("BC API setting '{$key}' is missing (BC_API_" . strtoupper($key) . ').');
            }
        }
    }

    public function name(): string
    {
        return 'api';
    }

    private function token(): string
    {
        return Cache::remember('bc_api_token_' . md5($this->cfg['client_id']), 3000, function () {
            $res = Http::asForm()->timeout($this->cfg['timeout'])->post(sprintf($this->cfg['token_url'], $this->cfg['tenant']), [
                'grant_type' => 'client_credentials',
                'client_id' => $this->cfg['client_id'],
                'client_secret' => $this->cfg['client_secret'],
                'scope' => $this->cfg['scope'],
            ]);
            if (!$res->successful()) {
                throw new RuntimeException('BC API token request failed: HTTP ' . $res->status());
            }
            return $res->json('access_token');
        });
    }

    private function url(string $path): string
    {
        return rtrim($this->cfg['base_url'], '/') . "/{$this->cfg['tenant']}/{$this->cfg['environment']}/api/farmerschoice/mrp/v1.0/companies({$this->cfg['company_id']})/" . ltrim($path, '/');
    }

    private function client()
    {
        return Http::withToken($this->token())->acceptJson()->timeout($this->cfg['timeout']);
    }

    public function fetch(string $from, string $to): array
    {
        $url = $this->url('wmsPlanOrders') . '?' . http_build_query([
            '$filter' => "planDate ge {$from} and planDate le {$to}",
            '$expand' => 'wmsPlanComponents',
        ], '', '&', PHP_QUERY_RFC3986);
        $plans = [];
        while ($url) {
            $res = $this->client()->get($url);
            if (!$res->successful()) {
                throw new RuntimeException('BC API wmsPlanOrders failed: HTTP ' . $res->status() . ' ' . $res->body());
            }
            foreach ($res->json('value', []) as $p) {
                $plans[] = $this->normalise($p);
            }
            $url = $res->json('@odata.nextLink');
        }
        return $plans;
    }

    private function normalise(array $p): array
    {
        return [
            'plan_no' => $p['planNo'],
            'bc_id' => $p['id'],
            'item_no' => $p['itemNo'],
            'description' => $p['description'] ?? null,
            'location_code' => $p['locationCode'],
            'plan_date' => $p['planDate'],
            'uom' => $p['unitOfMeasure'] ?? null,
            'planned_qty' => (float) $p['plannedQuantity'],
            'executed_qty' => (float) $p['executedQuantity'],
            'remaining_qty' => (float) $p['remainingQuantity'],
            'status' => ($p['planStatus'] ?? '') === 'Closed' ? 'closed' : 'open',
            'bc_modified_at' => isset($p['lastModifiedDateTime']) ? date('Y-m-d H:i:s', strtotime($p['lastModifiedDateTime'])) : null,
            'closed_at' => (!empty($p['closedAt']) && strpos($p['closedAt'], '0001-') !== 0) ? date('Y-m-d H:i:s', strtotime($p['closedAt'])) : null,
            'closed_by' => $p['closedBy'] ?? null,
            'components' => array_map(function ($c) {
                return [
                    'plan_line_no' => (int) $c['planLineNo'],
                    'line_no' => (int) $c['lineNo'],
                    'item_no' => $c['itemNo'],
                    'description' => $c['description'] ?? null,
                    'uom' => $c['unitOfMeasure'] ?? null,
                    'qty_per' => (float) $c['quantityPer'],
                    'expected_qty' => (float) $c['expectedQuantity'],
                    'remaining_qty' => (float) $c['remainingQuantity'],
                    'location_code' => $c['locationCode'] ?? null,
                    'flushing_method' => $c['flushingMethod'] ?? null,
                    'is_by_product' => (bool) ($c['isByProduct'] ?? ((float) $c['quantityPer'] < 0)),
                ];
            }, $p['wmsPlanComponents'] ?? []),
        ];
    }

    public function close(string $planNo, ?string $user = null): void
    {
        $url = $this->url('wmsPlanOrders') . '?' . http_build_query(['$filter' => "planNo eq '{$planNo}'", '$select' => 'id'], '', '&', PHP_QUERY_RFC3986);
        $res = $this->client()->get($url);
        $id = $res->successful() ? ($res->json('value.0.id')) : null;
        if (!$id) {
            throw new RuntimeException("Plan {$planNo} not found in BC.");
        }
        $res = $this->client()->post($this->url("wmsPlanOrders({$id})/Microsoft.NAV.closePlan"));
        if (!$res->successful()) {
            throw new RuntimeException("BC refused to close plan {$planNo}: HTTP " . $res->status() . ' ' . $res->body());
        }
    }
}
