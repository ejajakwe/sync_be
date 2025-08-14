<?php

namespace App\Http\Middleware;

use App\Models\AdminToken;
use App\Models\Admin; // atau App\Models\User jika modelmu bernama User
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // 1) Ambil token dari cookie HttpOnly atau Authorization Bearer
            $raw = $request->cookie('admin_token') ?: $this->bearer($request);

            if (!$raw) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // 2) Hash token dan cek ke DB
            $hash = hash('sha256', $raw);

            $row = AdminToken::where('token_hash', $hash)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->first();

            if (!$row) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // (Opsional) verifikasi IP/UA ringan
            // if ($row->ip && $row->ip !== $request->ip()) {
            //     return response()->json(['message' => 'Unauthorized'], 401);
            // }

            // 3) Set user terotentikasi (jika perlu dipakai downstream)
            //   Sesuaikan modelnya: Admin / User
            $admin = Admin::find($row->admin_id);
            if ($admin) {
                Auth::setUser($admin);
            }

            // 4) (Opsional) rolling expiry
            // $row->update(['expires_at' => now()->addHours(8)]);

            return $next($request);
        } catch (\Throwable $e) {
            // Jangan bocorkan detail ke klien; balas 401 agar frontend tidak “mental-mental”
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    private function bearer(Request $request): ?string
    {
        $h = $request->header('Authorization');
        if (!$h || !str_starts_with($h, 'Bearer ')) {
            return null;
        }
        return substr($h, 7);
    }
}
