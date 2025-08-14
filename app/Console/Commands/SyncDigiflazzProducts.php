<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Game;
use App\Models\Nominal;

class SyncDigiflazzProducts extends Command
{
    /**
     * Nama command artisan
     */
    protected $signature = 'sync:digiflazz-products {--markup_percent=0} {--markup_fixed=0}';

    /**
     * Deskripsi command
     */
    protected $description = 'Sinkronisasi produk Digiflazz ke database dengan markup harga jual';

    public function handle()
    {
        $api_key = env('DIGIFLAZZ_API_KEY');
        $username = env('DIGIFLAZZ_USERNAME');

        if (!$api_key || !$username) {
            $this->error("❌ ENV DIGIFLAZZ_API_KEY atau DIGIFLAZZ_USERNAME belum diset.");
            return;
        }

        $payload = [
            "cmd" => "prepaid",
            "username" => $username,
            "sign" => md5($username . $api_key . "pricelist")
        ];

        $response = Http::post('https://api.digiflazz.com/v1/price-list', $payload);

        if ($response->failed()) {
            $this->error("❌ Gagal tarik data dari Digiflazz");
            return;
        }

        $products = $response->json('data');

        // Ambil markup dari .env
        $markupPersen = (float) $this->option('markup_percent');
        $markupTetap = (float) $this->option('markup_fixed');

        foreach ($products as $p) {
            if ($p['category'] !== 'Games')
                continue;

            // Simpan Game jika belum ada
            $game = Game::firstOrCreate(
                ['name' => $p['brand']],
                [
                    'publisher' => 'Digiflazz',
                    'slug' => Str::slug($p['brand']),
                    'image_url' => null,
                    'header_url' => null,
                ]
            );

            // Hitung harga jual dengan markup
            $modal = (int) $p['price'];
            $hargaJual = $modal + ($modal * ($markupPersen / 100)) + $markupTetap;
            $hargaJual = ceil($hargaJual); // pembulatan ke atas

            // Simpan nominal/SKU
            Nominal::updateOrCreate(
                ['game_id' => $game->id, 'sku_code' => $p['buyer_sku_code']],
                [
                    'label' => $p['product_name'],
                    'price' => $hargaJual,
                    'modal_price' => $modal, // opsional: simpan harga modal
                    'active' => false, // default nonaktif, aktifkan manual di admin
                ]
            );
        }

        $this->info("✅ Sinkronisasi produk Digiflazz selesai dengan markup harga.");
    }
}
