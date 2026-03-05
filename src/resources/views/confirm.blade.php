@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/confirm.css') }}">
@endsection

@section('title')
    <title>商品確認</title>
@endsection

@section('content')
    <main>
        <div class="item-detail">
                <div class="left-content">
                    <div class="item-info">
                        <img src="{{ asset('storage/' . $item->img) }}" alt="商品画像" width="200">
                        <div>
                            <h1>{{ $item->name }}</h1>
                            <p>¥{{ $item->price }}</p>
                            @if($itemStatuses['sold'] == true)
                                <p>Sold</p>
                            @endif
                            @if($itemStatuses['suspended'] == true)
                                <p>現在、この商品は出品を停止しています</p>
                            @endif
                        </div>
                    </div>
                    <div class="payment-info">
                        <form method="post" action="{{ route('purchase.confirm', $item->id) }}">
                        @csrf
                        <h2>支払い方法</h2>

                        <select class="payment-method" name="payment_method" onchange="this.form.submit()">
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
                            <div>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="address-info">
                        <div class="address-info__nav">
                            <h2>配送先</h2>
                            @if($itemStatuses['sold'] == false && $itemStatuses['suspended'] == false)
                                <form method="post" action="{{ route('purchase.edit', $item->id) }}">
                                @csrf
                                <input type="hidden" name="payment_method" value="{{ $paymentMethod }}">
                                <button type="submit" class="link-button">
                                    変更する
                                </button>
                            </form>
                            @endif
                        </div>
                            <form action="{{ route('purchase.store', $item->id) }}" method="post">
                        @csrf
                        <ul>
                            <li>
                                〒<input type="text" name="postcode" value="{{ old('postcode') ?? $address['postcode'] }}" readonly>
                                @error('postcode')
                                    <div>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </li>
                            <li>
                                <input type="text" name="address" value="{{ old('address') ?? $address['address'] }}" readonly>
                                @error('address')
                                    <div>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </li>
                            <li>
                                <input type="text" name="building" value="{{ old('building') ?? $address['building'] }}" readonly>
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
                            <p>{{ \App\Models\Order::PAYMENT_METHODS[$paymentMethod] ?? \App\Models\Order::PAYMENT_HIDDEN }}</p>
                            <input type="hidden" name="payment_method" value="{{ $paymentMethod ?? null }}">
                        </span>
                    </div>
                    @if($itemStatuses['sold'] == false && $itemStatuses['suspended'] == false)
                        <button>購入する</button>
                    @endif
                </div>
            </form>
        </div>
@endsection