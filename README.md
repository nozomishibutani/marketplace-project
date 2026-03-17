# フリマアプリ（Flea Market App）

### 概要

本アプリは、ユーザー同士で商品を売買できるフリマアプリです。
ユーザーは商品の出品・購入・コメント・お気に入り登録を行うことができます。

### 主な機能

* ユーザー登録・ログイン機能(メール認証付き)
* 商品の出品
* 商品一覧表示・キーワード検索
* 商品詳細表示（コメント・いいね機能付き）
* お気に入り登録（マイリスト機能）
* 商品購入機能（Stripe決済連携）
* プロフィール登録・編集

### 特徴

* メール認証機能の実装によるユーザー認証の安全性向上
* 中間テーブルを用いたお気に入り機能、カテゴリー機能の実装
* 決済APIとの連携による購入フローの実現

### 使用技術
<img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white">
<img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white">
<img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white">
<img src="https://img.shields.io/badge/Nginx-009639?style=for-the-badge&logo=nginx&logoColor=white">
<img src="https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white">

- PHP 8.1.34
- Laravel 8.83.29
- MySQL 8.0.26
- nginx 1.21.1
- docker 29.2.0
- Docker Compose 5.0.2



## 環境構築
## Dockerビルド

```bash
- git clone git@github.com:nozomishibutani/marketplace-project.git
- DockerDesktopアプリを立ち上げる
- docker-compose up -d --build
# docker-compose up を実行する前に、プロジェクトのルートディレクトリ（docker-compose.yml がある場所）に移動してください。
```

## Laravel環境構築

```bash
1. docker-compose exec php bash
2. composer install
3. 「.env.example」ファイルを 「.env」に名称変更。または、新しく.envファイルを作成
4. envに参考(1)の環境変数を追加 
5. php artisan key:generate
6. php artisan migrate
7. php artisan db:seed

#  テスト用も同様に
8. テスト用に「.env.example」ファイルを 「.env.testing」に名称変更。または、新しく.env.testingファイルを作成
9. .env.testingに参考(2)の環境変数を追加
10. php artisan key:generate --env=testing
11. php artisan migrate --env=testing
12. php artisan db:seed --env=testing

⚠ Windows + WSL 環境で開発する場合
- プロジェクト内のファイルで権限エラーが出ることがあります
- 必要に応じて各自で権限を調整してください
- sudo chmod -R 777 src/*

# 参考(1)
⚠.envを書き換える際に権限エラーが出る場合は、適宜権限を変更してください。
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

Mailtrap（メール認証用）
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=xxxx
MAIL_PASSWORD=xxxx

# 適宜設定
# 50通以上メール送信するとフリープランでは送信できなくなります、　MAIL_MAILER=log　を設定するとメール送信はされずlaravel.logに出力されます。
MAIL_MAILER=log

Stripe
STRIPE_KEY=pk_test_xxxx
STRIPE_SECRET=sk_test_xxxx

# 参考(2)
⚠.env.testingを書き換える際に権限エラーが出る場合は、適宜権限を変更してください。
APP_ENV=test
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=demo_test
DB_USERNAME=root
DB_PASSWORD=root

Mailtrap（メール認証用）
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=xxxx
MAIL_PASSWORD=xxxx

Stripe
# .envの内容でOK
STRIPE_KEY=pk_test_xxxx
STRIPE_SECRET=sk_test_xxxx
```


## その他ツール
```md id="tip01"
※ MailtrapおよびStripeのアカウントは各自で作成してください。
```

## 開発環境
- お問い合わせ画面：http://localhost/
- ユーザー登録：http://localhost/register
- 管理画面：http://localhost/admin
- phpMyAdmin：http://localhost:8080/



## ER図
[erd.md](erd.md)