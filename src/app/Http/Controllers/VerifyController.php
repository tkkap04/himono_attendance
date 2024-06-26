<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Auth\Events\Registered;

class VerifyController extends Controller
{
    protected $createNewUser;

    public function __construct(CreateNewUser $createNewUser)
    {
        $this->createNewUser = $createNewUser;
    }

    public function register(Request $request)
    {
        $user = $this->createNewUser->create($request->all());
        
        // ユーザーが登録された後、メールの検証通知を送信する
        event(new Registered($user));

        // 自動的にログイン
        Auth::login($user);
        
        // リダイレクト
        return redirect()->route('verification.notice');
    }
}
