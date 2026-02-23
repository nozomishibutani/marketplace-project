@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('title')
    <title>プロフィール</title>
@endsection

@section('profile')
    <div>
        <div>
            <img src="{{ $avatar }}" alt="">
            <p>{{ $username }}</p>
        </div>
        <a href="{{ route('profile.edit') }}">プロフィールを編集</a>
    </div>

@endsection

@section('menu')
    <nav class="menu">
        <ul class="menu__nav">
            <a href="{{ route( 'profile.index', ['page' => \App\Common\Common::PAGE_SELL]) }}">
                <li>出品した商品</li>
            </a>
            <a href="{{ route( 'profile.index', ['page' => \App\Common\Common::PAGE_BUY]) }}">
                <li>購入した商品</li>
            </a>
        </ul>
    </nav>
@endsection

@section('content')
    @foreach ($items as $item)
        <a href="{{ route('items.show', $item->id) }}" class="item" >
            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}">
            <p>{{ $item->name }}</p>
            @if($item->status == \App\Models\Item::STATUS_SOLD)
                <p>Sold</p>
            @endif
        </a>
    @endforeach
@endsection