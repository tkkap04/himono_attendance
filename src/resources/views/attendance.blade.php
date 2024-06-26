@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css')}}">
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
            <a href="{{ $previousDateUrl }}"><p class="page-title__arrow">←</p></a>
            <p class="page-title__date">{{ $currentDate->format('Y-m-d') }}</p>
            <a href="{{ $nextDateUrl }}"><p class="page-title__arrow">→</p></a>
        </h2>
    </div>

    <table class="atte__table">
        <tr class="atte__row">
            <th class="atte__label">名前</th>
            <th class="atte__label">勤務開始</th>
            <th class="atte__label">勤務終了</th>
            <th class="atte__label">休憩時間</th>
            <th class="atte__label">勤務時間</th>
        </tr>
        @foreach ($paginatedData as $work)
        <tr class="atte__row">
            <td class="atte__data">{{ $work->user }}</td>
            <td class="atte__data">{{ \Carbon\Carbon::parse($work->startTime)->format('H:i:s') }}</td>
            <td class="atte__data">{{ $work->workOverDay ? '23:59:59' : \Carbon\Carbon::parse($work->endTime)->format('H:i:s') }}</td>
            <td class="atte__data">{{ $work->totalRestHours }}</td>
            <td class="atte__data">{{ $work->totalWorkHours }}</td>
        </tr>
        @endforeach
        
    </table>

    {{ $paginatedData->links('vendor.pagination.custom') }}

@endsection