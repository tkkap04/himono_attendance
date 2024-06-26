@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css')}}">
@endsection

@section('content')
<div class="page-title">
    <h2 class="page-title__text">会員登録</h2>
</div>

<form class="input-box" action="/register" method="post">
    @csrf
    <div class="input-box__item">
        <input type="text" name="name" class="input-box__item-input" placeholder="名前" value="{{ old('name') }}" >
        <p class="input-box__error-message">　
            @error('name')
            {{ $message }}
            @enderror
        </p>
    </div>
    
    <div class="input-box__item">
        <input type="email" name="email" class="input-box__item-input" placeholder="メールアドレス" value="{{ old('email') }}" >
        <p class="input-box__error-message">　
            @error('email')
            {{ $message }}
            @enderror
        </p>
    </div>
    <div class="input-box__item">
        <input type="password" name="password" class="input-box__item-input" placeholder="パスワード" >
        <p class="input-box__error-message">　
            @error('password')
            {{ $message }}
            @enderror
        </p>
    </div>
    <div class="input-box__item">
        <input type="password" name="password_confirmation" class="input-box__item-input" placeholder="確認用パスワード" >
        <p class="input-box__error-message">　
            @error('password')
            {{ $message }}
            @enderror
        </p>
    </div>
    <div class="input-box__item">
        <input type="submit" class="input-box__item-submit" value="会員登録" >
    </div>
    <div class="input-box__other">
        <p>アカウントをお持ちの方はこちらから</p>
        <a href="/login">ログイン</a>
    </div>
</form>

@endsection('content')