<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        $stats = [
            'pasien'      => 0,
            'obat'        => 0,
            'pengambilan' => 0,
            'distributor' => 0,
            'tanggal'     => now()->locale('id')->isoFormat('D MMM YYYY'),
        ];

        try {
            $stats['pasien'] = \Illuminate\Support\Facades\DB::table('pasien')->where('is_aktif', true)->count();
            $stats['obat']   = \Illuminate\Support\Facades\DB::table('obat')->where('is_active', true)->count();
            $stats['pengambilan'] = \Illuminate\Support\Facades\DB::table('pengambilan_obat')
                ->whereBetween('tanggal_pengambilan', \App\Support\Periode::bulan(now()->year, now()->month))
                ->count();
            $stats['distributor'] = \Illuminate\Support\Facades\DB::table('distributors')->count();
        } catch (\Throwable $e) {
            // Stats are decorative only — never block the login page.
        }

        return view('auth.login', compact('stats'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
