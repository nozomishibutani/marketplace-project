<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>

<main>
    <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded shadow">
        <h1 class="text-xl font-bold mb-4">メール認証が必要です</h1>

        @if(session('alert'))
            <div class="alert">
                <p>{{ session('alert') }}</p>
            </div>
        @else
            <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
            <p>メール認証を完了してください。</p>
        @endif

        <div class="mt-6">
            <a href="{{ route('verification.confirm')}}">
                メール認証はこちら
            </a>
        </div>

        <div class="mt-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="underline text-blue-500">
                    認証メールを再送信する
                </button>
            </form>
        </div>
    </div>
    </main>
    @if(session('message'))
        <script>
            alert("{{ session('message') }}");
        </script>
    @endif
</body>

</html>
