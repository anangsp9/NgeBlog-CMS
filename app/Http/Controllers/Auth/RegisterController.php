<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        // Kata sandi sengaja diteruskan mentah: cast 'hashed' pada model User
        // sudah menghash-nya. Memanggil Hash::make() di sini berarti dua kali hash
        // yang menyesatkan pembaca.
        $user = User::create($request->userAttributes());

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('home');
    }
}
