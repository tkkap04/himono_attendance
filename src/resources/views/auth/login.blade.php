@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css')}}">
@endsection

@section('content')
<div class="page-title">
    <h2 class="page-title__text">ログイン</h2>
</div>

<form class="input-box" action="/login" method="post">
    @csrf
    <div class="input-box">
        <div class="input-box__item">
            <input type="email" name="email" placeholder="メールアドレス" class="input-box__item-input" value="{{ old('email') }}" >
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
            <input type="submit" class="input-box__item-submit"  value="ログイン" >
        </div>
        <div class="input-box__other">
            <p>アカウントをお持ちでない方はこちらから</p>
            <a href="/register">会員登録</a>
        </div>
    </div>
</form>

@endsection('content')