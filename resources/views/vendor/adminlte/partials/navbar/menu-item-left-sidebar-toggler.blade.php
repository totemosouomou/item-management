<li class="nav-item">
    <a class="nav-link" data-widget="pushmenu" href="#"
        @if(config('adminlte.sidebar_collapse_remember'))
            data-enable-remember="true"
        @endif
        @if(!config('adminlte.sidebar_collapse_remember_no_transition'))
            data-no-transition-after-reload="false"
        @endif
        @if(config('adminlte.sidebar_collapse_auto_size'))
            data-auto-collapse-size="{{ config('adminlte.sidebar_collapse_auto_size') }}"
        @endif>
        <i class="fas fa-bars"></i>
        <span class="sr-only">{{ __('adminlte::adminlte.toggle_navigation') }}</span>
    </a>
</li>

<!-- レスポンシブ時のナビゲーションメニュー -->
<li class="curriculum-nav-item">
    <a href="#" class="nav-link">カリキュラム一覧</a>
    <ul class="curriculum-dropdown">
        <li class="mt-1 mx-3"><a href="https://www.techis-learning.jp/top/基礎課題">基礎課題</a></li>
        <li class="mt-1 mx-3"><a href="https://www.techis-learning.jp/top/応用課題">応用課題</a></li>
        <li class="my-1 mx-3"><a href="https://www.techis-learning.jp/top/開発課題">開発課題</a></li>
    </ul>
</li>

<!-- デフォルトのナビゲーションメニュー -->
<li class="curriculum-nav-item nav-item nav-item-responsive">
    <a href="https://www.techis-learning.jp/top/基礎課題">
        <button class="btn btn-primary">基礎課題</button>
    </a>
</li>
<li class="curriculum-nav-item nav-item nav-item-responsive">
    <a href="https://www.techis-learning.jp/top/応用課題">
        <button class="btn btn-primary">応用課題</button>
    </a>
</li>
<li class="curriculum-nav-item nav-item nav-item-responsive">
    <a href="https://www.techis-learning.jp/top/開発課題">
        <button class="btn btn-primary">開発課題</button>
    </a>
</li>
