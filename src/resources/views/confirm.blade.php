<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
        <div class="item-detail">
            <form action="{{ route('purchase.store') }}" method="post">
            @csrf
                <div class="left-content">
                    <div class="item-info">
                        <img src="{{ asset('storage/' . $item->image) }}" alt="商品画像">
                        <div>
                            <h1>{{ $item->name }}</h1>
                            <p>¥{{ $item->price }}</p>
                            @if($isSold == true)
                                <p>sold</p>
                            @endif
                        </div>
                    </div>
                    <div class="payment-info">
                        <h2>支払い方法</h2>
                        <select class="payment-method" name="payment_method">
                        <option hidden>選択してください</option>
                            @foreach(\App\Models\Order::PAYMENT_METHODS as $key => $label)
                                <option value="{{ $key }}" {{ old('payment_method') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_method')
                            <div>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="address-info">
                        <div class="address-info__nav">
                            <h2>配送先</h2>
                            @if($isSold == true)
                                <a href="{{ route('purchase.edit', $item->id) }}" class="">非活性にする</a>
                            @else
                                <a href="{{ route('purchase.edit', $item->id) }}" class="">変更する</a>
                            @endif
                        </div>
                        <ul>
                            <li>
                                〒<input type="text" name="postcode" value="{{ old('postcode') ?? $profileAddress->postcode }}" readonly>
                                @error('postcode')
                                    <div>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </li>
                            <li>
                                <input type="text" name="address" value="{{ old('address') ?? $profileAddress->address }}" readonly>
                                @error('address')
                                    <div>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </li>
                            <li>
                                <input type="text" name="building" value="{{ old('building') ?? $profileAddress->building }}" readonly>
                                @error('building')
                                    <div>
                                        {{ $message }}
                                    </div>
                                @enderror
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
                            <p class="selected-payment"></p>
                        </span>
                    </div>
                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                    <button id>購入する</button>
                </div>
            </form>
        </div>
    </main>
<script src="{{ asset('js/payment-method.js') }}"></script>
</body>
</html>