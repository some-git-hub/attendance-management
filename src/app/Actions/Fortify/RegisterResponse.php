<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Illuminate\Support\Facades\Session;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * 登録後のレスポンスを返す
     */
    public function toResponse($request)
    {
        Session::put('newly_registered', true);

        return redirect()->route('mypage.edit');
    }
}