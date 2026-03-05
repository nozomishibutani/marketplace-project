@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/show.css') }}">
@endsection

@section('title')
    <title>商品詳細</title>
@endsection

@section('content')
    <div class="item-detail">
        @if(session('alert'))
            <div class="alert">
                <p>{{ session('alert') }}</p>
            </div>
        @endif
        <div class="item-image">
            <img src="{{ asset('storage/' . $item->img) }}" alt="商品画像" width="200">
        </div>

        <div class="item-info">
            <h1>{{ $item->name }}</h1>
            @if($itemStatuses['sold'] == true)
                <p>Sold</p>
            @endif
            @if($itemStatuses['suspended'] == true)
                <p>現在、この商品は出品を停止しています</p>
            @endif
            <p>{{ $item->brand_name }}</p>
            <p>¥{{ $item->price }}(税込)</p>
                <div class="button">
                    @if($isFavorite)
                        {{-- いいね解除 --}}
                        <form action="{{ route('items.unfavorite', $item->id) }}" method="post">
                        @csrf
                        @method('DELETE')
                            <button type="submit" style="border:none; background:none; padding:0;">
                                <img src="{{ asset('images/heart_pink.png') }}" alt="いいねマーク">
                            </button>
                        </form>
                    @else
                        {{-- いいねする --}}
                        <form action="{{ route('items.favorite', $item->id) }}" method="post">
                        @csrf
                            <button type="submit" style="border:none; background:none; padding:0;">
                                <img src="{{ asset('images/heart_default.png') }}" alt="いいねマーク">
                            </button>
                        </form>
                    @endif
                    <span>{{ $favoriteCount }}</span>
                    {{-- コメント --}}
                    <img src="{{ asset('images/speech_bubble.png') }}" alt="コメントマーク">
                    <span>{{ $content['count'] }}</span>
                </div>
                @if($itemStatuses['sold'] == false && $itemStatuses['suspended'] == false)
                    <a href="{{ route('purchase.confirm', $item->id) }}" class="btn">購入手続きへ</a>
                @endif

            <h2>商品説明</h2>
            <p>{{ $item->description }}</p>

            <h2>商品の情報</h2>
            <div class="category">
                <span>
                    <label for="">カテゴリー</label>
                </span>
                <span>
                    <label for="">
                        @foreach($item->categories as $category)
                            {{ $category->name }}
                        @endforeach
                    </label>
                </span>
            </div>
            <div class="status">
                <span>
                    <label for="">商品の状態</label>
                </span>
                <span>
                    <label for="">{{ \App\Models\Item::CONDITIONS[$item->status] ?? '' }}</label>
                </span>
            </div>
            <P class="">コメント{{-- コメントの数表示 --}}</P>
            <div class="profile">
                <img src="{{ asset('storage/' . $item->image) }}" alt="商品画像">
                <span>
                    <label for="">{{ $item->user_name ?? '' }}</label>
                </span>
            </div>
            @foreach($content['content'] as $value)
                <p>{{ $value['content'] }}</p>
            @endforeach
            <h3>商品へのコメント</h3>
                <form action="{{ route('items.comment', $item->id) }}" method="post">
                @csrf
                <div class="msg">
                    @error('content')
                        {{ $message }}
                    @enderror
                </div>
                <textarea name="content" id=""></textarea>
                <button>コメントを送信する</button>
            </form>
        </div>
    </div>
@endsection