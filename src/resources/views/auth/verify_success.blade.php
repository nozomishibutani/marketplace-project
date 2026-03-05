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
<x-guest-layout>
    <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded shadow">
        <h1 class="text-xl font-bold mb-4">メール認証が完了しました！</h1>

        <p>これでプロフィール設定など、会員機能を使うことができます。</p>

        <div class="mt-6">
            <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                プロフィール設定へ進む
            </a>
        </div>
    </div>
</x-guest-layout>
    </div>


    </main>
</body>

</html>
