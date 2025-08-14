<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GameController extends Controller
{
    public function index()
    {
        return response()->json(Game::with('nominals')->get());
    }

    public function show($id)
    {
        $game = Game::with('nominals')->find($id);
        if (!$game) {
            return response()->json([
                'message' => 'Game tidak ditemukan'
            ], 404);
        }

        return response()->json($game);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'publisher' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'header_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'fields' => 'nullable|string',
            'nominals' => 'nullable|string',
        ]);

        $imagePath = $request->file('image')->store('images/games', 'public');
        $headerPath = $request->hasFile('header_image')
            ? $request->file('header_image')->store('images/banners', 'public')
            : null;

        $game = Game::create([
            'name' => $request->name,
            'publisher' => $request->publisher,
            'image_url' => asset('storage/' . $imagePath),
            'header_image_url' => $headerPath ? asset('storage/' . $headerPath) : null,
            'fields' => $request->fields,
        ]);

        if ($request->filled('nominals')) {
            $nominals = json_decode($request->nominals, true);
            foreach ($nominals as $n) {
                $game->nominals()->create([
                    'label' => $n['label'],
                    'price' => $n['price'],
                    'sku_code' => $n['sku_code'] ?? null,
                    'active' => $n['active'] ?? false,
                ]);
            }
        }

        return response()->json($game, 201);
    }

    public function update(Request $request, $id)
    {
        $game = Game::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string',
            'publisher' => 'sometimes|required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'header_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'fields' => 'nullable|string',
            'nominals' => 'nullable|string',
            'payment_methods' => 'nullable|string',
        ]);

        $data = $request->only('name', 'publisher');

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images/games', 'public');
            $data['image_url'] = asset('storage/' . $imagePath);
        }

        if ($request->hasFile('header_image')) {
            $headerPath = $request->file('header_image')->store('images/banners', 'public');
            $data['header_image_url'] = asset('storage/' . $headerPath);
        }

        if ($request->filled('fields')) {
            $data['fields'] = $request->fields;
        }

        if ($request->filled('nominals')) {
            $game->nominals()->delete(); // reset
            $nominals = json_decode($request->nominals, true);
            foreach ($nominals as $n) {
                $game->nominals()->create([
                    'label' => $n['label'],
                    'price' => $n['price'],
                    'sku_code' => $n['sku_code'] ?? null,
                    'active' => $n['active'] ?? false,
                ]);
            }
        }
        if ($request->has('payment_methods')) {
            $methods = json_decode($request->input('payment_methods'), true) ?? [];

            foreach ($methods as $i => &$method) {
                // Jika ada file upload untuk logo
                if ($request->hasFile("payment_method_logos.$i")) {
                    $image = $request->file("payment_method_logos.$i");
                    $path = $image->store("images/payments", "public");
                    $method['logo'] = asset("storage/" . $path); // URL yang bisa diakses frontend
                } else {
                    // Jika tidak ada file baru, pertahankan logo sebelumnya (jika ada)
                    if (!isset($method['logo']) || empty($method['logo'])) {
                        $method['logo'] = null;
                    }
                }
            }

            $game->payment_methods = json_encode($methods);
        }

        $game->update($data);

        return response()->json($game);
    }

    public function destroy($id)
    {
        $game = Game::findOrFail($id);
        $game->delete();

        return response()->json(['message' => 'Game berhasil dihapus']);
    }

    public function showBySlug($slug)
    {
        $game = Game::where('slug', $slug)->with('nominals')->firstOrFail();
        return response()->json($game);
    }
}
