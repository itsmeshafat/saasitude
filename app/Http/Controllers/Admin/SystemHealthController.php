<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SystemHealthController extends Controller
{
    public function show(): View
    {
        $queueDepth = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();

        $schedulerSetting = DB::table('settings')->where('key', 'system.last_schedule_run')->value('value');

        $dbSizeMb = $this->databaseSizeMb();
        $diskFreeGb = round(disk_free_space('/') / 1024 / 1024 / 1024, 1);
        $diskTotalGb = round(disk_total_space('/') / 1024 / 1024 / 1024, 1);

        $checks = [
            ['label' => 'Queue depth (pending jobs)', 'value' => (string) $queueDepth, 'ok' => $queueDepth < 100],
            ['label' => 'Failed jobs', 'value' => (string) $failedJobs, 'ok' => $failedJobs === 0],
            ['label' => 'Database size', 'value' => "{$dbSizeMb} MB", 'ok' => true],
            ['label' => 'Disk free', 'value' => "{$diskFreeGb} GB / {$diskTotalGb} GB", 'ok' => $diskFreeGb > 1],
            ['label' => 'Scheduler last run', 'value' => $schedulerSetting ? Carbon::parse($schedulerSetting)->diffForHumans() : 'unknown', 'ok' => true],
        ];

        return view('dashboard.system-health', [
            'checks' => $checks,
            'queueDepth' => $queueDepth,
            'failedJobs' => $failedJobs,
            'dbSizeMb' => $dbSizeMb,
            'diskFreeGb' => $diskFreeGb,
            'diskTotalGb' => $diskTotalGb,
            'diskUsedPct' => $diskTotalGb > 0 ? round(($diskTotalGb - $diskFreeGb) / $diskTotalGb * 100, 1) : 0,
            'schedulerLastRun' => $schedulerSetting ? Carbon::parse($schedulerSetting) : null,
            'failedRecent' => DB::table('failed_jobs')->latest('failed_at')->limit(5)->get(),
        ]);
    }

    private function databaseSizeMb(): string
    {
        try {
            $size = DB::selectOne('SELECT pg_database_size(current_database()) AS bytes')?->bytes
                ?? DB::selectOne('SELECT SUM(data_length + index_length) AS bytes FROM information_schema.tables')?->bytes
                ?? 0;

            return round($size / 1024 / 1024, 1);
        } catch (\Throwable) {
            return 'unknown';
        }
    }
}
