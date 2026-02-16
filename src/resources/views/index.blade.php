<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <title>商品一覧</title>
</head>
<body>
    <header class="">
        <div class="header__logo">COACTTECH</div>
        <div class="header__search-form">
            <form action="" method="get">
                @csrf
                <input type="form" class="" value="" placeholder="なにをお探しですか？">
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

    <nav class="menu">
        <ul class="menu__nav">
            <li>おすすめ</li>
            <li>マイリスト</li>
        </ul>
    </nav>

    <main>
        @foreach ($items as $item)
            <a href="{{ route('items.show', $item->id) }}" class="item">
                <img src="{{ asset('storage/' . $item->image) }}" alt="商品画像">
                <p>{{ $item->name }}</p>
            </a>
        @endforeach
    </main>

</body>
</html>