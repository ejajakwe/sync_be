<?php

namespace App\Http\Controllers;

use App\Models\Leveling;
use App\Models\Nominal;
use Illuminate\Http\Request;

class LevelingController extends Controller
{
    public function index()
    {
        return response()->json(Leveling::with('nominals')->get());
    }

    public function show($id)
    {
        $leveling = Leveling::with('nominals')->find($id);
        if (!$leveling) {
            return response()->json(['message' => 'Leveling tidak ditemukan'], 404);
        }

        return response()->json($leveling);
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

        $imagePath = $request->file('image')->store('images/levelings', 'public');
        $headerPath = $request->hasFile('header_image')
            ? $request->file('header_image')->store('images/banners', 'public')
            : null;

        $leveling = Leveling::create([
            'name' => $request->name,
            'publisher' => $request->publisher,
            'image_url' => asset('storage/' . $imagePath),
            'header_image_url' => $headerPath ? asset('storage/' . $headerPath) : null,
            'fields' => $request->fields,
        ]);

        if ($request->filled('nominals')) {
            $nominals = json_decode($request->nominals, true);
            foreach ($nominals as $n) {
                Nominal::create([
                    'label' => $n['label'],
                    'price' => $n['price'],
                    'leveling_id' => $leveling->id,
                ]);
            }
        }

        return response()->json($leveling, 201);
    }

    public function update(Request $request, $id)
    {
        $leveling = Leveling::findOrFail($id);

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
            $imagePath = $request->file('image')->store('images/levelings', 'public');
            $data['image_url'] = asset('storage/' . $imagePath);
        }

        if ($request->hasFile('header_image')) {
            $headerPath = $request->file('header_image')->store('images/banners', 'public');
            $data['header_image_url'] = asset('storage/' . $headerPath);
        }

        if ($request->filled('fields')) {
            $data['fields'] = $request->fields;
        }

        if ($request->has('payment_methods')) {
            $methods = json_decode($request->input('payment_methods'), true) ?? [];

            foreach ($methods as $i => &$method) {
                if ($request->hasFile("payment_method_logos.$i")) {
                    $image = $request->file("payment_method_logos.$i");
                    $path = $image->store("images/payments", "public");
                    $method['logo'] = asset("storage/" . $path);
                } elseif (!isset($method['logo'])) {
                    $method['logo'] = null;
                }
            }

            $data['payment_methods'] = json_encode($methods);
        }

        $leveling->update($data);

        if ($request->filled('nominals')) {
            $leveling->nominals()->delete();
            $nominals = json_decode($request->nominals, true);

            foreach ($nominals as $n) {
                Nominal::create([
                    'label' => $n['label'],
                    'price' => $n['price'],
                    'leveling_id' => $leveling->id,
                ]);
            }
        }

        return response()->json($leveling);
    }

    public function destroy($id)
    {
        $leveling = Leveling::findOrFail($id);
        $leveling->delete();

        return response()->json(['message' => 'Leveling berhasil dihapus']);
    }

    public function showBySlug($slug)
    {
        $leveling = Leveling::where('slug', $slug)->with('nominals')->firstOrFail();
        return response()->json($leveling);
    }
}