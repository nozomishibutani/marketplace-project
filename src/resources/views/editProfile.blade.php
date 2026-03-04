@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/editProfile.css') }}">
@endsection

@section('title')
    <title>プロフィール</title>
@endsection

@section('content')
<div class="profile-form">
    <h1 class="profile-form__heading">プロフィール設定</h1>
    <form action="{{ route('profile.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    @if ($profile)
        @method('PATCH')
    @endif
        <div class="profile-form__inner">
                <img src="{{ asset('storage/' . ($profile->avatar ?? 'profiles/icon_dummy.png')) }}" alt="プロフィール画像" width="200">
                <input type="file" name="avatar" accept="image/*" value="画像を選択する">
        </div>

        <div class="profile-form__inner">
            <div class="profile-form__group">
                <label class="profile-form__label" for="username">ユーザー名</label>
                <input class="profile-form__input" type="text" name="username" id="username" value="{{ old('username', $username) }}" >
                <p class="profile-form__error-message">
                    @error('username')
                        {{ $message }}
                    @enderror
                </p>
            </div>

            <div class="profile-form__group">
                <label class="profile-form__label" for="postcode">郵便番号</label>
                <input class="profile-form__input" type="text" name="postcode" id="postcode" value="{{ old('postcode' , $profile->postcode ?? null) }}">
                <p class="profile-form__error-message">
                    @error('postcode')
                        {{ $message }}
                    @enderror
                </p>
            </div>

            <div class="profile-form__group">
                <label class="profile-form__label" for="address">住所</label>
                <input class="profile-form__input" type="text" name="address" id="address" value="{{ old('address' , $profile->address ?? null) }}">
                <p class="profile-form__error-message">
                    @error('address')
                        {{ $message }}
                    @enderror
                </p>
            </div>

            <div class="profile-form__group">
                <label class="profile-form__label" for="building">建物名</label>
                <input class="profile-form__input" type="text" name="building" id="building" value="{{ old('building' , $profile->building ?? null) }}">
                <p class="profile-form__error-message">
                    @error('building')
                        {{ $message }}
                    @enderror
                </p>
            </div>

            <input class="profile-form__btn btn" type="submit" value="更新する">
        </form>
    </div>
</div>
@endsection