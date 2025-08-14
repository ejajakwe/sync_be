<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'q'        => 'required|string|min:1|max:100',
            'type'     => 'sometimes|in:all,game,leveling',
            'page'     => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:50',
        ]);

        $q        = $data['q'];
        $type     = $data['type'] ?? 'all';
        $perPage  = $data['per_page'] ?? 20;

        $response = [
            'query' => $q,
            'results' => [
                'games'     => [],
                'levelings' => [],
            ],
            'meta' => [
                'page'      => (int)($data['page'] ?? 1),
                'per_page'  => (int)$perPage,
            ],
        ];

        // Games
        if ($type === 'all' || $type === 'game') {
            $games = DB::table('games')
                ->select('id','name','slug','publisher','image_url')
                ->where(function($w) use ($q) {
                    $w->where('name','like',"%{$q}%")
                      ->orWhere('publisher','like',"%{$q}%");
                })
                ->orderBy('name')
                ->paginate($perPage);

            $response['results']['games'] = $games->items();
            $response['meta']['games'] = [
                'total' => $games->total(),
                'last_page' => $games->lastPage(),
            ];
        }

        // Levelings
        if ($type === 'all' || $type === 'leveling') {
            $levelings = DB::table('levelings')
                ->select('id','name','slug','publisher','image_url')
                ->where(function($w) use ($q) {
                    $w->where('name','like',"%{$q}%")
                      ->orWhere('publisher','like',"%{$q}%");
                })
                ->orderBy('name')
                ->paginate($perPage);

            $response['results']['levelings'] = $levelings->items();
            $response['meta']['levelings'] = [
                'total' => $levelings->total(),
                'last_page' => $levelings->lastPage(),
            ];
        }

        return response()->json($response);
    }

    // Saran cepat untuk dropdown (maks 5 item)
    public function suggest(Request $request)
    {
        $data = $request->validate([
            'q'    => 'required|string|min:1|max:100',
            'type' => 'sometimes|in:all,game,leveling',
            'limit'=> 'sometimes|integer|min:1|max:10',
        ]);

        $q     = $data['q'];
        $type  = $data['type'] ?? 'all';
        $limit = $data['limit'] ?? 5;

        $res = ['games'=>[], 'levelings'=>[]];

        if ($type === 'all' || $type === 'game') {
            $res['games'] = DB::table('games')
                ->select('id','name','slug','image_url')
                ->where('name','like',"%{$q}%")
                ->orderBy('name')
                ->limit($limit)
                ->get();
        }

        if ($type === 'all' || $type === 'leveling') {
            $res['levelings'] = DB::table('levelings')
                ->select('id','name','slug','image_url')
                ->where('name','like',"%{$q}%")
                ->orderBy('name')
                ->limit($limit)
                ->get();
        }

        return response()->json([
            'query' => $q,
            'suggestions' => $res
        ]);
    }
}