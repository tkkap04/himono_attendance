<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Work;
use App\Models\Rest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class WorkController extends Controller
{
// stamp画面の表示
    public function index(Request $request)
    {
        $user = Auth::user();
        // ユーザーIDを取得
        $userId = Auth::id();
        // 今日の日付を取得
        $date = Carbon::now()->toDateString();
        // 現在時刻を取得
        $currentTime = Carbon::now();

        $work = Work::where('user_id', Auth::id())->where('work_date', $date)->first();

        // ボタンの表示フラグを初期化
        $workNum = 0;
        $restNum = 0;

        $workStart = false;
        $workEnd = false;
        $restStart = false;
        $restEnd = false;

        // 勤務開始前であれば勤務開始ボタンを表示
        if ($workNum === 0) {
            $workEnd = true;
            $restStart = true;
            $restEnd = true;
        
        // 勤務中であれば勤務終了ボタンと休憩開始ボタンを表示
        }elseif ($workNum === 1) {
            $workEnd = true;
            $restStart = true;
        
        // 休憩中であれば休憩終了ボタンを表示
        }elseif ($restNum === 1) {
            $restEnd = true;
        }
        
        return view('stamp', compact('user', 'workStart', 'workEnd', 'restStart', 'restEnd'));
    }

// 勤務開始ボタン
    public function startWork(Request $request)
    {
        $user = Auth::user();
        // ユーザーIDを取得
        $userId = $user->id;
        // 現在時刻を取得
        $currentTime = now();

        // 今日の勤務を開始
        $work = Work::create([
            'user_id' => $userId,
            'work_date' => $currentTime->toDateString(),
            'start_time' => $currentTime,
        ]);

        $workId = $work->id;

        $workStart = true;
        $workEnd = false;
        $restStart = false;
        $restEnd = true;

        return view('stamp', compact('user', 'workId', 'workStart', 'workEnd', 'restStart', 'restEnd'));
    }

    // 勤務終了ボタン
    public function endWork(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;
        $currentTime = now();

        // 現在勤務中のレコードを取得
        $work = Work::where('user_id', $userId)
                    ->whereNull('end_time')
                    ->latest()
                    ->first();
        
        if (!$work) {
            return back()->with('error', '勤務開始が記録されていません。');
        }

        $work->update(['end_time' => $currentTime]);
        $workId = $work->id;

        $workStart = false;
        $workEnd = true;
        $restStart = true;
        $restEnd = true;

        return view('stamp', compact('user', 'workId', 'workStart', 'workEnd', 'restStart', 'restEnd'));
    }

    // 休憩開始ボタン
    public function startRest(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;
        $currentTime = now();

        // 既存のWorkレコードを取得
        $work = Work::where('user_id', $userId)
                    ->whereNull('end_time')
                    ->latest()
                    ->first();
        
        if (!$work) {
            return back()->with('error', '勤務開始が記録されていません。');
        }

        $rest = Rest::create([
            'work_id' => $work->id,
            'rest_start_time' => $currentTime,
        ]);

        $workId = $work->id;
        $restId = $rest->id;

        $workStart = true;
        $workEnd = true;
        $restStart = true;
        $restEnd = false;

        return view('stamp', compact('user', 'workId', 'restId', 'workStart', 'workEnd', 'restStart', 'restEnd'));
    }

    // 休憩終了ボタン
    public function endRest(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;
        $currentTime = now();

        // 既存のWorkレコードを取得
        $work = Work::where('user_id', $userId)
                    ->whereNull('end_time')
                    ->latest()
                    ->first();
        
        if (!$work) {
            return back()->with('error', '勤務開始が記録されていません。');
        }

        // 現在休憩中のレコードを取得
        $rest = $work->rests()->whereNull('rest_end_time')->latest()->first();

        if (!$rest) {
            return back()->with('error', '休憩開始が記録されていません。');
        }

        $rest->update(['rest_end_time' => $currentTime]);

        $restId = $rest->id;
        $workId = $work->id;

        $workStart = true;
        $workEnd = false;
        $restStart = false;
        $restEnd = true;

        return view('stamp', compact('user', 'workId', 'restId', 'workStart', 'workEnd', 'restStart', 'restEnd'));
    }

// 日付一覧画面表示
public function show(Request $request)
{
    $date = $request->input('date', Carbon::now()->format('Y-m-d'));        
    // 今日の日付を取得
    $currentDate = Carbon::parse($date);
    // 前日と翌日の日付を計算
    $previousDate = $currentDate->copy()->subDay();
    $nextDate = $currentDate->copy()->addDay();

    // 日付切り替えボタンのURLを生成
    $previousDateUrl = route('attendance.show', ['date' => $previousDate->format('Y-m-d')]);
    $nextDateUrl = route('attendance.show', ['date' => $nextDate->format('Y-m-d')]);

    $workRecords = Work::where('work_date', $currentDate->format('Y-m-d'))->get();

    $calculatedData = [];
    foreach ($workRecords as $work) {
        $startTime = Carbon::parse($work->start_time);
        $endTime = Carbon::parse($work->end_time);

        // 勤務が日付をまたぐかどうかのフラグ
        $workOverDay = $endTime->greaterThan($startTime->copy()->endOfDay());

        // 勤務時間の分割処理
        if ($workOverDay) {
            $endOfDay = $currentDate->copy()->endOfDay(); // 当日の23:59:59
            $startOfNextDay = $nextDate->copy()->startOfDay(); // 翌日の00:00:00

            $workEndTimeForToday = $endOfDay;
            $workStartTimeForNextDay = $startOfNextDay;
            $workEndTimeForNextDay = $endTime;

            // 当日の勤務時間
            $workSecondsForToday = $workEndTimeForToday->diffInSeconds($startTime);
            // 翌日の勤務時間
            $workSecondsForNextDay = $workEndTimeForNextDay->diffInSeconds($workStartTimeForNextDay);
        } else {
            $workSecondsForToday = $endTime->diffInSeconds($startTime);
            $workSecondsForNextDay = 0;
        }

        // 休憩時間の合計を計算する
        $rests = Rest::where('work_id', $work->id)->get();
        $totalRestSecondsForToday = 0;
        $totalRestSecondsForNextDay = 0;

        foreach ($rests as $rest) {
            $restStartTime = Carbon::parse($rest->rest_start_time);
            $restEndTime = Carbon::parse($rest->rest_end_time);

            // 休憩が日付をまたぐかどうかのフラグ
            $restOverDay = $restEndTime->greaterThan($restStartTime->copy()->endOfDay());


            if ($restOverDay) {
                // 当日の休憩時間
                if ($restStartTime->lessThanOrEqualTo($endOfDay)) {
                $restEndTimeForToday = $restEndTime->lessThan($endOfDay) ? $restEndTime : $endOfDay;
                $totalRestSecondsForToday += $restEndTimeForToday->diffInSeconds($restStartTime);
            }

                // 翌日の休憩時間
                if ($restEndTime->greaterThan($startOfNextDay)) {
                    $restStartTimeForNextDay = $startOfNextDay;
                    $totalRestSecondsForNextDay += $restEndTime->diffInSeconds($restStartTimeForNextDay);
                }
            } else {
                // 日付をまたがない場合の休憩時間
                $totalRestSecondsForToday += $restEndTime->diffInSeconds($restStartTime);
            }
        }

        // 日付をまたいだ場合の当日の勤務時間から休憩時間を差し引く
            if ($workOverDay) {
                $totalWorkSecondsForToday = $workSecondsForToday - $totalRestSecondsForToday;
                $totalWorkSecondsForNextDay = $workSecondsForNextDay - $totalRestSecondsForNextDay;
            } else {
                $totalWorkSecondsForToday = $workSecondsForToday - $totalRestSecondsForToday;
                $totalWorkSecondsForNextDay = 0;
            }

        // 分を時間に変換（当日分）
        $totalRestHoursForToday = floor($totalRestSecondsForToday / 3600);
        $totalRestMinutesForToday = floor(($totalRestSecondsForToday % 3600) / 60);
        $totalRestSecondsForToday = $totalRestSecondsForToday % 60;
        $totalWorkHoursForToday = floor($totalWorkSecondsForToday / 3600);
        $totalWorkMinutesForToday = floor(($totalWorkSecondsForToday % 3600) / 60);
        $totalWorkSecondsForToday = $totalWorkSecondsForToday % 60;

        // データをオブジェクトとして格納（当日分）
        $data = new \stdClass();
        $data->user = $work->user->name;
        $data->startTime = $work->start_time;
        $data->endTime = $workOverDay ? $workEndTimeForToday->toDateTimeString() : $work->end_time;
        $data->totalRestHours = sprintf('%02d:%02d:%02d', $totalRestHoursForToday, $totalRestMinutesForToday, $totalRestSecondsForToday);
        $data->totalWorkHours = sprintf('%02d:%02d:%02d', $totalWorkHoursForToday, $totalWorkMinutesForToday, $totalWorkSecondsForToday);
        $data->workOverDay = $workOverDay;

        $calculatedData[] = $data;

        // 日付をまたいでいる場合の翌日分データ
        if ($workOverDay) {
            $totalRestHoursForNextDay = floor($totalRestSecondsForNextDay / 3600);
            $totalRestMinutesForNextDay = floor(($totalRestSecondsForNextDay % 3600) / 60);
            $totalRestSecondsForNextDay = $totalRestSecondsForNextDay % 60;
            $totalWorkHoursForNextDay = floor($totalWorkSecondsForNextDay / 3600);
            $totalWorkMinutesForNextDay = floor(($totalWorkSecondsForNextDay % 3600) / 60);
            $totalWorkSecondsForNextDay = $totalWorkSecondsForNextDay % 60;

            $nextDayData = new \stdClass();
            $nextDayData->user = $work->user->name;
            $nextDayData->startTime = $workStartTimeForNextDay->toDateTimeString();
            $nextDayData->endTime = $work->end_time;
            $nextDayData->totalRestHours = sprintf('%02d:%02d:%02d', $totalRestHoursForNextDay, $totalRestMinutesForNextDay, $totalRestSecondsForNextDay);
            $nextDayData->totalWorkHours = sprintf('%02d:%02d:%02d', $totalWorkHoursForNextDay, $totalWorkMinutesForNextDay, $totalWorkSecondsForNextDay);
            $nextDayData->workOverDay = false; // 翌日のデータは日付をまたいでいないのでフラグはfalse

            $calculatedData[] = $nextDayData;
        }
    }

    // ページネーションの設定
    $perPage = 5;
    $page = $request->input('page', 1);
    $offset = ($page - 1) * $perPage;

    // データをコレクションに変換してページネーション可能にする
    $paginatedData = new LengthAwarePaginator(
        array_slice($calculatedData, $offset, $perPage, true),
        count($calculatedData),
        $perPage,
        $page,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    return view('attendance', compact('previousDateUrl', 'nextDateUrl', 'currentDate', 'paginatedData' ));
}


    // ユーザー別勤怠表
    public function showUserAttendance(Request $request, $id)
    {
        // リクエストから年月を取得（なければ現在の年月）
        $yearMonth = $request->input('date', Carbon::now()->format('Y-m'));
        $date = Carbon::createFromFormat('Y-m', $yearMonth);

        $user = User::findOrFail($id);
        $previousDate = $date->copy()->subMonth();
        $nextDate = $date->copy()->addMonth();

        // 前月と翌月のURLを生成
        $previousUrl = route('list.attendance', ['id' => $id, 'date' => $previousDate->format('Y-m')]);
        $nextUrl = route('list.attendance', ['id' => $id, 'date' => $nextDate->format('Y-m')]);

        // 指定されたユーザーの現在の年月の勤務記録を取得
        $workRecords = Work::where('user_id', $id)
                            ->whereYear('work_date', $date->year)
                            ->whereMonth('work_date', $date->month)
                            ->get();

        $calculatedData = [];
        foreach ($workRecords as $work) {
            $startTime = Carbon::parse($work->start_time);
            $endTime = Carbon::parse($work->end_time);

            // 日付をまたぐかどうかのフラグ
            $workOverDay = $endTime->greaterThan($startTime->copy()->endOfDay());

            // 勤務時間の分割処理
            if ($workOverDay) {
                $endOfDay = $startTime->copy()->endOfDay(); // 当日の23:59:59
                $startOfNextDay = $endOfDay->copy()->addSecond(); // 翌日の00:00:00

                $workEndTimeForToday = $endOfDay;
                $workStartTimeForNextDay = $startOfNextDay;
                $workEndTimeForNextDay = $endTime;

                // 当日の勤務時間
                $workSecondsForToday = $workEndTimeForToday->diffInSeconds($startTime);
                // 翌日の勤務時間
                $workSecondsForNextDay = $workEndTimeForNextDay->diffInSeconds($workStartTimeForNextDay);
            } else {
                $workSecondsForToday = $endTime->diffInSeconds($startTime);
                $workSecondsForNextDay = 0;
            }

            // 休憩時間の合計を計算する
            $rests = Rest::where('work_id', $work->id)->get();
            $totalRestSecondsForToday = 0;
            $totalRestSecondsForNextDay = 0;

            foreach ($rests as $rest) {
                $restStartTime = Carbon::parse($rest->rest_start_time);
                $restEndTime = Carbon::parse($rest->rest_end_time);

                // 休憩が日付をまたぐかどうかのフラグ
                $restOverDay = $restEndTime->greaterThan($restStartTime->copy()->endOfDay());

                if ($restOverDay) {
                    // 当日の休憩時間
                    if ($restStartTime->lessThanOrEqualTo($endOfDay)) {
                        $restEndTimeForToday = $restEndTime->lessThan($endOfDay) ? $restEndTime : $endOfDay;
                        $totalRestSecondsForToday += $restEndTimeForToday->diffInSeconds($restStartTime);
                    }

                    // 翌日の休憩時間
                    if ($restEndTime->greaterThan($startOfNextDay)) {
                        $restStartTimeForNextDay = $startOfNextDay;
                        $totalRestSecondsForNextDay += $restEndTime->diffInSeconds($restStartTimeForNextDay);
                    }
                } else {
                    // 日付をまたがない場合の休憩時間
                    $totalRestSecondsForToday += $restEndTime->diffInSeconds($restStartTime);
                }
            }

            // 日付をまたいだ場合の当日の勤務時間から休憩時間を差し引く
            if ($workOverDay) {
                $totalWorkSecondsForToday = $workSecondsForToday - $totalRestSecondsForToday;
                $totalWorkSecondsForNextDay = $workSecondsForNextDay - $totalRestSecondsForNextDay;
            } else {
                $totalWorkSecondsForToday = $workSecondsForToday - $totalRestSecondsForToday;
                $totalWorkSecondsForNextDay = 0;
            }

            // 分を時間に変換（当日分）
            $totalRestHoursForToday = floor($totalRestSecondsForToday / 3600);
            $totalRestMinutesForToday = floor(($totalRestSecondsForToday % 3600) / 60);
            $totalRestSecondsForToday = $totalRestSecondsForToday % 60;
            $totalWorkHoursForToday = floor($totalWorkSecondsForToday / 3600);
            $totalWorkMinutesForToday = floor(($totalWorkSecondsForToday % 3600) / 60);
            $totalWorkSecondsForToday = $totalWorkSecondsForToday % 60;

            // データをオブジェクトとして格納（当日分）
            $data = new \stdClass();
            $data->user = $work->user->name;
            $data->startTime = $work->start_time;
            $data->endTime = $workOverDay ? $workEndTimeForToday->toDateTimeString() : $work->end_time;
            $data->totalRestHours = sprintf('%02d:%02d:%02d', $totalRestHoursForToday, $totalRestMinutesForToday, $totalRestSecondsForToday);
            $data->totalWorkHours = sprintf('%02d:%02d:%02d', $totalWorkHoursForToday, $totalWorkMinutesForToday, $totalWorkSecondsForToday);
            $data->workOverDay = $workOverDay;

            $calculatedData[] = $data;

            // 日付をまたいでいる場合の翌日分データ
            if ($workOverDay) {
                $totalRestHoursForNextDay = floor($totalRestSecondsForNextDay / 3600);
                $totalRestMinutesForNextDay = floor(($totalRestSecondsForNextDay % 3600) / 60);
                $totalRestSecondsForNextDay = $totalRestSecondsForNextDay % 60;
                $totalWorkHoursForNextDay = floor($totalWorkSecondsForNextDay / 3600);
                $totalWorkMinutesForNextDay = floor(($totalWorkSecondsForNextDay % 3600) / 60);
                $totalWorkSecondsForNextDay = $totalWorkSecondsForNextDay % 60;

                $nextDayData = new \stdClass();
                $nextDayData->user = $work->user->name;
                $nextDayData->startTime = $workStartTimeForNextDay->toDateTimeString();
                $nextDayData->endTime = $work->end_time;
                $nextDayData->totalRestHours = sprintf('%02d:%02d:%02d', $totalRestHoursForNextDay, $totalRestMinutesForNextDay, $totalRestSecondsForNextDay);
                $nextDayData->totalWorkHours = sprintf('%02d:%02d:%02d', $totalWorkHoursForNextDay, $totalWorkMinutesForNextDay, $totalWorkSecondsForNextDay);
                $nextDayData->workOverDay = false; // 翌日のデータは日付をまたいでいないのでフラグはfalse

                $calculatedData[] = $nextDayData;
            }
        }

        // ページネーションの設定
        $perPage = 5;
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;

        // データをコレクションに変換してページネーション可能にする
        $paginatedData = new LengthAwarePaginator(
            array_slice($calculatedData, $offset, $perPage, true),
            count($calculatedData),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // ビューにデータを渡して表示
        return view('user', compact('user', 'date', 'paginatedData', 'previousUrl', 'nextUrl'));
    }


}