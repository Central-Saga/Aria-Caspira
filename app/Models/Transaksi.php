<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Transaksi extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'baju_id',
        'jenis_transaksi',
        'jumlah',
        'tanggal',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'jumlah' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function baju()
    {
        return $this->belongsTo(Baju::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('transaksi')
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Transaksi {$eventName}");
    }
}
