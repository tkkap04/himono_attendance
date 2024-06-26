@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css')}}">
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
<div class="user-content">
    <div class="page-title">
        <h2 class="page-title__text">ユーザー一覧</h2>
    </div>
    <table class="atte__table">
        <tr  class="atte__row">
            <th class="atte__label">ID</th>
            <th class="atte__label">名前</th>
            <th class="atte__label">勤怠表</th>
        </tr>
        @foreach($users as $user)
        <tr  class="atte__row">
            <td class="atte__data">{{ $user->id }}</td>
            <td class="atte__data">{{ $user->name }}</td>
            <td class="atte__data"><a href="{{ route('list.attendance', ['id' => $user->id]) }}">詳細</a></td>
        </tr>
        @endforeach
    </table>
</div>

<div class="pagination">
    {{ $users->links('vendor.pagination.custom') }}
</div>

@endsection('content')