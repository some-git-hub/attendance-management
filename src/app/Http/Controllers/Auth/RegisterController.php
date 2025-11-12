<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Actions\Fortify\CreateNewUser;


class RegisterController extends Controller
{
    /**
     *  新規登録画面の表示(一般ユーザー)
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }


    /**
     *  新規登録機能(一般ユーザー)
     */
    public function store(RegisterRequest $request, CreateNewUser $creator)
    {
        $user = $creator->create($request->validated());

        Auth::login($user);
        $request->session()->regenerate();

        // メール認証用通知の送信
        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice');
    }
}
