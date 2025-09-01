<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriBaju extends Model
{
    use HasFactory;

    protected $table = 'kategori_bajus';
    protected $fillable = ['nama_kategori'];
}