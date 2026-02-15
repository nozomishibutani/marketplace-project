<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <title>商品購入</title>
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
        {{-- ログイン時のみ表示 --}}
        <nav class="header__nav">
            <ul>
                <li>ログアウト</li>
                <li>マイページ</li>
                <li>出品</li>
            </ul>
        </div>
    </header>

    <nav class="menu">
        <ul class="menu__nav">
            <li>おすすめ</li>
            <li>マイリスト</li>
        </ul>
    </nav>

    <main>
        <div class="item-detail">
            <form action="{{ route('purchase.store') }}" method="post">
            @csrf
                <div class="left-content">
                    <div class="item-info">
                        <img src="{{ asset('storage/' . $item->image) }}" alt="商品画像">
                        <div>
                            <h1>{{ $item->name }}</h1>
                            <p>¥{{ $item->price }}</p>
                            @if($soldFlg == true)
                                <p>sold</p>
                            @endif
                        </div>
                    </div>
                    <div class="payment-info">{{-- JSで連携させる--}}
                        <h2>支払い方法</h2>
                        <select name="payment_method">
                        <option hidden>選択してください</option>
                            @foreach(\App\Models\Order::PAYMENT_METHODS as $key => $label)
                                <option value="{{ $key }}">
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="address-info">
                        <div class="address-info__nav">
                            <h2>配送先</h2>
                            @if($soldFlg == true)
                                <a href="{{ route('purchase.edit', $item->id) }}" class="">非活性にする</a>
                            @elseif
                                <a href="{{ route('purchase.edit', $item->id) }}" class="">変更する</a>
                            @endif
                        </div>
                        <ul>
                            <li>
                                〒<input type="text" name="postcode" value="{{ session('address_edit.postcode') ?? $shippingAddress->postcode }}" readonly>
                            </li>
                            <li>
                                <input type="text" name="address" value="{{ session('address_edit.address') ?? $shippingAddress->address }}" readonly>
                            </li>
                            <li>
                                <input type="text" name="building" value="{{ session('address_edit.building') ?? $shippingAddress->building }}" readonly>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="right-content">
                    <div class="content-group">
                        <span>
                            <label for="">商品代金</label>
                        </span>
                        <span>
                            <label for="">¥{{ $item->price }}</label>
                        </span>
                    </div>
                    <div class="content-group">
                        <span>
                            <label for="">支払い方法</label>
                        </span>
                        <span>
                            <label for="">JS連携する</label>
                        </span>
                    </div>
                    <input type="hidden" name="user_id" value="1">
                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                    <input type="hidden" name="payment_method" value="1">
                    <input type="hidden" name="status" value="1">{{-- 今は不要 --}}
                    <button>購入する</button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>