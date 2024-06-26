@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp.css')}}">
@endsection

@section('link')
<nav class="header__nav">
    <ul class="header__nav-list">
        <li class="header__nav-list__item">
            <a class="header__nav-list__item-name" href="/">ホーム</a>
        </li>
        <li class="header__nav-list__item">
            <a class="header__nav-list__item-name" href="/attendance">日付一覧</a>
        </li>
        <li class="header__nav-list__item">
            <a class="header__nav-list__item-name" href="/list">ユーザー一覧</a>
        </li>
        <li class="header__nav-list__item">
            <form action="/logout" method="post">
                @csrf
                <button class="header__nav-list__item-submit">ログアウト</button>
            </form>
        </li>
    </ul>
</nav>
@endsection

@section('content')
<div class="page-title">
    <h2 class="page-title__text">{{ $user->name }}さんお疲れ様です！</h2>
</div>

<div class="stamp-content">
    <div class="stamp-box">
        <form class="stamp-box__form__start-work" action="/start-work" method="post">
            @csrf
            <input class="stamp-box__item-text" type="submit" value="勤務開始" 
            @if(isset($workStart) && $workStart) disabled @endif >
        </form>

        <form class="stamp-box__form__end-work" action="/end-work" method="post">
            @csrf
            <input class="stamp-box__item-text" type="submit" value="勤務終了"
            @if(isset($workEnd) && $workEnd) disabled @endif>
        </form>

        <form class="stamp-box__form__start-rest" action="/start-rest" method="post">
            @csrf
            <input class="stamp-box__item-text" type="submit" value="休憩開始"
            @if(isset($restStart) && $restStart) disabled @endif>
        </form>

        <form class="stamp-box__form__end-rest" action="/end-rest" method="post">
            @csrf
            <input class="stamp-box__item-text" type="submit" value="休憩終了"
            @if(isset($restEnd) && $restEnd) disabled @endif>
        </form>
    </div>
</div>

@endsection('content')