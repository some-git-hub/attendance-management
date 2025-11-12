@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/register.css') }}" />
@endsection

@section('content')
<div class="all__wrapper">
    <form class="register-form__wrapper" action="{{ route('register') }}" method="post">
        @csrf
        <h2 class="register-form__heading">
            会員登録
        </h2>

        <!-- 名前の入力欄 -->
        <div class="register-form__container">
            <label class="register-form__label">
                名前
            </label>
            <div class="register-form__inner">
                <div class="register-form__input-area">
                    <input class="register-form__input" type="text" maxlength="20" name="name" value="{{ old('name') }}">
                </div>
                @error('name')
                <div class="register-form__error-message">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>

        <!-- メールアドレスの入力欄 -->
        <div class="register-form__container">
            <label class="register-form__label">
                メールアドレス
            </label>
            <div class="register-form__inner">
                <div class="register-form__input-area">
                    <input class="register-form__input" type="text" maxlength="255" name="email" value="{{ old('email') }}">
                </div>
                @error('email')
                <div class="register-form__error-message">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>

        <!-- パスワードの入力欄 -->
        <div class="register-form__container">
            <label class="register-form__label">
                パスワード
            </label>
            <div class="register-form__inner">
                <div class="register-form__input-area">
                    <input class="register-form__input" type="password" name="password">
                </div>
                @error('password')
                <div class="register-form__error-message">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>

        <!-- 確認用パスワードの入力欄 -->
        <div class="register-form__container">
            <label class="register-form__label">
                パスワード確認
            </label>
            <div class="register-form__inner">
                <div class="register-form__input-area">
                    <input class="register-form__input" type="password" name="password_confirmation">
                </div>
                @error('password_confirmation')
                <div class="register-form__error-message">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>

        <!-- 新規登録ボタン -->
        <div class="register-form__button-area">
            <button type="submit" class="register-form__button-submit">登録する</button>
        </div>

        <!-- ログインリンク -->
        <div class="register-form__link-area">
            <a href="{{ route('login') }}" class="register-form__link-login">ログインはこちら</a>
        </div>
    </form>
</div>
@endsection