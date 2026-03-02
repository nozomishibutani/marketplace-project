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
            <a href="{{ route('items.index',
                array_merge(
                    ['tab' => \App\Common\Common::TAB_MYLIST],request()->only('keyword')
                )
            ) }}">
                <li>マイリスト</li>
            </a>
        </ul>
    </nav>
@endsection

@section('content')
    @foreach ($items as $item)
        <a href="{{ route('items.show', $item->id) }}" class="item" >
            <img src="{{ asset('storage/' . $item->img) }}" alt="{{ $item->name }}"  width="300" height="250">
            <p>{{ $item->name }}</p>
            @if($item->status == \App\Models\Item::STATUS_SOLD)
                <p>Sold</p>
            @endif
        </a>
    @endforeach
@endsection