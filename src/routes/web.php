<?php

use App\Http\Controllers\WorkController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerifyController;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// ログイン済みかつメール認証済みの場合にのみアクセスできる
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [WorkController::class, 'index']);
    Route::post('/start-work', [WorkController::class, 'startWork']);
    Route::post('/end-work', [WorkController::class, 'endWork']);
    Route::post('/start-rest', [WorkController::class, 'startRest']);
    Route::post('/end-rest', [WorkController::class, 'endRest']);
    Route::get('/attendance', [WorkController::class, 'show'])->name('attendance.show');
    Route::get('/list', [UserController::class, 'list']);
    Route::get('/list/{id}/attendance', [WorkController::class, 'showUserAttendance'])->name('list.attendance');
});

Route::get('/verify', function () {
    return view('auth.verify');
})->middleware('auth')->name('verification.notice');

Route::get('/success', function() {
    return view('auth.success');
})->middleware('auth')->name('verification.success');

// ユーザーがメール内のリンクをクリックしたときに処理するルート
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request){
    $request->fulfill();
    return redirect('/success');
})->middleware(['auth', 'signed'])->name('verification.verify');

// リクエストされた認証メールを再送信するルート
Route::post('/email/resend', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

