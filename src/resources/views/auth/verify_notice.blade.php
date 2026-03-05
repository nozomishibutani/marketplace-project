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

        <p>登録していただいたメールアドレスに認証メールを送付しました。メール内のリンクをクリックして認証してください。</p>

        <div class="mt-6">
            <a href="{{ route('verification.verify') }}" class="btn btn-primary">
                認証はこちらから
            </a>
        </div>

        <div class="mt-4">
            {{-- 認証メール再送 --}}
            <form method="POST" action="">
                @csrf
                <button type="submit" class="underline text-blue-500">
                    認証メールを再送信
                </button>
            </form>
        </div>
    </div>
    </main>
</body>

</html>
