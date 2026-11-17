<?php

namespace App\Console\Commands;

use App\Services\BcPlans\EndOfDay;
use Illuminate\Console\Command;

class BcEndOfDay extends Command
{
    protected $signature = 'bc:end-of-day
        {--date= : close orders due on this day or earlier (Y-m-d, default today)}
        {--section= : only this WMS section (Sausage, Butchery, ...)}
        {--close : send the close requests to BC (without it, only list what would be closed)}
        {--refresh : only check BC for orders already closing}';

    protected $description = 'End of day: ask BC to close the day\'s planned production orders (BC finishes them), or check the ones closing';

    public function handle()
    {
        $eod = new EndOfDay();
        if ($this->option('refresh')) {
            $this->info($eod->refresh() . ' order(s) closed in BC since the last check.');
            return 0;
        }
        $date = $this->option('date') ?: today()->toDateString();
        $orders = $eod->candidates($date, $this->option('section') ?: null)->whereIn('status', EndOfDay::CLOSABLE);
        $this->table(['Order', 'Plan', 'Date', 'Section', 'Item', 'Planned', 'Produced', 'Unposted'], $orders->map(fn($o) => [
            $o->plan_no, $o->released_from, $o->plan_date, $o->section, $o->item_no, round($o->planned_qty, 2), round($o->produced, 2), $o->unposted,
        ])->all());
        if (!$this->option('close')) {
            $this->line('Dry run: add --close to send these close requests to BC.');
            return 0;
        }
        $failed = 0;
        foreach ($eod->close($orders->pluck('plan_no')->all(), null, 'WMS-EOD') as $no => $r) {
            $r['ok'] ? $this->info("{$no}: {$r['message']}") : $this->warn("{$no}: {$r['message']}");
            $failed += $r['ok'] ? 0 : 1;
        }
        return $failed ? 1 : 0;
    }
}
