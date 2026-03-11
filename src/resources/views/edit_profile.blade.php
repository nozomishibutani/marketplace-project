@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/edit-profile.css') }}">
@endsection

@section('title')
    <title>プロフィール</title>
@endsection

@section('content')
    @if(session('alert'))
        <div class="alert">
            <p>{{ session('alert') }}</p>
        </div>
    @endif

    <div class="profile">
        <h1 class="profile__ttl">プロフィール設定</h1>
        <form action="{{ route('profile.store') }}" method="post" enctype="multipart/form-data">
        @csrf
            @if($profile)
                @method('PATCH')
            @endif

            <div class="profile__img-box">
                <img id="avatarPreview"
                    src="{{ asset('storage/' . ($profile->avatar ?? 'profiles/icon_dummy.png')) }}"
                    alt="プロフィール画像">

                <label class="profile__img-btn btn--outline">
                    画像を選択する
                    <input
                        id="avatarInput"
                        class="profile__img-input"
                        type="file"
                        name="avatar"
                        accept="image/*">
                </label>
            </div>
            @error('avatar')
                <div class="msg profile__img-msg">
                    {{ $message }}
                </div>
            @enderror

            <ul class="profile__list">
                <li class="profile__item">
                    <label class="profile__label" for="username">ユーザー名</label>
                    <input class="profile__form-input" type="text" name="username" id="username" value="{{ old('username', $username) }}" >
                    @error('username')
                        <div class="msg">
                            {{ $message }}
                        </div>
                    @enderror
                </li>

                <li class="profile__item">
                    <label class="profile__label" for="postcode">郵便番号</label>
                    <input class="profile__form-input" type="text" name="postcode" id="postcode" value="{{ old('postcode' , $profile->postcode ?? null) }}">
                    @error('postcode')
                        <div class="msg">
                            {{ $message }}
                        </div>
                    @enderror
                </li>

                <li class="profile__item">
                    <label class="profile__label" for="address">住所</label>
                    <input class="profile__form-input" type="text" name="address" id="address" value="{{ old('address' , $profile->address ?? null) }}">
                    @error('address')
                        <div class="msg">
                            {{ $message }}
                        </div>
                    @enderror
                </li>

                <li class="profile__item">
                    <label class="profile__label" for="building">建物名</label>
                    <input class="profile__form-input" type="text" name="building" id="building" value="{{ old('building' , $profile->building ?? null) }}">
                    @error('building')
                        <div class="msg">
                            {{ $message }}
                        </div>
                    @enderror
                </li>
            </ul>
            <div class="btn-box profile__btn-box">
                <button class="btn">更新する</button>
            </div>
        </div><!-- profile-->
    </form>
@endsection

@section('js')
    <!-- プレビュー用JS -->
    <script>
    $(function() {
        $('#avatarInput').on('change', function(e){
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(event) {
                $('#avatarPreview').attr('src', event.target.result);
            };
            reader.readAsDataURL(file);
        });
    });
    </script>
@endsection