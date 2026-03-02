@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/sell.css') }}">
@endsection

@section('title')
    <title>商品の出品</title>
@endsection

@section('content')
    <h1>商品の出品</h1>

    <form method="post" action="{{ route('items.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="">
        <h3>商品画像</h3>
        <input type="file" name="img" accept="image/*">
    </div>
    @error('img')
        <div>
            {{ $message }}
        </div>
    @enderror

    <div>
        <h2>商品の詳細</h2>
        <h3>カテゴリー</h3>
        <div>
            @foreach($categories as $id => $name)
                <input type="checkbox" id="{{ $id }}" name="categories[]" value="{{ $id }}"  {{ in_array($id, old('categories', [])) ? 'checked' : '' }}>
                <label for="{{ $id }}">{{ $name }}</label>
            @endforeach
        </div>
        @error('categories')
            <div>
                {{ $message }}
            </div>
        @enderror
        @error('categories.*')
            <div>
                {{ $message }}
            </div>
        @enderror
        <h3>商品の状態</h3>
        <select class="" name="condition" >
            <option hidden>
                {{ \App\Models\Item::CONDITION_HIDDEN }}
            </option>
            @foreach(\App\Models\Item::CONDITIONS as $key => $label)
                <option value="{{ $key }}" {{ old('condition')  == $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('condition')
            <div>
                {{ $message }}
            </div>
        @enderror
    </div>

    <div>
        <h2>商品名と説明</h2>
        <div>
            <h3>商品名</h3>
            <input type="text" name="name" id="" value="{{ old('name') }}">
        </div>
        @error('name')
            <div>
                {{ $message }}
            </div>
        @enderror
        <div>
            <h3>ブランド名</h3>
            <input type="text" name="brand_name" id="" value="{{ old('brand_name') }}">
        </div>
        @error('brand_name')
            <div>
                {{ $message }}
            </div>
        @enderror
        <div>
            <h3>商品の説明</h3>
            <textarea name="description" id="">{{ old('description') }}</textarea>
        </div>
        @error('description')
            <div>
                {{ $message }}
            </div>
        @enderror
        <div>
            <h3>販売価格</h3>
            <div class="price-wrapper">
                <span class="yen">¥</span>
                <input type="text" name="price" value="{{ old('price') }}">
            </div>
        </div>
        @error('price')
            <div>
                {{ $message }}
            </div>
        @enderror
    </div>
    <div>
        <button>出品する</button>
    </div>
    </form>
@endsection