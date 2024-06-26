@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/success.css')}}">
@endsection

@section('content')
<div class="user-content">
    <div class="page-title">
        <h2 class="page-title__text">メールアドレスの認証が完了しました</h2>
    </div>
    <div>
        <p>以下のリンクからログインしてください。</p>
        <a href="/login" class="input-box__item-submit" >ログイン</a>
    </div>
</div>
@endsection('content')