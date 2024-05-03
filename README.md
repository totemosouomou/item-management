## 記事管理システム

### 環境構築手順

-   Git クローン
-   .env.example をコピーして .env を作成
-   MySQL のデータベース作成（名前：item_management）
-   Mac の場合 .env の DB_PASSWORD を root に修正（Windows は修正不要）

    ```INI
    DB_PASSWORD=root
    ```

-   APP_KEY 生成

    ```console
    php artisan key:generate
    ```

-   Composer インストール

    ```console
    composer install
    ```

-   フロント環境構築

    ```console
    npm ci
    npm run build
    ```

-   マイグレーション

    ```console
    php artisan migrate
    ```

-   起動

    ```console
    php artisan serve
    ```
