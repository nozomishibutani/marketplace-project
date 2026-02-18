@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('title')
    <title>商品一覧</title>
@endsection

@section('menu')
    <nav class="menu">
        <ul class="menu__nav">
            <li>おすすめ</li>
            <a href="{{ route('items.index', ['tab' => \App\Common\Common::TAB_MYLIST]) }}">
                <li>マイリスト</li>
            </a>
        </ul>
    </nav>
@endsection

@section('content')
    @foreach ($items as $item)
        @if($item->status == \App\Models\Item::STATUS_SOLD)
            <p>sold</p>
        @endif
        <a href="{{ route('items.show', $item->id) }}" class="item">
            <img src="{{ asset('storage/' . $item->image) }}" alt="商品画像">
            <p>{{ $item->name }}</p>
        </a>
    @endforeach
@endsection