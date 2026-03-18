@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/sell.css') }}">
@endsection

@section('title')
    <title>商品の出品</title>
@endsection

@section('content')
    <div class="item">
        <h1 class="item__ttl">商品の出品</h1>
        <form method="post" action="{{ route('items.store') }}" enctype="multipart/form-data">
        @csrf
        <!-- 商品画像 -->
        <h3 class="item__img-label">商品画像</h3>
        <div class="item__img-upload">
            <div class="item__img-box">
                <img id="imgPreview" class="item__img-preview"
                src=""
                alt="商品画像">

                <label id="imgLabel" class="item__img-btn btn--outline">
                    画像を選択する
                    <input
                        id="imgInput"
                        class="item__img-input"
                        type="file"
                        name="img"
                        accept="image/*">
                </label>
            </div>
            @error('img')
                <div class="msg">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- 商品の詳細 -->
        <section class="detail">
            <h2 class="detail__ttl">商品の詳細</h2>
            <h3 class="detail__label">カテゴリー</h3>
            <div class="detail__tag">
                @foreach($categories as $id => $name)
                    <input class="detail__checkbox" type="checkbox" id="cat-{{ $id }}" name="categories[]" value="{{ $id }}"
                        {{ in_array($id, old('categories', [])) ? 'checked' : '' }}>
                    <label class="detail__tag-name detail__btn--outline" for="cat-{{ $id }}">{{ $name }}</label>
                @endforeach
            </div>
            @error('categories')
                <div class="msg">
                    {{ $message }}
                </div>
            @enderror
            @error('categories.*')
                <div class="msg">
                    {{ $message }}
                </div>
            @enderror

            <h3 class="detail__label--status">商品の状態</h3>
            <select class="detail__select-box" name="condition" >
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
                <div class="msg ">
                    {{ $message }}
                </div>
            @enderror
        </section>

        <!-- 商品名と説明 -->
        <section class="description">
            <h2 class="description__ttl">商品名と説明</h2>
            <ul class="description__list">
                <li class="description__item">
                    <label class="description__label" for="name">商品名</label>
                    <input type="text" class="description__form-input" name="name" id="name" value="{{ old('name') }}">
                    @error('name')
                        <div class="msg">
                            {{ $message }}
                        </div>
                    @enderror
                </li>

                <li class="description__item">
                    <label class="description__label" for="brand_name">ブランド名</label>
                    <input type="text" class="description__form-input" name="brand_name" id="brand_name" value="{{ old('brand_name') }}">
                    @error('brand_name')
                        <div class="msg">
                            {{ $message }}
                        </div>
                    @enderror
                </li>

                <li class="description__item">
                    <label class="description__label" for="description">商品の説明</label>
                    <textarea class="description__form-textarea" name="description" id="description">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="msg">
                            {{ $message }}
                        </div>
                    @enderror
                </li>

                <li class="description__item">
                    <label class="description__label" for="price">販売価格</label>
                    <div class="description__price-box">
                        <span class="description__price-symbol">¥</span>
                        <input class="description__form-input description__form-input--price" type="text" name="price" id="price" value="{{ old('price') }}">
                    </div>
                    @error('price')
                        <div class="msg">
                            {{ $message }}
                        </div>
                    @enderror
                </li>
            </ul>
        </section>
        <div class="btn-box">
            <button class="btn">出品する</button>
        </div>
        </form>
    </div><!-- item -->
@endsection

@section('js')
    <!-- jQuery 読み込み -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- プレビュー用JS -->
    <script>
        $(function() {
            $('#imgInput').on('change', function(e){
                const file = e.target.files[0];

                if (!file) {
                    $('#imgPreview').attr('src', '');
                    $('.item__img-box').removeClass('has-image');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(event) {
                    $('#imgPreview').attr('src', event.target.result);
                    $('.item__img-box').addClass('has-image');
                };
                reader.readAsDataURL(file);
            });
        });
</script>
@endsection