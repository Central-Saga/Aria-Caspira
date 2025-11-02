<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class NotifikasiStok extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'baju_id',
        'status',
        'pesan',
        'terbaca',
    ];

    protected $casts = [
        'terbaca' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function baju()
    {
        return $this->belongsTo(Baju::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('notifikasi-stok')
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Notifikasi stok {$eventName}");
    }
}
