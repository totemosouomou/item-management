@extends('adminlte::page')

@section('title', '一覧表示')

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
                    <h3 class="card-title">{{ $user_name }}を表示</h3>
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
                                                    <label for="title" class="form-label-sm text-muted" style="position: relative; top: -5px;">Title</label>
                                                </div>
                                                <div class="col-sm-11">
                                                    <input type="text" class="form-control mb-2" style="position: relative; top: -10px;" name="title" placeholder="記事のタイトルなど、わかりやすい見出しにしましょう">
                                                </div>
                                                <div class="col-sm-1">
                                                    <label for="url" class="form-label-sm text-muted" style="position: relative; top: -5px;">URL</label>
                                                </div>
                                                <div class="col-sm-11">
                                                    <input type="text" class="form-control mb-2" style="position: relative; top: -10px;" name="url" placeholder="リンク先となる記事のURLを入力しましょう">
                                                </div>
                                                <div class="col-sm-1">
                                                    <label for="post" class="form-label-sm text-muted" style="position: relative; top: 5px;">Post</label>
                                                </div>
                                                <div class="col-sm-11">
                                                    <input type="text" class="form-control mb-2" name="post"  placeholder="コメントを入力できます">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 投稿ボタン -->
                                        <div class="d-flex" style="height: 2.4em;">
                                            <div class="edit-form m-2 mr-4">
                                                    <button class="btn btn-primary rounded-pill" style="background-color: #00abae; border: 0; padding: 7px 30px;">投稿</button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- ブログカード表示 -->
                                        <div class="iframely-embed mt-4">
                                            <div class="iframely-responsive" style="height: 170px; padding-bottom: 0;">
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

                                        <div class="modal-body">

                                            <!-- 編集用テキストボックス -->
                                            <div id="editBox_{{ $item->id }}" style="display: none;">
                                                <form method="POST" action="{{ url('items/update') }}">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-sm-1">
                                                        <label for="title" class="form-label-sm text-muted" style="position: relative; top: -5px;">Title</label>
                                                    </div>
                                                    <div class="col-sm-11">
                                                        <input type="text" class="form-control mb-2" style="position: relative; top: -10px;" name="title" placeholder="記事のタイトルなど、わかりやすい見出しにしましょう" value="{{ $item->title }}">
                                                    </div>
                                                    <div class="col-sm-1">
                                                        <label for="url" class="form-label-sm text-muted" style="position: relative; top: -5px;">URL</label>
                                                    </div>
                                                    <div class="col-sm-11">
                                                        <input type="text" class="form-control mb-2" style="position: relative; top: -10px;" name="url" placeholder="リンク先となる記事のURLを入力しましょう" value="{{ $item->url }}">
                                                    </div>
                                                    <div class="col-sm-1">
                                                        <label for="post" class="form-label-sm text-muted" style="position: relative; top: 5px;">Post</label>
                                                    </div>
                                                    <div class="col-sm-11">
                                                        <input type="text" class="form-control mb-2" name="post"  placeholder="{{ $userPost ? $userPost : 'コメントを入力できます' }}" value="{{ $userPost }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- ブログカード表示 -->
                                            <div class="iframely-embed">
                                                <div class="iframely-responsive" style="height: 170px; padding-bottom: 0;">
                                                    <a href="{{ $item->url }}" data-iframely-url="//cdn.iframe.ly/api/iframe?url={{ $item->url }}&media=0&api_key={{ config('iframely.api.key') }}"></a>
                                                </div>
                                            </div>

                                            <!-- 編集・削除ボタン -->
                                            <div class="d-flex" style="height: 2.4em;">
                                                <div class="edit-form m-2 mr-4">
                                                        <input type="hidden" name="id" value="{{ $item->id }}">
                                                        <p id="editBtn_{{ $item->id }}" class="btn btn-primary rounded-pill" onclick="editItem('{{ $item->id }}');" style="background-color: #00abae; border: 0; padding: 7px 30px;">編集</p>
                                                        <button id="editSubmitBtn_{{ $item->id }}" class="btn btn-primary rounded-pill" style="display: none; background-color: #00abae; border: 0; padding: 7px 30px;">更新</button>
                                                    </form>
                                                </div>
                                                @if(auth()->id() == $item->user_id)
                                                    <div class="delete-form m-2 ml-4">
                                                        <form method="POST" action="{{ url('items/delete') }}">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $item->id }}">
                                                            <button type="submit" class="btn btn-danger rounded-pill" style="padding: 7px 30px;">削除</button>
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
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .urls, .posts {
            margin-left: 16px;
        }

        .contents {
            position: relative;
            padding-left: 10px;
        }

        .contents::before {
            content: '';
            position: absolute;
            top: -5px;
            left: 0;
            width: 3px;
            height: 20px;
            background-color: rgba(0, 171, 174, 0.8);
        }

        .contents::after {
            content: '';
            position: absolute;
            top: -5px;
            left: 0;
            width: 20px;
            height: 3px;
            background-color: rgba(0, 171, 174, 0.8);
        }

        .modal-dialog {
            max-width: 800px;
            max-height: 400px;
        }
    </style>
@stop

@section('js')
    <script>
        // セッションに 'add' が設定されている場合のみ、モーダルを開く
        @if(session('add'))
            $(document).ready(function() {
                $('#urlModalAdd').modal('show');
            });
        @endif

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

            // 入力フィールドを非表示にする
            document.getElementById('editBox_' + itemId).style.display = 'none';

            // 編集ボタンを元に戻す
            document.getElementById('editBtn_' + itemId).style.display = 'block';
            document.getElementById('editSubmitBtn_' + itemId).style.display = 'none';
        });

        // 編集ボタンがクリックされたときの処理
        function editItem(itemId) {
            // テキストを非表示にする
            var titleElement = document.getElementById('urlModalLabel' + itemId).style.display = 'none';
            var urlElement = document.getElementById('itemUrl_' + itemId).style.display = 'none';
            var postElement = document.getElementById('itemPost_' + itemId).style.display = 'none';

            // 入力フィールドを表示する
            document.getElementById('editBox_' + itemId).style.display = 'block';

            // 編集ボタンを更新ボタンにする
            document.getElementById('editBtn_' + itemId).style.display = 'none';
            document.getElementById('editSubmitBtn_' + itemId).style.display = 'block';
        }
    </script>
@stop
