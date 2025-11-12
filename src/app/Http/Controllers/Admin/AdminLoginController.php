<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    /**
     * ログイン画面の表示(管理者)
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }


    /**
     * ログイン処理(管理者)
     */
    public function store(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->role === 1) {
                return redirect()->route('admin.attendance.list');
            } else {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'ログイン情報が登録されていません'
                ]);
            }
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);
    }
}
