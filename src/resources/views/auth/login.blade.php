@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}" />
@endsection

@section('content')
<div class="all__wrapper">
    <form class="login-form__wrapper" action="{{ route('login') }}" method="post">
        @csrf
        <h2 class="login-form__heading">
            ログイン
        </h2>
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
        <div class="login-form__button-area">
            <button type="submit" class="login-form__button-submit">ログインする</button>
        </div>
        <div class="login-form__link-area">
            <a href="{{ route('register') }}" class="login-form__link-register">会員登録はこちら</a>
        </div>
    </form>
</div>
@endsection