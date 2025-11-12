@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}" />
@endsection

@section('content')
<div class="all__wrapper">
    <form class="login-form__wrapper" action="{{ route('admin.login') }}" method="post">
        @csrf
        <h2 class="login-form__heading">
            管理者ログイン
        </h2>

        <!-- メールアドレスの入力欄 -->
        <div class="login-form__container">
            <label class="login-form__label">
                メールアドレス
            </label>
            <div class="login-form__inner">
                <div class="login-form__input-area">
                    <input class="login-form__input" type="text" maxlength="255" name="email" value="{{ old('email') }}">
                </div>
                @error('email')
                <div class="login-form__error-message">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>

        <!-- パスワードの入力欄 -->
        <div class="login-form__container">
            <label class="login-form__label">
                パスワード
            </label>
            <div class="login-form__inner">
                <div class="login-form__input-area">
                    <input class="login-form__input" type="password" name="password">
                </div>
                @error('password')
                <div class="login-form__error-message">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>

        <!-- 管理者ログインボタン -->
        <div class="login-form__button-area">
            <button type="submit" class="login-form__button-submit">管理者ログインする</button>
        </div>
    </form>
</div>
@endsection