@extends('adminlte::page')

@section('title', '記事管理：Bookmark')

@section('content_header')
    <h1>投稿＆ブックマーク</h1><img class="option-btn mt-1 mr-1" src="{{ asset('image/option-icon.png') }}" alt="option-icon" onClick();>
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

            <div class="card">
                <div class="card-header card-container">
                    <h3 class="card-title mb-0">{{ $title_name }}を表示</h3>
                    <div class="card-tools">
                        <button class="add-btn" style="font-weight: bold;" data-toggle="modal" data-target="#urlModalAdd">
                            記事登録
                        </button>
                    </div>
                </div>
                <div class="card-body">
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
                    <div class="d-flex flex-wrap">

                        <!-- add表示用のモーダル -->
                        <div class="modal fade" id="urlModalAdd" tabindex="-1" role="dialog" aria-labelledby="urlModalLabelAdd" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title text-dark font-weight-bold" id="urlModalLabelAdd">
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">

                                        <!-- 投稿用テキストボックス -->
                                        <div>
                                            <form method="post" action="{{ url('items/add') }}">
                                            @csrf
                                            <div class="row">
                                                <div class="col-sm-1">
                                                    <label for="url" class="form-label-sm text-muted" style="position: relative; top: 11px; left: 10px;">URL</label>
                                                </div>
                                                <div class="col-sm-11">
                                                    <input type="text" class="form-control mb-2" style="position: relative; top: 4px;" id="url" name="url" placeholder="URLを入力しましょう" value="{{ session('urlInput') ?: old('url') }}">
                                                </div>
                                                <div class="col-sm-1">
                                                    <label for="title" class="form-label-sm text-muted" style="position: relative; top: 6px; left: 10px;">Title</label>
                                                </div>
                                                <div class="col-sm-11">
                                                    <input type="text" class="form-control mb-2" style="position: relative; top: 0px;" id="title" name="title" placeholder="わかりやすいタイトルにしましょう" value="{{ old('title') }}" onClick="urlToTitle(this)">
                                                </div>
                                                <div class="col-sm-1">
                                                    <label for="post" class="form-label-sm text-muted" style="position: relative; top: 15px; left: 8px;">Post</label>
                                                </div>
                                                <div class="col-sm-11">
                                                    <input type="text" class="form-control mb-2" style="position: relative; top: 8px;" id="post" name="post" placeholder="コメントを入力できます" value="{{ old('post') }}">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 投稿ボタン -->
                                        <div class="d-flex">
                                            <div class="edit-form m-2 mr-4">
                                                @if (isset($titleNames[$stage]) && $stage !== $period)
                                                    <button type="submit" class="btn rounded-pill btn-size submit-btn ml-5 submit-responsive" onclick="setPeriodValue('{{ $stage }}')">{{ $titleNames[$stage] }}へ投稿</button>
                                                    <button type="submit" class="btn rounded-pill btn-size mirror-btn ml-2 submit-responsive" onclick="setPeriodValue('{{ $period }}')">{{ $titleNames[$period] }}へ投稿</button>
                                                    <input type="hidden" name="period" id="periodValue">
                                                @else
                                                    <button type="submit" class="btn rounded-pill btn-size submit-btn ml-5">投稿</button>
                                                @endif
                                                </form>
                                            </div>
                                        </div>

                                        <!-- ブログカード表示 -->
                                        <div class="iframely-embed mt-2" style="display: none;"></div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($pinnedItems->count() >= 5 )
                            @foreach ($pinnedItems->take(5) as $item)
                                <form id="pin-form_{{ $item->id }}" method="post" action="{{ url('items/pin') }}">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                    <input type="hidden" name="bookmarkId" value="{{ $item->bookmarks->first()->id }}">
                                    <figure type="submit" class="m-3 figcaption-contents" onclick="pinItem('{{ $item->id }}');">
                                        <figcaption class="text-dark font-weight-bold">{{ $item->title }}</figcaption>
                                            @if ($item->posts->where('user_id', Auth::user()->id)->first())
                                                <p class="list-inline-item mb-0" style="border-radius: 10px; padding: 1px 20px; font-size: 0.8em; background-color: rgba(250, 250, 250, 0.5); color: rgba(33, 37, 41, 0.8); text-decoration: none;">{{ str_replace(" by " . Auth::user()->name, "", $item->posts->where('user_id', Auth::user()->id)->first()->post) }}</p>
                                            @else
                                                <p class="mb-0">{{ \Illuminate\Support\Str::limit($item->url, 45, $end='...') }}</p>
                                            @endif
                                    </figure>
                                </form>
                            @endforeach
                        @elseif ($pinnedItems->isNotEmpty())
                            @foreach ($pinnedItems as $item)
                                <a class="bookmarks" href="{{ $item->url }}">
                                    <figure class="m-3 figcaption-contents" data-toggle="modal" data-target="#urlModal{{ $item->id }}" onClick="openModal(this, '{{ $item->url }}')">
                                        <figcaption class="text-dark font-weight-bold">{{ $item->title }}</figcaption>
                                            @if ($item->posts->where('user_id', Auth::user()->id)->first())
                                                <p class="list-inline-item mb-0" style="border-radius: 10px; padding: 1px 20px; font-size: 0.8em; background-color: rgba(250, 250, 250, 0.5); color: rgba(33, 37, 41, 0.8); text-decoration: none;">{{ str_replace(" by " . Auth::user()->name, "", $item->posts->where('user_id', Auth::user()->id)->first()->post) }}</p>
                                            @else
                                                <p class="mb-0">{{ \Illuminate\Support\Str::limit($item->url, 45, $end='...') }}</p>
                                            @endif
                                    </figure>
                                </a>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            @if ($unpinnedItems->isNotEmpty())
                <div class="bookmark-items">
                @foreach ($unpinnedItems as $item)
                    @php
                        $imagePath = 'public/bookmarks/' . $item->id . '.png';
                        $userPost = null;
                        $userPostGet = $item->posts->where('user_id', Auth::user()->id)
                            ->first();
                        if ($userPostGet) {
                            $userPost = str_replace(" by " . Auth::user()->name, "", $userPostGet->post);
                        }
                    @endphp
                    <a class="card bookmark-card card-body bookmarks bookmark-block" href="{{ $item->url }}">
                        <div class="d-flex flex-wrap">
                            <figure class="ml-2 mb-0 figure-area d-flex justify-content-between">
                                <div class="{{ Storage::exists($imagePath) ? 'text-content' : '' }} figcaption-contents mt-1">
                                    <figcaption class="text-dark font-weight-bold">{{ $item->title }}</figcaption>
                                        @if ($userPost)
                                            <p class="list-inline-item mb-0" style="border-radius: 10px; padding: 1px 20px; font-size: 0.8em; background-color: rgba(250, 250, 250, 0.5); color: rgba(33, 37, 41, 0.8); text-decoration: none;">{{ $userPost }}</p>
                                        @else
                                            <p class="mb-0">{{ \Illuminate\Support\Str::limit($item->url, 45, $end='...') }}</p>
                                        @endif
                                </div>
                                @if ($item->bookmarks->isNotEmpty() && $item->bookmarks->first()->thumbnail)
                                    <div class="img-container" id="{{ $item->id }}">
                                        <img src="{{ $item->bookmarks->first()->thumbnail }}" alt="Thumbnail" onClick="event.preventDefault();">
                                    </div>
                                @endif
                            </figure>
                        </div>
                        <div class="card-option">
                            <form method="post" action="{{ url('items/pin') }}" class="mt-1 mb-3">
                                @csrf
                                <input type="hidden" name="id" value="{{ $item->id }}">
                                <input type="hidden" name="bookmarkId" value="{{ $item->bookmarks->first()->id }}">
                                <button type="submit" class="py-1 btn rounded-pill btn-size submit-btn">ピン</button>
                            </form>
                            <form method="post" action="{{ url('items/delete') }}">
                                @csrf
                                <input type="hidden" name="id" value="{{ $item->id }}">
                                <div class="" style="position: absolute; right: 15px; bottom: 10px;">
                                    <button type="submit" class="flag-btn ml-2">削除</button>
                                </div>
                            </form>
                        </div>
                    </a>
                @endforeach
                </div>
            @elseif ($items->isEmpty())
                <p>No articles found.</p>
            @endif
                </div>

                <!-- ページネーション -->
                @if ($items->hasPages())
                    <div class="card-footer clearfix pb-0">
                        {{ $items->appends(['search' => implode(' ', (array)session('requestSearch', []))])->links() }}
                    </div>
                @endif
            </div>

            <!-- Qitta記事をapiで取得 -->
            @include('item.articles')

            </div>
            <div class="container-bar"></div>
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
        // 三点リーダーを開いてピンと削除ボタンを表示する
        document.addEventListener('DOMContentLoaded', function() {
            const optionBtn = document.querySelector('.option-btn');
            const cards = document.querySelectorAll('.bookmark-card');
            cards.forEach(card => {
                card.style.minWidth = '60%';
            });

            optionBtn.addEventListener('click', function() {
                const bookmarkItems = document.querySelector('.bookmark-items');
                if (bookmarkItems) {
                    const currentDisplay = getComputedStyle(bookmarkItems).display;
                    bookmarkItems.style.display = currentDisplay === 'flex' ? 'block' : 'flex';
                }
                cards.forEach(card => {
                    const currentMinWidth = card.style.minWidth;
                    card.style.minWidth = currentMinWidth === '' ? '100%' : '60%';
                    card.classList.toggle('toggle');
                });
            });
        });

        // ピンを処理するスクリプト
        function pinItem(itemId) {

            // フォームの送信を実行
            var form = document.getElementById('pin-form_' + itemId);
            if (form) {
                form.submit();
            }
        }

        // リンク先の画像の長さによらずスクロール速度を固定にする
        document.addEventListener('DOMContentLoaded', function() {
            const imgContainers = document.querySelectorAll('.img-container');

            imgContainers.forEach((container) => {
                const img = container.querySelector('img');
                let bottomValue;

                img.addEventListener('load', function() {
                    const containerRect = container.getBoundingClientRect();
                    const windowCenter = window.innerHeight / 2;
                    const distanceFromCenter = containerRect.bottom - windowCenter;
                    const dampingFactor = 0.66;
                    let bottomValue = distanceFromCenter * dampingFactor;
                    const imgHeight = img.naturalHeight;
                    const duration = imgHeight / 750;
                    img.style.objectPosition = '0 0';

                    container.addEventListener('mouseover', function() {
                        img.style.transitionDuration = `${duration}s`;
                        console.log(`Image ${img.id} duration set to: ${duration}s`);
                        img.style.objectPosition = '0 100%';
                        container.closest('.card').style.zIndex = '10000';
                        container.style.setProperty('bottom', `${bottomValue}px`);
                    });

                    container.addEventListener('mouseout', function() {
                        img.style.transitionDuration = `${duration*0.25}s`;
                        img.style.objectPosition = '0 0';
                        container.closest('.card').style.zIndex = '1';
                        container.style.setProperty('bottom', `11px`);
                    });
                });

                if (img.complete) {
                    img.dispatchEvent(new Event('load'));
                }
            });
        });
    </script>
@stop
