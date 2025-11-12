<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'                  => 'required|string|max:20',
            'email'                 => 'required|string|max:255|email|unique:users',
            'password'              => 'required|string|min:8',
            'password_confirmation' => 'required|string|min:8|same:password',
        ];
    }

    public function messages()
{
    return [
        'password_confirmation.same'      => 'パスワードと一致しません',
    ];
}

    public function attributes()
    {
        return [
            'name'     => 'お名前',
            'email'    => 'メールアドレス',
            'password' => 'パスワード',
            'password_confirmation' => '確認用パスワード',
        ];
    }
}
