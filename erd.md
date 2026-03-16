# ER図

## tweetデータベース

```mermaid
---
title: "タイトル"
---
erDiagram
    users ||--o{ tweets : ""

    users {
        bigint id PK "ID"
        varchar name "名称"
        varchar username "ユーザー名"
        varchar description "説明"
        timestamp deleted_at "削除日時"
        timestamp created_at "作成日時"
        timestamp updated_at "更新日時"
    }

    tweets {
        bigint id PK "ID"
        bigint author_id FK "オーサーID:users.id"
        varchar tweet "ツイート"
        timestamp deleted_at "削除日時"
        timestamp created_at "作成日時"
        timestamp updated_at "更新日時"
    }
```