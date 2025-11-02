<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\PruneActivityLog;

class PruneActivityLogCommand extends Command
{
    protected $signature = 'activitylog:prune {--days=}';
    protected $description = 'Prune activity logs older than the given number of days';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? env('ACTIVITYLOG_RETENTION_DAYS', 90));
        if ($days <= 0) {
            $this->error('Invalid days. Provide --days or set ACTIVITYLOG_RETENTION_DAYS.');
            return self::FAILURE;
        }

        // Dispatch job synchronously when running in console to ensure completion
        (new PruneActivityLog($days))->handle();

        $this->info("Pruned logs older than {$days} days.");
        return self::SUCCESS;
    }
}

