<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/base.css') }}">
    <title>メール認証</title>
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
                <div class="auth__text-box">
                    <p class="auth__text">登録していただいたメールアドレスに認証メールを送付しました。<br>メール認証を完了してください。</p>
                    <div class="auth__link-box">
                        <a class="auth__link auth__link--btn" href="{{ route('verification.confirm')}}">
                            認証はこちらから
                        </a>
                    </div>
                </div>

                <div class="auth__btn-box">
                    <form method="post" action="{{ route('verification.send') }}">
                        @csrf
                        <button class="auth__btn">
                            認証メールを再送する
                        </button>
                    </form>
                </div>

            </div><!-- main__container -->
        </div><!-- auth -->
    </main>

    <!-- メール再送信完了したメッセージ-->
    @if(session('message'))
        <script>
            alert("{{ session('message') }}");
        </script>
    @endif

</body>
</html>
