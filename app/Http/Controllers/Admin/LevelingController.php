<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LevelingController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Admin Leveling List']);
    }
}
