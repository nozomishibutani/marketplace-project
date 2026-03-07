@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('title')
    <title>商品一覧</title>
@endsection

@section('menu')
    <nav class="menu__nav">
        <div class="menu__container">
            <ul class="menu__list">
                <li class="menu__item">
                    <a class="menu__link {{ $tab ? '' : 'menu__link--active' }}" href="{{ route('items.index') }}">おすすめ</a>
                </li>
                <li class="menu__item">
                    <a class="menu__link {{ $tab ? 'menu__link--active' : '' }}"
                        href="{{ route('items.index',array_merge(['tab' => \App\Common\Common::TAB_MYLIST],request()->only('keyword'))) }}">マイリスト</a>
                </li>
            </ul>
        </div>
    </nav>
@endsection

@section('content')
    @if(session('alert'))
            <div class="alert">
                <p>{{ session('alert') }}</p>
            </div>
    @endif

    <div class="items">
        @foreach($items as $item)
            <div class="item">
                <a href="{{ route('items.show', $item->id) }}" class="item__link">
                    <img src="{{ asset('storage/' . $item->img) }}" alt="{{ $item->name }}" class="item__img">
                    <h2 class="item__ttl">{{ $item->name }}</h2>

                    @if($item->status == \App\Models\Item::STATUS_SOLD)
                        <span class="item__sold">Sold</span>
                    @endif
                </a>
            </div>
        @endforeach
    </div>
@endsection