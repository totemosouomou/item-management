@extends('adminlte::page')

@section('title', '記事管理：一覧表示')

@section('content_header')
    <h1>一覧表示</h1>
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
                <div class="card-header">
                    <h3 class="card-title">{{ $title_name }}を表示</h3>
                    <div class="card-tools">
                        <button class="add-btn" style="font-weight: bold;" data-toggle="modal" data-target="#urlModalAdd">
                            記事登録
                        </button>
                    </div>
                </div>
                <div class="card-body">
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
                                            <form method="POST" action="{{ url('items/add') }}">
                                            @csrf
                                            <div class="row">
                                                <div class="col-sm-1">
                                                    <label for="title" class="form-label-sm text-muted" style="position: relative; top: 6px; left: 10px;">Title</label>
                                                </div>
                                                <div class="col-sm-11">
                                                    <input type="text" class="form-control mb-2" style="position: relative; top: 0px;" id="title" name="title" placeholder="記事のタイトルなど、わかりやすい見出しにしましょう">
                                                </div>
                                                <div class="col-sm-1">
                                                    <label for="url" class="form-label-sm text-muted" style="position: relative; top: 11px; left: 10px;">URL</label>
                                                </div>
                                                <div class="col-sm-11">
                                                    <input type="text" class="form-control mb-2" style="position: relative; top: 4px;" id="url" name="url" placeholder="リンク先となる記事のURLを入力しましょう">
                                                </div>
                                                <div class="col-sm-1">
                                                    <label for="post" class="form-label-sm text-muted" style="position: relative; top: 15px; left: 8px;">Post</label>
                                                </div>
                                                <div class="col-sm-11">
                                                    <input type="text" class="form-control mb-2" style="position: relative; top: 8px;" id="post" name="post" placeholder="コメントを入力できます">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 投稿ボタン -->
                                        <div class="d-flex">
                                            <div class="edit-form m-2 mr-4">
                                                @if (isset($titleNames[$stage]) && $stage !== $period)
                                                    <button type="submit" class="btn rounded-pill btn-size submit-btn ml-5" onclick="setPeriodValue('{{ $stage }}')">{{ $titleNames[$stage] }}へ投稿</button>
                                                    <button type="submit" class="btn rounded-pill btn-size mirror-btn ml-2" onclick="setPeriodValue('{{ $period }}')">{{ $titleNames[$period] }}へ投稿</button>
                                                    <input type="hidden" name="period" id="periodValue">
                                                @else
                                                    <button type="submit" class="btn rounded-pill btn-size submit-btn ml-5">投稿</button>
                                                @endif
                                                </form>
                                            </div>
                                        </div>

                                        <!-- ブログカード表示 -->
                                        <div class="iframely-embed mt-2">
                                            <div>
                                                <a href="https://youtu.be/kyRMuV8oJVY?si=dilNpM_bdLjLEKMe" data-iframely-url="//cdn.iframe.ly/api/iframe?url=https://youtu.be/kyRMuV8oJVY?si=dilNpM_bdLjLEKMe&autoplay=0&api_key={{ config('iframely.api.key') }}"></a>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        @foreach ($items as $item)
                            <figure class="m-3 contents" data-toggle="modal" data-target="#urlModal{{ $item->id }}">
                                <figcaption class="text-dark font-weight-bold">{{ $item->title }}</figcaption>
                                    <p>{{ \Illuminate\Support\Str::limit($item->url, 45, $end='...') }}</p>
                            </figure>

                            <!-- URL表示用のモーダル -->
                            <div class="modal fade" id="urlModal{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="urlModalLabel{{ $item->id }}" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title text-dark font-weight-bold" id="urlModalLabel{{ $item->id }}">
                                                {{ $item->title }}
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <p class="urls mx-3 my-0">
                                            <a href="{{ $item->url }}" style="text-decoration: none;" id="itemUrl_{{ $item->id }}">
                                                {{ \Illuminate\Support\Str::limit($item->url, 80, $end='...') }}
                                            </a>
                                        </p>
                                        <ul class="posts mx-3 mt-1 mb-0 list-unstyled list-inline" id="itemPost_{{ $item->id }}">
                                            @php
                                                $userPost = null;
                                            @endphp
                                            @foreach ($item->posts as $post)
                                                @if ($post->user_id == Auth::user()->id)
                                                    @php
                                                        $userPost = str_replace(" by " . Auth::user()->name, "", $post->post);
                                                    @endphp
                                                @endif
                                                <li class="list-inline-item mb-1" style="background-color: rgba(240, 240, 240, 0.6); border-radius: 10px; padding: 1px 20px; font-size: 0.8em; color: rgba(0, 0, 0, 0.6);">
                                                    {{ $post->post }}
                                                </li>
                                            @endforeach
                                        </ul>
                                        @if (!$userPost)
                                            <form method="POST" action="{{ url('items/update') }}" id="userPost_{{ $item->id }}">
                                                @csrf
                                                <div class="row mx-2 mt-2">
                                                    <div class="col-sm-1">
                                                        <label for="post" class="form-label-sm text-muted" style="position: relative; top: 6px; left: 8px;">Post</label>
                                                    </div>
                                                    <div class="col-sm-11">
                                                        <input type="text" class="form-control" id="post" name="post" placeholder="記事へコメントしましょう！">
                                                        <input type="hidden" name="title" value="{{ $item->title }}">
                                                        <input type="hidden" name="url" value="{{ $item->url }}">
                                                        <input type="hidden" name="id" value="{{ $item->id }}">
                                                        <button type="submit" id="submit-button" class="btn rounded-pill btn-size submit-btn mt-2">コメント送信</button>
                                                    </div>
                                                </div>
                                            </form>
                                        @endif

                                        <div class="modal-body">

                                            <!-- 編集用テキストボックス -->
                                            <div id="editBox_{{ $item->id }}" style="display: none;">
                                                <form method="POST" action="{{ url('items/update') }}">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-sm-1">
                                                        <label for="title" class="form-label-sm text-muted" style="position: relative; top: 6px; left: 10px;">Title</label>
                                                    </div>
                                                    <div class="col-sm-11">
                                                        <input type="text" class="form-control mb-2" style="position: relative; top: 0px;" id="title" name="title" placeholder="記事のタイトルなど、わかりやすい見出しにしましょう" value="{{ $item->title }}">
                                                    </div>
                                                    <div class="col-sm-1">
                                                        <label for="url" class="form-label-sm text-muted" style="position: relative; top: 11px; left: 10px;">URL</label>
                                                    </div>
                                                    <div class="col-sm-11">
                                                        <input type="text" class="form-control mb-2" style="position: relative; top: 4px;" id="url" name="url" placeholder="リンク先となる記事のURLを入力しましょう" value="{{ $item->url }}">
                                                    </div>
                                                    <div class="col-sm-1">
                                                        <label for="post" class="form-label-sm text-muted" style="position: relative; top: 15px; left: 8px;">Post</label>
                                                    </div>
                                                    <div class="col-sm-11">
                                                        <input type="text" class="form-control mb-2" style="position: relative; top: 8px;" id="post" name="post" placeholder="{{ $userPost ? $userPost : 'コメントを入力できます' }}" value="{{ $userPost }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- 編集用ボタンボックス -->
                                            <div id="editSubmitBox_{{ $item->id }}" style="display: none;">
                                                <div class="d-flex">
                                                    <div class="edit-form">
                                                        <input type="hidden" name="id" value="{{ $item->id }}">
                                                        <button type="submit" class="btn rounded-pill btn-size submit-btn" style="margin: 9px 15px 16px 56px;">更新して投稿</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- ブログカード表示 -->
                                            <div class="iframely-embed">
                                                <div>
                                                    <a href="{{ $item->url }}" data-iframely-url="//cdn.iframe.ly/api/iframe?url={{ $item->url }}&media=0&api_key={{ config('iframely.api.key') }}"></a>
                                                </div>
                                            </div>

                                            <!-- 編集・削除ボタン -->
                                            <div class="d-flex" style="height: 2.4em;">
                                                <div id="editBtn_{{ $item->id }}" class="edit-form m-2 mr-4">
                                                    <p class="btn rounded-pill btn-size mirror-btn" onclick="editItem('{{ $item->id }}');">編集</p>
                                                </div>
                                                @if(auth()->id() == $item->user_id)
                                                    <div id="deleteBtn_{{ $item->id }}" class="d-flex delete-form m-2 ml-4">
                                                        <form method="POST" action="{{ url('items/delete') }}" id="delete-form_{{ $item->id }}">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $item->id }}">
                                                            <p id="delete-btn_{{ $item->id }}" class="btn btn-outline-danger rounded-pill btn-size delete-btn" onclick="deleteItem('{{ $item->id }}');">削除</p>
                                                            <button id="delete-Submit_{{ $item->id }}" class="btn btn-danger rounded-pill btn-size" style="display: none; position: relative; left: 100px;">削除を実行するボタン</button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>

                                        </div>

                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                {{-- ページネーション --}}
                @if ($items->hasPages())
                    <div class="card-footer clearfix pb-0">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>
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
        // セッションに 'add' が設定されている場合のみ、モーダルを開く
        @if(session('add'))
            $(document).ready(function() {
                $('#urlModalAdd').modal('show');
            });
        @endif

        function setPeriodValue(period) {
            document.getElementById('periodValue').value = period;
        }

        // モーダルが閉じたときのイベントを検知するためのスクリプト
        $('.modal').on('hidden.bs.modal', function (e) {
            var itemId = $(this).attr('id').replace('urlModal', '');

            // itemIdが"Add"の場合は処理をスキップする
            if (itemId === "Add") {
                return;
            }

            // テキストを元の表示状態に戻す
            document.getElementById('urlModalLabel' + itemId).style.display = 'block';
            document.getElementById('itemUrl_' + itemId).style.display = 'block';
            document.getElementById('itemPost_' + itemId).style.display = 'block';
            var userPostElement = document.getElementById('userPost_' + itemId);
            if (userPostElement) {
                userPostElement.style.display = 'block';
            }

            // 入力フィールドを非表示にする
            document.getElementById('editBox_' + itemId).style.display = 'none';

            // ボタンエリアの余白を調整
            var parentElement = document.getElementById('editBtn_' + itemId).parentElement;
            parentElement.style.height = '2.4em';

            // 編集ボタンを元に戻す
            document.getElementById('editSubmitBox_' + itemId).style.display = 'none';
            document.getElementById('editBtn_' + itemId).style.display = 'block';

            // 削除ボタンを元に戻す（存在する場合）
            var deleteBtnAria = document.getElementById('deleteBtn_' + itemId);
            var deleteBtn = document.getElementById('delete-btn_' + itemId);
            var deleteSubmitBtn = document.getElementById('delete-Submit_' + itemId);
            if (deleteBtnAria) {
                deleteBtnAria.style.display = 'block';
                deleteBtn.style.display = 'block';
                deleteSubmitBtn.style.display = 'none';
            }
        });

        // 編集ボタンがクリックされたときの処理
        function editItem(itemId) {
            // テキストを非表示にする
            var titleElement = document.getElementById('urlModalLabel' + itemId).style.display = 'none';
            var urlElement = document.getElementById('itemUrl_' + itemId).style.display = 'none';
            var postElement = document.getElementById('itemPost_' + itemId).style.display = 'none';
            var userPostElement = document.getElementById('userPost_' + itemId);
            if (userPostElement) {
                userPostElement.style.display = 'none';
            }

            // 入力フィールドを表示する
            document.getElementById('editBox_' + itemId).style.display = 'block';

            // 編集ボタンを有効にする
            document.getElementById('editSubmitBox_' + itemId).style.display = 'block';

            // ボタンエリアの余白を調整
            var parentElement = document.getElementById('editBtn_' + itemId).parentElement;
            parentElement.style.height = '0';

            // editBtn を非表示にする
            var editBtn = document.getElementById('editBtn_' + itemId);
            editBtn.style.display = 'none';

            // deleteBtn を非表示にする（存在する場合）
            var deleteBtnAria = document.getElementById('deleteBtn_' + itemId);
            var deleteBtn = document.getElementById('delete-btn_' + itemId);
            if (deleteBtnAria) {
                deleteBtnAria.style.display = 'none';
                deleteBtn.style.display = 'none';
            }
        }

        // 削除ボタンがクリックされたときの処理
        function deleteItem(itemId) {
            var deleteBtn = document.getElementById('delete-btn_' + itemId);
            var deleteSubmitBtn = document.getElementById('delete-Submit_' + itemId);

            // アニメーションのスタート
            var animation = deleteBtn.animate([
                { transform: 'translateX(0)' },
                { transform: 'translateX(100px)' }
            ], {
                duration: 500, // アニメーションの時間（ミリ秒）
                easing: 'ease', // アニメーションのイージング
            });

            // 削除実行ボタンを有効にする
            animation.onfinish = function() {
                deleteBtn.style.display = 'none';
                deleteSubmitBtn.style.display = 'block';
            };
        }

    </script>
@stop
