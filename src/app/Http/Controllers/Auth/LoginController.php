<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;

class LoginController extends Controller
{
    /**
     * ログイン画面の表示(一般ユーザー)
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }


    /**
     * ログイン機能(一般ユーザー)
     */
    public function store(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->role === 0) {
                return redirect()->intended(route('attendance.create'));
            } else {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'ログイン情報が登録されていません'
                ]);
            }
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}
