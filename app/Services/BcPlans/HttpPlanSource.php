<?php

namespace App\Services\BcPlans;

/**
 * Orders BC posts to WMS (POST /api/v1/bc/released-orders): nothing to fetch, BC pushes them. Closing goes through the
 * same BC staging route as the SQL source (WMS Plan Close Request, processed by BC's job queue).
 */
class HttpPlanSource implements PlanSource
{
    public function fetch(string $from, string $to): array
    {
        return [];
    }

    public function name(): string
    {
        return 'bc-http';
    }

    public function close(string $planNo, ?string $user = null): void
    {
        (new BcStaging())->requestClose($planNo, $user);
    }
}
