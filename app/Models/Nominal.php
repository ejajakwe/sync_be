<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nominal extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi melalui mass assignment
     */
    protected $fillable = [
        'game_id',
        'leveling_id',
        'label',
        'price',
        'sku_code',
        'active',
    ];

    /**
     * Relasi ke model Game
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Relasi ke model Leveling
     */
    public function leveling()
    {
        return $this->belongsTo(Leveling::class);
    }

    /**
     * Casting otomatis jika diperlukan (opsional)
     */
    protected $casts = [
        'price' => 'float',
    ];
}
