<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Activitylog\Models\Activity;

class PruneActivityLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $days;

    public function __construct(int $days)
    {
        $this->days = $days;
    }

    public function handle(): void
    {
        $cutoff = now()->subDays($this->days);
        Activity::query()
            ->where('created_at', '<', $cutoff)
            ->chunkById(1000, function ($batch) {
                $ids = $batch->pluck('id');
                Activity::whereIn('id', $ids)->delete();
            });
    }
}

