<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    @yield('css')
    @yield('title')
</head>

<body>
    <header class="header">
        <div class="container">
            <div class="header__inner">
                <div class="header__logo">
                    <a href="{{ route('items.index') }}">
                        <img src="{{ asset('/images/header_logo.png') }}" alt="ヘッダーロゴ画像">
                    </a>
                </div>
                <div class="header__search">
                    <form action="{{ route('search')}}" method="get" >
                        @if(request('tab'))
                            <input type="hidden" name="tab" value="{{ request('tab') }}">
                        @endif
                            <input type="text" class="search-form" name="keyword" value="{{ request('keyword') ?? null }}" placeholder="なにをお探しですか？">
                            <button class="">検索</button>
                    </form>
                </div>
                <nav class="header__nav">
                    <ul class="header__ul">
                        <li>
                            @if (Auth::check())
                                <form class="form" action="/logout" method="post">
                                @csrf
                                    <button class="header-nav__button">ログアウト</button>
                                </form>
                            @else
                                <a href="/login" class="header-nav__button">ログイン</a>
                            @endif
                        </li>
                        <li>
                            <a href="{{ route('profile.index') }}" class="header-nav__button">マイページ</a>
                        </li>
                        <li>
                            <a href="{{ route('items.create') }}" class="header-nav__button">出品</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </header>

        <main>
            {{-- マイページ画面のみ表示 --}}
            @yield('profile')

            {{-- 商品一覧画面、マイページのみ表示 --}}
            @yield('menu')

            @yield('content')
        </main>

    </div>

</body>

</html>