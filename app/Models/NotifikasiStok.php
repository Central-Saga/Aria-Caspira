<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotifikasiStok extends Model
{
    use HasFactory;

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
}

