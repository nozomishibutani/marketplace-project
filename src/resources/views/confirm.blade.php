@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/confirm.css') }}">
@endsection

@section('jquery')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
@endsection

@section('title')
    <title>商品確認</title>
@endsection

@section('content')
    <main>
        <div class="item-detail">
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
                    <form method="post" action="{{ route('purchase.payment', $item->id) }}">
                        @csrf
                        <h2>支払い方法</h2>

                        <select class="payment-method" name="payment_method" onchange="this.form.submit()">
                            <option hidden>
                                {{ \App\Models\Order::PAYMENT_HIDDEN }}
                            </option>

                            @foreach(\App\Models\Order::PAYMENT_METHODS as $key => $label)
                                <option value="{{ $key }}"
                                    {{ request('payment_method') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        <input type="hidden" name="postcode"
                            value="{{ request('postcode') ?? $profileAddress->postcode }}">

                        <input type="hidden" name="address"
                            value="{{ request('address') ?? $profileAddress->address }}">

                        <input type="hidden" name="building"
                            value="{{ request('building') ?? $profileAddress->building }}">
                    </form>
                        @error('payment_method')
                            <div>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <form action="{{ route('purchase.store', $item->id) }}" method="post">
                    @csrf
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
                            <p>{{ \App\Models\Order::PAYMENT_METHODS[$paymentMethod] ?? \App\Models\Order::PAYMENT_HIDDEN }}</p>
                            <input type="hidden" name="payment_method" value="{{ $paymentMethod }}">
                        </span>
                    </div>
                    <button id>購入する</button>
                </div>
            </form>
        </div>
@endsection

@section('js')
    {{--<script src="{{ asset('js/payment-method.js') }}"></script>--}}
@endsection
