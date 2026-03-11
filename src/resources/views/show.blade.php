@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/show.css') }}">
@endsection

@section('title')
    <title>商品詳細</title>
@endsection

@section('content')
    <div class="item">
        <!-- 左側：商品画像 -->
        <div class="item__img">
            <img src="{{ asset('storage/' . $item->img) }}" alt="商品画像">
        </div>

        <!-- 右側：商品情報 -->
        <div class="item__content">
            <!-- メッセージ -->
            @if(session('alert'))
                <div class="alert {{ session('alert-type', 'alert-success') }}">
                    <p>{{ session('alert') }}</p>
                </div>
            @endif
            <!-- 商品名 -->
            <div class="item__header">
                @if($itemStatuses['suspended'] == true)
                        <span class="msg msg--suspended">現在、この商品は出品を停止しています</span>
                @endif
                <h1 class="item__ttl">{{ $item->name }}
                    @if($itemStatuses['sold'] == true)
                        <span class="item__sold">Sold</span>
                    @endif
                </h1>
                <!-- ブランド名 -->
                <p class="item__brand-name">{{ $item->brand_name }}</p>
            </div>

                <!-- 価格 -->
                <p class="item__price">
                    <span class="item__price-symbol">¥</span>
                    {{ $item->price }}
                    <span class="item__price-tax">(税込)</span>
                </p>

                <!-- いいね・コメントアイコン -->
                <div class="item__actions-container">
                    <div class="item__actions-group">
                        @if($isFavorite)
                            <!-- いいね解除 -->
                            <form action="{{ route('items.unfavorite', $item->id) }}" method="post">
                            @csrf
                            @method('DELETE')
                                <button class="item__actions-btn" type="submit">
                                    <img class="item__actions-img" src="{{ asset('images/heart_pink.png') }}" alt="いいね済アイコン">
                                </button>
                            </form>
                        @else
                            <!-- いいねする -->
                            <form action="{{ route('items.favorite', $item->id) }}" method="post">
                            @csrf
                                <button class="item__actions-btn" type="submit">
                                    <img class="item__actions-img" src="{{ asset('images/heart_default.png') }}" alt="いいね未アイコン">
                                </button>
                            </form>
                        @endif
                        <span class="item__actions-count">{{ $favoritesCount }}</span>
                    </div>

                    <!-- コメントアイコン -->
                    <div class="item__actions-group">
                        <img class="item__actions-img item__actions-img--comment" src="{{ asset('images/speech_bubble.png') }}" alt="コメントアイコン">
                        <span class="item__actions-count">{{ $commentsCount }}</span>
                    </div>
                </div>

                <!-- 購入ボタン-->
                <div class="link-box item__link-box">
                    @if($itemStatuses['sold'] == false && $itemStatuses['suspended'] == false)
                        <a class="link item__link" href="{{ route('purchase.confirm', $item->id) }}">購入手続きへ</a>
                    @else
                        <a class="link item__link link--disabled" href="#">購入手続きへ</a>
                    @endif
                </div>

                <!-- 商品説明 -->
                <section class="description">
                    <h2 class="description__ttl">商品説明</h2>
                    <p class="description__content">
                        {{ $item->description }}
                    </p>
                </section>

                <!-- 商品情報 -->
                <section class="information">
                    <h2 class="information__ttl">商品の情報</h2>
                    <div class="information__row">
                        <h3 class="information__label">カテゴリー</h3>
                        <div class="information__tag">
                            @foreach($item->categories as $category)
                                <span class="information__tag-name information__tag-name--category">{{ $category->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="information__row">
                        <h3 class="information__label">商品の状態</h3>
                        <div class="information__tag">
                            <span class="information__tag-name">
                                {{ \App\Models\Item::CONDITIONS[$item->status] ?? '' }}
                            </span>
                        </div>
                    </div>
                </section>

                <!-- コメント -->
                <section class="comment">
                    <h2 class="comment__ttl">コメント<span>({{ $commentsCount }})</span></h2>
                    @if($commentsCount > 0)
                        <div class="comment__list">
                            @foreach ($item->comments as $comment)
                            <div class="comment__item">
                                <div class="comment__user">
                                    <img class="comment__avatar" src="{{ asset('storage/' . $avatar[$comment->id]) }}" alt="プロフィール画像">
                                    <span class="comment__username">
                                        {{ $comment->user->username }}
                                    </span>
                                </div>
                                <p class="comment__content">
                                    {!! nl2br(e(trim($comment->content))) !!}
                                </p>
                            </div>
                            @endforeach
                        </div>
                    @endif

                    <h3 class="comment__form-title">商品へのコメント</h3>
                        <form class="comment__form" action="{{ route('items.comment', $item->id) }}" method="post">
                        @csrf
                        <textarea class="comment__textarea" name="content" id=""></textarea>
                        @error('content')
                            <div class="msg">
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="btn-box">
                            <button class="btn comment__btn">コメントを送信する</button>
                        </div>
                    </form>
                </section>
        <div><!-- item_content -->
    </div><!-- item -->

@endsection