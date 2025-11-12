<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" />
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <div class="header-logo">
                <img class="header-logo__image" src="{{ asset('images/logo.svg') }}" alt="Logo">
            </div>
            @unless (in_array(Route::currentRouteName(), ['login', 'register', 'verification.notice']))
            @auth
            <div class="header-nav">
                <form method="get" action="{{ route('attendance.create') }}">
                    <button class="nav-button" type="submit">勤怠</button>
                </form>
                <form method="get" action="{{ route('attendance.list') }}">
                    <button class="nav-button" type="submit">勤怠一覧</button>
                </form>
                <form method="get" action="{{ route('attendance.correction-list') }}">
                    <button class="nav-button" type="submit">申請</button>
                </form>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="nav-button" type="submit">ログアウト</button>
                </form>
            </div>
            @endauth
            @endunless
        </div>
    </header>

    <main>
    @yield('content')
    @yield('js')
    </main>
</body>
</html>