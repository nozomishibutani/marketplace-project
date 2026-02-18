
@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/editAddress.css') }}">
@endsection

@section('jquery')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
@endsection

@section('title')
    <title>住所変更</title>
@endsection

@section('content')
    <h1>住所の変更</h1>
    <form action="{{ route('purchase.update') }}" method="post">
        <input type="hidden" name="item_id" value="{{ $item_id }}">
        @csrf
        <div>
            <label for="postcode">郵便番号</label>
            <input type="text" name="postcode" id="postcode" value="{{ old('postcode') }}">
            @error('postcode')
                <div>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div>
            <label for="address">住所</label>
            <input type="text" name="address" id="address" value="{{ old('address') }}">
            @error('address')
                <div>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div>
            <label for="building">建物名</label>
            <input type="text" name="building" id="building" value="{{ old('building') }}">
            @error('building')
                <div>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <button type="submit">更新する</button>
    </form>
@endsection

@section('js')
    <script src="{{ asset('js/postcode.js') }}"></script>
@endsection

</body>

</html>