<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Leveling extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($leveling) {
            $leveling->slug = Str::slug($leveling->name);
        });

        static::updating(function ($leveling) {
            $leveling->slug = Str::slug($leveling->name);
        });
    }
    
    // Kolom yang boleh diisi secara massal
    protected $fillable = [
        'name',
        'publisher',
        'image_url',
        'header_image_url',
        'fields',
        'payment_methods',
    ];

    // Konversi otomatis kolom JSON ke array PHP
    protected $casts = [
        'fields' => 'array',
    ];

    // Relasi ke tabel nominals (one-to-many)
    public function nominals()
    {
        return $this->hasMany(Nominal::class, 'leveling_id');
    }
}