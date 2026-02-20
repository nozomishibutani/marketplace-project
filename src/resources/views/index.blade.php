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
        <a href="{{ route('items.show', $item->id) }}" class="item" data-item-id="{{ $item->id }}" >
            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}">
            <p>{{ $item->name }}</p>
            @if($item->status == \App\Models\Item::STATUS_SOLD)
                <p>Sold</p>
            @endif
        </a>
    @endforeach
@endsection