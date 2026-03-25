<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Baju extends Model
{
    use HasFactory, LogsActivity;

    protected static function booted(): void
    {
        static::deleting(function (Baju $baju): void {
            $baju->transaksis()->delete();
            $baju->notifikasiStoks()->delete();

            if ($baju->gambar) {
                Storage::disk('public')->delete($baju->gambar);
            }
        });
    }

    protected $fillable = [
        'kategori_baju_id',
        'nama_baju',
        'ukuran',
        'warna',
        'harga',
        'stok_tersedia',
        'gambar',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'stok_tersedia' => 'integer',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriBaju::class, 'kategori_baju_id');
    }

    public function notifikasiStoks(): HasMany
    {
        return $this->hasMany(NotifikasiStok::class);
    }

    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class);
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
