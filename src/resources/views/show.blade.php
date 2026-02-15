<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/show.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <title>商品詳細</title>
</head>

<header class="">
    <div class="header__logo">COACTTECH</div>
    <div class="header__search-form">
        <form action="" method="get">
            @csrf
            <input type="form" class="" value="" placeholder="なにをお探しですか？">
            <button class="">検索</button>
        </form>
    </div>
    {{-- ログイン時の表示 --}}
    <nav class="header__nav">
        <ul>
            <li>ログアウト</li>
            <li>マイページ</li>
            <li>出品</li>
        </ul>
    <nav>
</header>


    <body>
        <main>
                <div class="item-detail">
                    <div class="item-image">
                        <img src="{{ asset('storage/' . $item->image) }}" alt="商品画像">
                    </div>

                    <div class="item-info">
                        <h1>{{ $item->name }}</h1>
                        @if($soldFlg == true)
                            <p>sold</p>
                        @endif
                        <p>{{ $item->brand_name }}</p>
                        <p>¥{{ $item->price }}(税込)</p>
                        <div class="button">
                            <button class="like">はーと</button>
                            <span>3</span>
                            <img src="{{ asset('storage/' . $item->image) }}" alt="商品画像">
                            <span>1</span>
                        </div>
                        @if($soldFlg == true)
                            <a href="{{ route('purchase.confirm', $item->id) }}" class="btn">非活性にする</a>
                        @else
                            <a href="{{ route('purchase.confirm', $item->id) }}" class="btn">購入手続きへ</a>
                        @endif

                        <h2>商品説明</h2>
                        <p>{{ $item->description }}</p>

                        <h2>商品の情報</h2>
                        <div class="category">
                            <span>
                                <label for="">カテゴリー</label>
                            </span>
                            <span>
                                <label for="">{{ $item->category->name ?? '' }}</label>{{-- 複数表示させるようにする--}}
                            </span>
                        </div>
                        <div class="status">
                            <span>
                                <label for="">商品の状態</label>
                            </span>
                            <span>
                                <label for="">{{ \App\Models\Item::CONDITIONS[$item->status] ?? '' }}</label>
                            </span>
                        </div>
                        <P class="">コメント{{-- コメントの数表示 --}}</P>
                        <div class="profile">
                            <img src="{{ asset('storage/' . $item->image) }}" alt="商品画像">
                            <span>
                                <label for="">{{ $item->user_name ?? '' }}</label>
                            </span>
                        </div>
                        <p>{{-- commentテーブルから表示 --}}</p>
                        <h3>商品へのコメント</h3>
                        <form action="" method="post">
                            @csrf
                            <textarea name="" id=""></textarea>
                            <button>コメントを送信する</button>
                        </form>

                    </div>
                </div>
        </main>
    </body>
</html>