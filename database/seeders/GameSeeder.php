<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Game;

class GameSeeder extends Seeder
{
    public function run()
    {
        // Game::insert([
        //     [
        //         'name' => 'Mobile Legends',
        //         'publisher' => 'Moonton',
        //         'image_url' => 'http://127.0.0.1:8000/images/games/mlbb.png',
        //     ],
        //     [
        //         'name' => 'Free Fire',
        //         'publisher' => 'Garena',
        //         'image_url' => 'http://127.0.0.1:8000/images/games/ff.png',
        //     ],
            // Tambahkan game lainnya sesuai kebutuhan...
        // ]);
        // Game::find(1)->update([
        //     'fields' => json_encode([
        //         [ "name" => "userId", "label" => "ID", "placeholder" => "Masukkan ID" ],
        //         [ "name" => "serverId", "label" => "Server", "placeholder" => "Masukkan Server" ]
        //     ])
        // ]);
        // Game::find(2)->update([
        //     'fields' => json_encode([
        //         [ "name" => "userId", "label" => "ID", "placeholder" => "Masukkan ID" ],
        //     ])
        // ]);

        // Game::find(1)->update([
        //     'header_image_url' => 'http://127.0.0.1:8000/images/banners/mlbb.jpg'
        // ]);
        // Game::find(2)->update([
        //     'header_image_url' => 'http://127.0.0.1:8000/images/banners/ff.jpg'
        // ]);
    }
}
