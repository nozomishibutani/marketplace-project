<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
    @yield('title')
</head>

<body>
    <header class="header">
        <div class="header__container">
                <div class="header__logo">
                    <a href="{{ route('items.index') }}">
                        <img src="{{ asset('/images/header_logo.png') }}" alt="ヘッダーロゴ画像">
                    </a>
                </div>
                <div class="header__search">
                    <form class="header__search-form" action="{{ route('search')}}" method="get" >
                        @if(request('tab'))
                            <input type="hidden" name="tab" value="{{ request('tab') }}">
                        @endif
                            <input type="text" class="header__search-input" name="keyword" value="{{ request('keyword') ?? null }}" placeholder="なにをお探しですか？">
                    </form>
                </div>
                <nav class="header__nav">
                    <ul class="header__list">
                        <li class="header__item">
                            @if (Auth::check())
                                <form class="header__logout-form" action="/logout" method="post">
                                @csrf
                                    <button class="header__btn">ログアウト</button>
                                </form>
                            @else
                                <a href="/login" class="header__link">ログイン</a>
                            @endif
                        </li>
                        <li class="header__item">
                            <a href="{{ route('profile.index') }}" class="header__link">マイページ</a>
                        </li>
                        <li class="header__item">
                            <a href="{{ route('items.create') }}" class="header__link header__link--sell">出品</a>
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

        <div class="main__container">
            @yield('content')
        </div>
    </main>
    <!-- jQuery 読み込み -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- 個別JS -->
    @yield('js')
</body>
</html>