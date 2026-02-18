<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    @yield('css')
    @yield('jquery')
    @yield('title')
</head>

<body>
    <header class="">
        <div class="header__logo">
            <a href="{{ route('items.index') }}" class="header__img">
                <img src="{{ asset('/images/header_logo.png') }}" alt="ヘッダーロゴ画像">
            </a>
        </div>
        <div class="header__search-form">
            <form action="{{ route('search')}}" method="get">
                <input type="form" class="" name="keyword" value="{{ session('keyword') ?? null }}" placeholder="なにをお探しですか？">
                <button class="">検索</button>
            </form>
        </div>
        <nav class="header__nav">
            <ul>
                @if (Auth::check())
                    <form class="form" action="/logout" method="post">
                    @csrf
                        <button class="header-nav__button">ログアウト</button>
                    </form>
                @else
                    <a href="/login" class="header-nav__button">ログイン</a>
                @endif
                <li>マイページ</li>
                <li>出品</li>
            </ul>
        </nav>
    </header>

    {{-- 商品一覧画面のみ表示 --}}
    @yield('menu')

    <main>
        @yield('content')
    </main>

    @yield('js')
</body>

</html>