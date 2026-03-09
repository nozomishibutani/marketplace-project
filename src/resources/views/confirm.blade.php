@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/confirm.css') }}">
@endsection

@section('title')
    <title>商品確認</title>
@endsection

@section('content')
        <div class="purchase">
            <!-- 左側: 入力フォーム -->
            <div class="purchase__form">

                <div class="item">
                    <!-- 商品画像 -->
                    <div class="item__img">
                        <img src="{{ asset('storage/' . $item->img) }}" alt="商品画像">
                    </div>
                    <div class="item___information">
                        @if($itemStatuses['suspended'] == true)
                            <p class="msg msg--suspended">現在、この商品は出品を停止しています</p>
                        @endif
                        <!-- 商品名 -->
                        <h1 class="item__ttl">{{ $item->name }}
                            @if($itemStatuses['sold'] == true)
                                <span class="item__sold">Sold</span>
                            @endif
                        </h1>
                        <!-- 価格 -->
                        <p class="item__price">
                            <span class="item__price-symbol">¥</span>
                            {{ $item->price }}
                            <span class="item__price-tax">(税込)</span>
                        </p>
                    </div>
                </div>

                <!-- 支払い方法 -->
                <section class="payment">
                    <form method="post" action="{{ route('purchase.confirm', $item->id) }}">
                    @csrf
                    <h2 class="payment__ttl">支払い方法</h2>
                    <select class="payment__select-box" name="payment_method" onchange="this.form.submit()">
                        <option hidden>
                            {{ \App\Models\Order::PAYMENT_HIDDEN }}
                        </option>
                        @foreach(\App\Models\Order::PAYMENT_METHODS as $key => $label)
                            <option value="{{ $key }}"
                                {{ $paymentMethod == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="postcode" value="{{ $address['postcode'] }}">
                    <input type="hidden" name="address" value="{{ $address['address'] }}">
                    <input type="hidden" name="building" value="{{ $address['building'] }}">
                    </form>
                    @error('payment_method')
                        <div class="msg  payment__msg">
                            {{ $message }}
                        </div>
                    @enderror
                </section>

                <!-- 配送先-->
                <section class="address">
                    <div class="address__nav">
                        <h2 class="address__ttl">配送先</h2>
                        <!-- 配送先を変更する-->
                        <div class="btn-box">
                            @if($itemStatuses['sold'] == false && $itemStatuses['suspended'] == false)
                                <form method="post" action="{{ route('purchase.edit', $item->id) }}">
                                @csrf
                                    <input type="hidden" name="payment_method" value="{{ $paymentMethod }}">
                                    <button class="address__btn">変更する</button>
                                </form>
                            @else
                                <button class="address__btn btn--disabled" disabled>変更する</button>
                            @endif
                        </div>
                    </div>

                    <!-- 現在の配送先の表示-->
                    <form action="{{ route('purchase.store', $item->id) }}" method="post">
                    @csrf
                        <ul class="address__list">
                            <li lass="address__item">
                                <span class="address__postcode-symbol">〒</span>
                                <input class="address__item-form" type="text" name="postcode" value="{{ old('postcode') ?? $address['postcode'] }}" readonly>
                            </li>
                            <li lass="address__item">
                                <input class="address__item-form" type="text" name="address" value="{{ old('address') ?? $address['address'] }}" readonly>
                            </li>
                            <li lass="address__item">
                                <input class="address__item-form" type="text" name="building" value="{{ old('building') ?? $address['building'] }}" readonly>
                            </li>
                        </ul>
                        <!-- バリデーション表示-->
                        @php
                            $fields = ['postcode', 'address', 'building'];
                        @endphp
                        @foreach($fields as $field)
                            @error($field)
                                <div class="msg address__msg">{{ $message }}</div>
                            @enderror
                        @endforeach
                </section>
            </div><!-- purchase__form -->

            <!-- 右側: 入力内容表示 -->
            <ul class="purchase__summary">
                <li class="purchase__item">
                    <h3 class="purchase__label">商品代金</h3>
                    <span class="purchase__tag">¥{{ $item->price }}</span>
                </li>
                <li class="purchase__item">
                    <h3 class="purchase__label">支払い方法</h3>
                    <span class="purchase__tag">{{ \App\Models\Order::PAYMENT_METHODS[$paymentMethod] ?? \App\Models\Order::PAYMENT_HIDDEN }}</span>
                </li>
                <li class="btn-box purchase__btn-box">
                    @if($itemStatuses['sold'] == false && $itemStatuses['suspended'] == false)
                        <button class="btn">購入する</button>
                    @else
                        <button class="btn btn--disabled" disabled>購入する</button>
                    @endif
                </li>
            </ul><!-- purchase__summary -->
            <input type="hidden" name="payment_method" value="{{ $paymentMethod ?? null }}">
        </form>
    </div><!-- purchase-->
@endsection