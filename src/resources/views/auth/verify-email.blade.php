@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}" />
@endsection

@section('content')
<div class="all__wrapper">
    <div class="verify-email__message-area">
        <p class="verify-email__message-1">登録していただいたメールアドレスに認証メールを送付しました。</p>
        <p class="verify-email__message-2">メール認証を完了してください。</p>
    </div>

    <p class="verify-email__link-area">
        <a href="http://localhost:8025/" target="_blank" class="verify-email__link-verification">認証はこちらから</a>
    </p>

    <form method="post" action="{{ route('verification.send') }}" class="verify-email__form-resend">
        @csrf
        <button type="submit" class="verify-email__button-resend">認証メールを再送する</button>
    </form>
</div>
@endsection