<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        return response()->json(Banner::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:hero,joki',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $path = $request->file('image')->store('images/banners', 'public');

        $banner = Banner::create([
            'type' => $request->type,
            'image_url' => asset('storage/' . $path),
        ]);

        return response()->json($banner, 201);
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'type' => 'required|string|in:hero,joki',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = ['type' => $request->type];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images/banners', 'public');
            $data['image_url'] = asset('storage/' . $path);
        }

        $banner->update($data);

        return response()->json($banner);
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();
        return response()->json(['message' => 'Banner berhasil dihapus']);
    }
}