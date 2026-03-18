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
        <div class="alert {{ session('alert-type', 'alert-success') }}">
            <p>{{ session('alert') }}</p>
        </div>
    @endif

    <div class="item">
        @foreach($items as $item)
        <a class="item__link" href="{{ route('items.show', $item->id) }}">
            <div class="item__img">
                <img src="{{ asset('storage/' . $item->img) }}" alt="{{ $item->name }}">
            </div>
            <h2 class="item__ttl">{{ $item->name }}</h2>
            @if($item->isSold())
                <span class="item-status--sold">Sold</span>
            @endif
        </a>
        @endforeach
    </div>
@endsection