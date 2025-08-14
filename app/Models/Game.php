<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Game extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($game) {
            $game->slug = Str::slug($game->name);
        });

        static::updating(function ($game) {
            $game->slug = Str::slug($game->name);
        });
    }
    
    // Kolom yang boleh diisi secara massal
    protected $fillable = [
        'name',
        'publisher',
        'image_url',
        'header_image_url',
        'fields',
    ];

    // Konversi otomatis kolom JSON ke array PHP
    protected $casts = [
        'fields' => 'array',
    ];

    // Relasi ke tabel nominals (one-to-many)
    public function nominals()
    {
        return $this->hasMany(Nominal::class);
    }

}
