<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Baju extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'kategori_baju_id',
        'nama_baju',
        'ukuran',
        'warna',
        'harga',
        'stok_tersedia',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'stok_tersedia' => 'integer',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriBaju::class, 'kategori_baju_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('baju')
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Baju {$eventName}");
    }
}
