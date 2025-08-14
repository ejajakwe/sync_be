<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Banner;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('banners')->truncate();
        Banner::insert([
            [
                'image_url' => 'http://127.0.0.1:8000/images/banners/hero1.jpg',
                'type' => 'hero',
            ],
            [
                'image_url' => 'http://127.0.0.1:8000/images/banners/hero2.jpg',
                'type' => 'hero',
            ],
            [
                'image_url' => 'http://127.0.0.1:8000/images/banners/hero3.jpg',
                'type' => 'hero',
            ],
            [
                'image_url' => 'http://127.0.0.1:8000/images/banners/joki1.jpg',
                'type' => 'joki',
            ],
            [
                'image_url' => 'http://127.0.0.1:8000/images/banners/joki2.jpg',
                'type' => 'joki',
            ],
            [
                'image_url' => 'http://127.0.0.1:8000/images/banners/joki3.jpg',
                'type' => 'joki',
            ],
        ]);
    }
}
