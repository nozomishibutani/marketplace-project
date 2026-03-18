# フリマアプリ（Flea Market App）

**概要**
*  本アプリは、ユーザー同士で商品を売買できるフリマアプリです。
* ユーザーは商品の出品・購入・コメント・お気に入り登録を行うことができます。

**主な機能**
* ユーザー登録・ログイン機能(メール認証付き)
* 商品の出品
* 商品一覧表示・キーワード検索
* 商品詳細表示（コメント・いいね機能付き）
* お気に入り登録（マイリスト機能）
* 商品購入機能（Stripe決済連携）
* プロフィール登録・編集

**外部サービス**
* 本アプリでは以下を使用しています（⚠️各自でアカウント設定する必要があります）
*  [Stripe（決済）](https://stripe.com/jp)
*  [Mailtrap（メール認証）](https://mailtrap.io/signin)

**設定方法**
* .env、.env.testingファイルに環境変数を設定してください。
* 詳細は各公式ドキュメントを参照してください。

## 環境構築
**Dockerビルド**
1. `git clone git@github.com:nozomishibutani/marketplace-project.git`
2. DockerDesktopアプリを立ち上げる
3. プロジェクトのルートディレクトリ（docker-compose.yml がある場所）に移動
4. `docker-compose up -d --build`

**Laravel環境構築**
1. `docker-compose exec php bash`
2. `composer install`
3. 「.env.example」ファイルを 「.env」ファイルに命名を変更。または、新しく.envファイルを作成
4. .envに以下の環境変数を追加
``` text
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

# Mailtrap（メール認証）
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=xxxx
MAIL_PASSWORD=xxxx
MAIL_FROM_ADDRESS=test@example.com # 値は自由です
MAIL_FROM_NAME="Test App" # 値は自由です

# Stripe（決済）
STRIPE_KEY=pk_test_xxxx
STRIPE_SECRET=sk_test_xxxx
```

5. アプリケーションキーの作成
``` bash
php artisan key:generate
```
6. マイグレーションの実行
``` bash
php artisan migrate
```
7. シーディングの実行
``` bash
php artisan db:seed
```

**Laravel PHPUnitテスト 環境構築**

1. テスト用データベースの作成
``` text
docker-compose exec mysql bash
mysql -u root -p
```
- パスワードは docker-compose.yml の MYSQL_ROOT_PASSWORD に設定されている値を使用してください。
- デフォルトでは root が設定されています

``` text
CREATE DATABASE demo_test;
```
2. 「.env.example」ファイルを 「.env.testing」ファイルに命名を変更。または、新しく.env.testingファイルを作成
``` text
APP_ENV=test

DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=demo_test
DB_USERNAME=root
DB_PASSWORD=root

# Stripe（決済）
# .envと同じ内容でOK
STRIPE_KEY=pk_test_xxxx
STRIPE_SECRET=sk_test_xxxx
```
5. アプリケーションキーの作成
``` bash
php artisan key:generate --env=testing
```
6. マイグレーションの実行
``` bash
php artisan migrate --env=testing
```
7. シーディングの実行
``` bash
php artisan db:seed --env=testing
```
## ⚠️ 注意事項
### 権限エラーについて（Windows環境）
Windows + Docker 環境では、以下のような権限エラーが発生する場合があります。
> The stream or file "/var/www/storage/logs/laravel.log" could not be opened in append mode: Failed to open stream: Permission denied The exception occurred while attempting to log

#### 対処方法
以下のコマンドで権限を変更してください。
※ 本来は必要なディレクトリのみに権限付与するのが望ましいです。
```bash
sudo chmod -R 777 src/*
```
###  Mailtrap（メール認証）
- フリープランでは **送信数（最大50通）および送信頻度に制限**があります
- 短時間にメールを送信しすぎる、または送信数の上限に達すると、エラーが発生します
- 制限を回避する場合は、`.env` に以下を設定してください

MAIL_MAILER=log

- 上記設定により、メールは送信されず `laravel.log` に出力されます

---

### Stripe（決済）
#### コンビニ支払い
- テストモードでは、コンビニ決済を無効にしていても動作します
- 設定 > Payments > 決済手段 からコンビニ決済を有効にすることが可能です
- 取引額：120円 ～ 300,000円
- この範囲外の金額はエラーになります
- 参考：[Stripe コンビニ決済](https://docs.stripe.com/payments/konbini)

#### カード支払い
- 下限：50円
- 推奨上限（公式ドキュメント）：99,999,999円
- 推奨上限を超えた場合でも、技術的には Stripe の API 上で決済可能ですが、本番環境ではカード会社などに制限される場合があります
- 参考：[Stripe 通貨と最小/最大決済額](https://docs.stripe.com/currencies#minimum-and-maximum-charge-amounts)

#### テストカード
- カード番号（テスト用）：`4242 4242 4242 4242`
- CVC：任意（例：123）
- 有効期限：任意（例：12/34）

> このカードは Stripe のテストモード専用です。実際の請求は発生しません。

#### 決済後の画面遷移について
コンビニ決済はオフライン決済のため、決済完了後にアプリへリダイレクトされません。<br>
そのため、カード決済とは異なり、商品一覧画面への自動遷移は行われません。<br>

### PHPUnit テスト実行手順
本アプリでは、PHPUnit を用いたテストを実装しています。

#### 前提
- 上記記載の Laravel PHPUnitテスト 環境構築 が完了していること

#### 実行方法
全てのテストを実行する場合：

```bash
docker-compose exec php bash
vendor/bin/phpunit
```
特定のテストのみ実行する場合

- 会員登録
```bash
vendor/bin/phpunit tests/Feature/Auth/RegisterTest.php
```
- 商品一覧取得
```bash
vendor/bin/phpunit tests/Feature/Item/GetItemListTest.php
```

## 使用技術(実行環境)
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

## URL
- 開発環境：http://localhost/
- phpMyAdmin:：http://localhost:8080/

![alt](ER.png)