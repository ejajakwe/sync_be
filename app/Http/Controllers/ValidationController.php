<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GameValidationService;

class ValidationController extends Controller
{
    public function check(Request $r, string $game)
    {
        $svc = new GameValidationService();

        // Peta game → provider & code
        $MAP = [
            'mobile-legends' => ['provider' => 'duniagames_ml', 'needsZone' => true],
            'free-fire' => ['provider' => 'vocagame', 'code' => 'FREEFIRE', 'needsZone' => false],
            'pubg-mobile' => ['provider' => 'vocagame', 'code' => 'PUBG_MOBILE', 'needsZone' => false],
            'genshin-impact' => ['provider' => 'vocagame', 'code' => 'GENSHIN_IMPACT', 'needsZone' => true],
            // perbaikan penting di bawah:
            'call-of-duty-mobile' => ['provider' => 'vocagame', 'code' => 'CALL_OF_DUTY', 'needsZone' => false],
            'honor-of-kings' => ['provider' => 'vocagame', 'code' => 'HOK', 'needsZone' => false],
        ];


        if (!isset($MAP[$game])) {
            return response()->json(['ok' => false, 'message' => 'Game belum didukung.'], 400);
        }

        $cfg = $MAP[$game];
        $user = (string) $r->input('game_id');     // userId / UID
        $zone = $r->input('zone_id');             // server / zone (opsional)

        // Validasi minimal input
        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'game_id wajib diisi.'], 422);
        }
        if (!empty($cfg['needsZone']) && !$zone) {
            return response()->json(['ok' => false, 'message' => 'zone_id wajib diisi.'], 422);
        }

        // Dispatch ke provider
        switch ($cfg['provider']) {
            case 'duniagames_ml':
                $out = $svc->checkDuniaGamesML($user, (string) $zone);
                break;
            case 'vocagame':
                $out = $svc->checkVocagame($cfg['code'], $user, $zone);
                break;
            default:
                $out = ['ok' => false, 'message' => 'Provider belum diimplementasikan.'];
        }

        return response()->json($out, $out['ok'] ? 200 : 404);
    }
}
