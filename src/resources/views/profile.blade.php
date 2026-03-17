@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('title')
    <title>プロフィール</title>
@endsection

@section('profile')
    <div class="profile__container">
        <div class="profile__user">
            @if($avatar)
                <img class="profile__img" src="{{ asset('storage/' . $avatar) }}" alt="プロフィール画像">
            @else
                <div class="profile__img profile__img--placeholder"></div>
            @endif
            <span class="profile__label">{{ $username }}</span>
        </div>
        <div class="link-box profile__link-box">
            <a class="link profile__link link--outline" href="{{ route('profile.edit') }}">プロフィールを編集</a>
        </div>
    </div>

@endsection

@section('menu')
    <nav class="menu__nav">
        <div class="menu__container">
            <ul class="menu__list">
                <li class="menu__item">
                    <a @class([
                        'menu__link',
                        'menu__link--active' => request('page') === \App\Common\Common::PAGE_SELL
                    ])
                    href="{{ route('profile.index', ['page' => \App\Common\Common::PAGE_SELL]) }}">
                        出品した商品
                    </a>
                </li>
                <li class="menu__item">
                    <a @class([
                        'menu__link',
                        'menu__link--active' => request('page') === \App\Common\Common::PAGE_BUY
                    ])
                    href="{{ route('profile.index', ['page' => \App\Common\Common::PAGE_BUY]) }}">
                        購入した商品
                    </a>
                </li>
            </ul>
        </div>
    </nav>
@endsection

@section('content')
<div class="item">
    @foreach ($items as $item)
        <a class="item__link" href="{{ route('items.show', $item->id) }}">
            <div class="item__img">
                <img src="{{ asset('storage/' . $item->img) }}" alt="{{ $item->name }}">
            </div>
            <h2 class="item__ttl">{{ $item->name }}</h2>
                @if($item->isSold())
                    <span class="item__sold">Sold</span>
                @endif
        </a>
    @endforeach
</div>
@endsection
