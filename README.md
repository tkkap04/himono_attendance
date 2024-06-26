# himono_attendance
# アプリケーション名
Atte
![Stamp](https://github.com/tkkap04/himono_attendance/blob/main/stamp.png)



##作成した目的
毎日の勤怠状況を記録して人事評価に用いるため

##アプリケーションURL
http://13.115.101.13
ログインパスワードは8文字以上

##機能一覧
- 会員登録機能
- ログイン機能
- メール認証機能
- 打刻機能（勤務開始、勤務終了、休憩開始、休憩終了）
- 日付別勤怠情報表示機能
- ユーザー一覧昨日
- ユーザー別勤怠情報表示機能

## 使用技術(実行環境)
- Laravel Framework 8.83.27
- PHP 7.4.9

##テーブル設計

![Table](https://github.com/tkkap04/himono_attendance/blob/main/table.png)

##ER図

![Atte](https://github.com/tkkap04/himono_attendance/blob/main/atte.png)

## 環境構築
- Dockerのビルドからマイグレーション、シーディングまでを記述する
1. docker-compose exec php bash
2. composer install
3. .env.exampleファイルから.envを作成し、環境変数を変更
4. php artisan key:generate
5. php artisan migrate

## URL
- 開発環境：http://localhost/
- phpmyadmin：http://localhost:8080/
