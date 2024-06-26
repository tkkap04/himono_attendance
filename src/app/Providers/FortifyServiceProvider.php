<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\RegisterRequest as FortifyRegisterRequest;
use App\Http\Requests\RegisterRequest;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        // 登録画面
        Fortify::registerView(function () {
            return view('auth.register');
        });
        
        // ログイン画面
        Fortify::loginView(function () {
            return view('auth.login');
        });
        
        // メール送信後画面
        Fortify::verifyEmailView(function () {
            return view('auth.verify');
        });

        // 新規登録後のイベントリスナー
        Event::listen(Registered::class, function ($event) {
            // 登録されたユーザーにメールを送信
            $event->user->sendEmailVerificationNotification();
            // ユーザーを自動的にログインし、/verify ページにリダイレクト
            Auth::login($event->user);
            // ユーザーに認証メールを送信
            $event->user->sendEmailVerificationNotification();
            return redirect()->route('verification.notice');
        });

        // 会員登録後のリダイレクト先を /verify に変更
        Fortify::redirects('register', '/verify');

        // ログイン試行のレートリミット
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(10)->by($email . $request->ip());
        });

        $this->app->bind(FortifyRegisterRequest::class, RegisterRequest::class);
        $this->app->bind(FortifyLoginRequest::class, LoginRequest::class);

        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                        ->from(config('mail.from.address'), config('mail.from.name')) // 送信元のアドレスと名前
                        ->subject('メールアドレスの確認')
                        ->line('以下のリンクをクリックしてメールアドレス認証を完了してください。')
                        ->action('メールアドレスを確認', $url)
                        ->line('※こちらのメールは送信専用のメールアドレスより送信しています。');
        });
    }
}
