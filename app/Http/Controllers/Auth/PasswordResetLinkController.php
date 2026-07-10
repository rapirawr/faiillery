<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $temporaryPassword = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $user->forceFill([
                'password' => Hash::make($temporaryPassword),
                'remember_token' => Str::random(60),
            ])->save();

            return back()
                ->withInput($request->only('email'))
                ->with('status', 'Password sementara telah dibuat.')
                ->with('temporary_password', $temporaryPassword)
                ->with('warning', 'Harap ganti kata sandi setelah login menggunakan kode tersebut.');
        }

        return back()
            ->withInput($request->only('email'))
            ->with('status', 'Jika email terdaftar, password sementara akan dibuat dan ditampilkan di layar.')
            ->with('warning', 'Harap ganti kata sandi setelah login dengan password sementara tersebut.');
    }
}
