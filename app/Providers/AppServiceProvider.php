<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\Paginator;
use App\Rules\NgWords;

class AppServiceProvider extends ServiceProvider
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
        // SSL通信対応
        if (\App::environment('production') || \App::environment('develop')) {
            \URL::forceScheme('https');
        }

        // カスタムバリデーションルールの定義
        Validator::extend('max_mb_str', function ($attribute, $value, $parameters, $validator) {
            $max = (int) $parameters[0];
            return mb_strlen($value, 'UTF-8') <= $max;
        });

        // Rules内のクラスを呼び出し、カスタムバリデーションルールの定義
        Validator::extend('ng_words', function ($attribute, $value, $parameters, $validator) {
            $rule = new NgWords();
            return $rule->passes($attribute, $value);
        });

        // ページネーション表示のため
        Paginator::useBootstrap();
    }
}
