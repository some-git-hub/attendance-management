<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerifyController extends Controller
{
    /**
     *  メール認証誘導画面の表示
     */
    public function verifyNotice()
    {
        return view('auth.verify-email');
    }


    /**
     *  メール認証処理
     */
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect()->route('attendance.create');
    }


    /**
     *  メール認証用通知の再送信
     */
    public function resend(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
