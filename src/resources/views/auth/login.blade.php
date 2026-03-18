<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/base.css') }}">
    <title>ログイン</title>
</head>

<body>
    <header class="header">
        <div class="header__container">
                <div class="header__logo">
                    <a href="{{ route('items.index') }}">
                        <img src="{{ asset('/images/header_logo.png') }}" alt="ヘッダーロゴ画像">
                    </a>
                </div>
            </div>
    </header>

    <main>
        <div class="main__container">
            <div class="auth">
                @if(session('alert'))
                <div class="alert {{ session('alert-type', 'alert-success') }}">
                    <p>{{ session('alert') }}</p>
                </div>
                @endif

                <h1 class="auth-ttl">ログイン</h1>
                <form class="form" action="/login" method="post" novalidate>
                @csrf
                <ul class="auth__list">
                    <li class="auth__item">
                        <label class="auth__label" for="email">メールアドレス</label>
                        <input class="auth__form-input" type="email" name="email" id="email" value="{{ old('email') }}" />
                        @error('email')
                            <div class="msg auth__msg">
                                {{ $message }}
                            </div>
                        @enderror
                    </li>

                    <li class="auth__item">
                        <label class="auth__label" for="password">パスワード</label>
                        <input class="auth__form-input" type="password" name="password" name="password" id="password" />
                        @error('password')
                            <div class="msg auth__msg">
                                {{ $message }}
                            </div>
                        @enderror
                    </li>
                </ul>

                    <div class="btn-box">
                        <button class="btn">ログイン</button>
                    </div>
                </form>

            <div class="auth__link-box">
                <a class="auth__link" href="/register">会員登録の方はこちら</a>
            </div>

            </div><!-- auth -->
        </div><!-- main__container -->
    </main>
</body>
</html>
