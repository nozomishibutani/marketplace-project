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
        <h1>住所の変更</h1>
        <form action="{{ route('purchase.update') }}" method="post">
            <input type="hidden" name="item_id" value="{{ $item_id }}">
            @csrf
            <div>
                <label for="postcode">郵便番号</label>
                <input type="text" name="postcode" id="postcode">
            </div>

            <div>
                <label for="address">住所</label>
                <input type="text" name="address" id="address">
            </div>

            <div>
                <label for="building">建物名</label>
                <input type="text" name="building" id="building">
            </div>

            <button type="submit">更新する</button>
        </form>

    </main>

</body>
</html>