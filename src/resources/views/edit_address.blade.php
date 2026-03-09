
@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/edit-address.css') }}">
@endsection

@section('title')
    <title>住所変更</title>
@endsection

@section('content')

<div class="address">
    <h1 class="address-ttl">住所の変更</h1>
        <form action="{{ route('purchase.update', $item_id) }}" method="post">
        @csrf
        <ul class="address__list">
            <li class="address__item">
                <label class="address__label" for="postcode">郵便番号</label>
                <input class="address__form-input" type="text" name="postcode" id="postcode" value="{{ old('postcode') }}">
                @error('postcode')
                    <div class="msg">
                        {{ $message }}
                    </div>
                @enderror
            </li>

            <li class="address__item">
                <label class="address__label" for="address">住所</label>
                <input class="address__form-input" type="text" name="address" id="address" value="{{ old('address') }}">
                @error('address')
                    <div class="msg">
                        {{ $message }}
                    </div>
                @enderror
            </li>

            <li class="address__item">
                <label class="address__label" for="building">建物名</label>
                <input class="address__form-input" type="text" name="building" id="building" value="{{ old('building') }}">
                @error('building')
                    <div class="msg">
                        {{ $message }}
                    </div>
                @enderror
            </li>
        </ul>
        <div class="btn-box address__btn-box">
            <button class="btn">更新する</button>
        </div>
        <input type="hidden" name="payment_method" value="{{ $paymentMethod }}">
    </form>
</div>
@endsection
