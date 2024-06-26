@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify.css')}}">
@endsection

@section('content')
<div class="user-content">
    <div class="page-title">
        <h2 class="page-title__text">メールアドレスの確認</h2>
    </div>
    <div>
        <p>登録時に入力したメールアドレスに確認メールを送信しました。</p>
        <p>メール内のリンクをクリックして、メールアドレスを確認してください。</p>
    </div>
    <div>
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="input-box__item-submit" type="submit">確認メール再送信</button>
        </form>
    </div>
</div>
@endsection('content')