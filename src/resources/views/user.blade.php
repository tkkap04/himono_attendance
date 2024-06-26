@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user.css')}}">
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
    <h2 class="page-title__text">
        ID : {{ $user->id }} / 名前 : {{ $user->name }}
    </h2>
    <div class="page-title__month-navigation">
        <a href="{{ $previousUrl }}" class="page-title__month-button">前月</a>
        <span class="current-month">{{ $date->format('Y年m月') }}</span>
        <a href="{{ $nextUrl }}" class="page-title__month-button">翌月</a>
    </div>
</div>

<table class="atte__table">
    <tr class="atte__row">
        <th class="atte__label">勤務日</th>
        <th class="atte__label">出勤時間</th>
        <th class="atte__label">退勤時間</th>
        <th class="atte__label">休憩時間</th>
        <th class="atte__label">勤務時間</th>
    </tr>
    @foreach ($paginatedData as $work)
    <tr class="atte__row">
        <td class="atte__data">{{ \Carbon\Carbon::parse($work->startTime)->format('Y-m-d') }}</td>
        <td class="atte__data">{{ \Carbon\Carbon::parse($work->startTime)->format('H:i:s') }}</td>
        <td class="atte__data">{{ $work->workOverDay ? '23:59:59' : \Carbon\Carbon::parse($work->endTime)->format('H:i:s') }}</td>
        <td class="atte__data">{{ $work->totalRestHours }}</td>
        <td class="atte__data">{{ $work->totalWorkHours }}</td>
    </tr>
    @endforeach
</table>

{{ $paginatedData->links('vendor.pagination.custom') }}

@endsection
