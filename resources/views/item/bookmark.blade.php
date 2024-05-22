@extends('adminlte::page')

@section('title', '記事管理：Bookmark')

@section('content_header')
    <h1>bookmark</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any() || session('error'))
                <div class="alert alert-danger">
                    <ul>
                        @if(session('error'))
                            <li>{{ session('error') }}</li>
                        @endif
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session()->has('requestSearch'))
                <div class="d-flex">
                    @foreach(session('requestSearch') as $searchWord)
                        <form method="post" action="{{ url()->current(['page' => null]) }}">
                            @csrf
                            <input type="hidden" name="clear" value="{{ $searchWord }}">
                            <button class="btn btn-outline-secondary mr-2" type="submit">
                                {{ $searchWord }} &times;
                            </button>
                        </form>
                    @endforeach
                </div>
            @endif

            @if (!$items->isEmpty())
                @foreach ($items as $item)
                    <a class="card bookmarks" href="#">
                        <div class="card-body">
                            <div class="d-flex flex-wrap">
                                <figure class="ml-3 mb-0 figure-area d-flex justify-content-between">
                                    <div class="text-content contents mt-1">
                                        <figcaption class="text-dark font-weight-bold">{{ $item->title }}</figcaption>
                                            @php
                                                $userPost = null;
                                                $userPostGet = $item->posts->where('user_id', Auth::user()->id)
                                                    ->first();
                                                if ($userPostGet) {
                                                    $userPost = str_replace(" by " . Auth::user()->name, "", $userPostGet->post);
                                                }
                                            @endphp
                                            @if ($userPost)
                                                <p class="list-inline-item mb-0" style="border-radius: 10px; padding: 1px 20px; font-size: 0.8em; background-color: rgba(250, 250, 250, 0.5); color: rgba(33, 37, 41, 0.8); text-decoration: none;">{{ $userPost }}</p>
                                            @else
                                                <p class="mb-0">{{ \Illuminate\Support\Str::limit($item->url, 45, $end='...') }}</p>
                                            @endif
                                    </div>
                                    @if($item->bookmarks->isNotEmpty())
                                        <img src="{{ asset('storage/' . $item->bookmarks->first()->thumbnail) }}" alt="Bookmark Thumbnail" class="img-fluid">
                                    @endif
                                </figure>
                            </div>
                        </div>
                    </a>
                @endforeach
            @else
                <p>No articles found.</p>
            @endif
                <!-- ページネーション -->
                @if ($items->hasPages())
                    <div class="card-footer clearfix pb-0">
                        {{ $items->appends(['search' => implode(' ', (array)session('requestSearch', []))])->links() }}
                    </div>
                @endif

            <!-- Qitta記事をapiで取得 -->
            @include('item.articles')
        </div>
    </div>
@stop

@section('footer')
    @include('vendor.adminlte.partials.footer')
@stop

@section('css')
@stop

@section('js')
    <script>
        // 画像が拡大する post-image クラスの設定
        $('.post-image').click(function() {
            // altText の値を取得
            var altText = $(this).attr('alt');

            // src の値を取得
            var src = $(this).attr('src');

            // showModalWithAlt 関数を呼び出す
            showModalWithAlt(altText, src, 60000);
        });

        // img 要素の alt の内容と src をモーダルへ渡す
        function showModalWithAlt(altText, src, duration) {
            // モーダルの存在をチェック
            if ($('#modalWrap').length > 0) {
                // モーダルが存在する場合は処理を実行
                showModal(altText, src, duration);
            }
        }

        // モーダルを表示するためのコード
        function showModal(altText, src, duration) {

            // bodyの最下にwrapを作る
            if ($('#modalWrap').length < 1) {
                $('body').append('<div id="modalWrap" />');
            }
            var wrap = $('#modalWrap');

            // モーダルの表示を切り替える
            wrap.fadeIn('200');

            // モーダルの中に content を挿入する
            var modalContent = '<div class="modalInner"><p>' + altText + '</p><img src="' + src + '" class="modalImage"></div>';
            $('.modalBox').html(modalContent);

            // 画像の読み込みが完了した後に画像のサイズを調整する
            $('.modalImage').on('load', function() {
                adjustImageSize();
            });

            // ウィンドウのリサイズ時に画像のサイズを再調整する
            $(window).on('resize', function () {
                adjustImageSize();
            });

            // モーダルをクリックしたら消える
            $('.modalBox').on('click', function () {
                clearTimeout(modalTimeout);
                clickAction();
                return false;
            });

            // 一定時間後に消える
            var modalTimeout = setTimeout(clickAction, duration);

            // モーダルをフェードイン
            $('.modalBox').fadeIn('200');
        }

        // キーボードの矢印キーでモーダル内の画像を切り替える
        $(document).keydown(function(e) {

            // モーダルが表示されている場合に動作
            if ($('#modalWrap').is(':visible')) {

                // 右矢印キーまたは下矢印キーが押された場合の処理
                if (e.keyCode == 39 || e.keyCode == 40) {

                    // すべての.tweet 要素をループ処理
                    $('.tweet').each(function() {
                        var currentSrc = $('.modalBox .modalInner img').attr('src');
                        var targetSrc = $(this).find('.post-image').attr('src');
                        var nextTweet = $(this).next('.tweet');

                        // 次の.tweet 要素が存在しないか、.post-image 要素が存在しない場合
                        while (nextTweet.length !== 0 && !nextTweet.find('.post-image').length) {
                            nextTweet = nextTweet.next('.tweet'); // 次の .tweet 要素を探す
                        }

                        // 次の要素の高さを取得し、その高さだけスクロールする
                        if (nextTweet.length !== 0 && currentSrc === targetSrc) {
                            var nextTweetHeight = nextTweet.offset().top;
                            if (e.keyCode == 40) {
                                $('html, body').animate({ scrollTop: nextTweetHeight }, 'fast');
                            } else {
                                $('html, body').scrollTop(nextTweetHeight);
                            }
                            nextTweet.find('.post-image').click();
                            console.log('Left arrow key pressed');
                            return false;
                        }
                    });
                }

                // 左矢印キーまたは上矢印キーが押された場合の処理
                else if (e.keyCode == 37 || e.keyCode == 38) {

                    // すべての.tweet 要素をループ処理
                    $('.tweet').each(function() {
                        var currentSrc = $('.modalBox .modalInner img').attr('src');
                        var targetSrc = $(this).find('.post-image').attr('src');
                        var prevTweet = $(this).prev('.tweet');

                        // 次の.tweet 要素が存在しないか、.post-image 要素が存在しない場合
                        while (prevTweet.length !== 0 && !prevTweet.find('.post-image').length) {
                            prevTweet = prevTweet.prev('.tweet'); // 次の .tweet 要素を探す
                        }

                        // 次の要素の高さを取得し、その高さだけスクロールする
                        if (prevTweet.length !== 0 && currentSrc === targetSrc) {
                            var prevTweetHeight = prevTweet.offset().top;
                            if (e.keyCode == 38) {
                                $('html, body').animate({ scrollTop: prevTweetHeight }, 'fast');
                            } else {
                                $('html, body').scrollTop(prevTweetHeight);
                            }
                            prevTweet.find('.post-image').click();
                            console.log('Left arrow key pressed');
                            return false;
                        }
                    });
                }
            }
        });

        // 画像のサイズを調整する関数
        function adjustImageSize() {

            var windowHeight = $(window).innerHeight();
            var windowWidth = $(window).innerWidth();
            var modalImage = $('.modalImage');
            var imageHeight = modalImage.height();
            var imageWidth = modalImage.width();

            // 画像の縦横比を維持しながら、表示画面の幅または高さに収まるように調整する
            if ((imageHeight / imageWidth) > (windowHeight / windowWidth)) {
                modalImage.css({ width: 'auto', height: windowHeight * 0.85 });
            } else {
                modalImage.css({ width: windowWidth * 0.95, height: 'auto' });
            }
        }

        // モーダルが非表示になったら要素の内容をクリアにする
        function clickAction() {
            $('.modalBox').fadeOut('200');
            $('#modalWrap').fadeOut('200', function () {
                $('.modalBox').empty();
            });
        }

        // 祖父要素の中で、親要素の指定の属性値が一致する要素を検索する関数
        function findDraggableInGrandparent(grandparentSelector, tagName, attributeName, attributeValue) {
            var grandparentDiv = document.querySelector(grandparentSelector);
            if (!grandparentDiv) {
                return [];
            }

            // 祖父要素の中で、親要素の指定の属性値が一致する要素を検索
            var parentElements = grandparentDiv.querySelectorAll(`${tagName}[${attributeName}="${attributeValue}"]`);
            return Array.from(parentElements);
        }
    </script>
@stop
