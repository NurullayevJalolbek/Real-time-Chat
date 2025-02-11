<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthUserController extends Controller
{
    public function login(Request $request)
    {
        // Validatsiya
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
    
        if (Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            return redirect()->route('app');
        }
    
        return back()->withErrors(['email' => "Email yoki parol noto'g'ri."])->withInput();
    }
    

    public function register(Request $request)
    {
        // Validatsiya
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        if (User::where('email', $validated['email'])->exists()) {
            return back()->withErrors(['email' => "Bu email manzili allaqachon ro\'yxatdan o\'tgan."])->withInput();
        }
        // Foydalanuvchini yaratish
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Agar foydalanuvchi muvaffaqiyatli yaratildi
        if ($user) {
            Auth::login($user);
            return redirect()->route('app');
        }
        // Agar foydalanuvchi yaratilmadi
        return back()->withErrors(['email' => "Email yoki parol noto'g'ri."])->withInput();
    }



    public function forgotPassword(Request $request)
    {
        return view('auth.forgot-password');
    }
}
