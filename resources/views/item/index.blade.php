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

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $user_name }}を表示中</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm">
                            <div class="input-group-append">
                                <a href="{{ url('items/add') }}" class="btn btn-default">記事登録</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap">
                        @foreach ($items as $item)
                        <figure class="m-3 contents" data-toggle="modal" data-target="#urlModal{{ $item->id }}">
                            <figcaption class="text-dark font-weight-bold">{{ $item->name }}</figcaption>
                                <p>{{ \Illuminate\Support\Str::limit($item->url, 45, $end='...') }}</p>
                        </figure>

                            <!-- URL表示用のモーダル -->
                            <div class="modal fade" id="urlModal{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="urlModalLabel{{ $item->id }}" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title text-dark font-weight-bold" id="urlModalLabel{{ $item->id }}">
                                                {{ $item->name }}
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <p class="urls ms-5 my-0">
                                            <a href="{{ $item->url }}" style="text-decoration: none;" id="itemUrl_{{ $item->id }}">
                                                {{ \Illuminate\Support\Str::limit($item->url, 80, $end='...') }}
                                            </a>
                                        </p>
                                        <ul class="posts ms-5 mt-1 mb-0 list-unstyled list-inline" id="itemPost_{{ $item->id }}">
                                            @php
                                                $userPost = null;
                                            @endphp
                                            @foreach ($item->posts as $post)
                                                @if ($post->user_id == Auth::user()->id)
                                                    @php
                                                        $userPost = str_replace(" by " . Auth::user()->name, "", $post->post);
                                                    @endphp
                                                @endif
                                                <li class="list-inline-item" style="background-color: rgba(240, 240, 240, 0.6); border-radius: 100000px; padding: 1px 20px; font-size: 0.8em; color: rgba(0, 0, 0, 0.6);">
                                                    {{ $post->post }}
                                                </li>
                                            @endforeach
                                        </ul>

                                        <div class="modal-body">

                                            <!-- 編集用テキストボックス -->
                                            <div id="editBox_{{ $item->id }}" style="display: none;">
                                                <form method="POST" action="{{ url('items/update') }}">
                                                @csrf
                                                <input type="text" class="form-control mb-2" id="name" name="name" placeholder="見出し" value="{{ $item->name }}">
                                                <input type="text" class="form-control mb-2" id="url" name="url" placeholder="URL" value="{{ $item->url }}">
                                                <input type="text" class="form-control mb-2" id="post" name="post"  placeholder="{{ $userPost ? $userPost : '詳細を入力できます' }}" value="{{ $userPost }}">
                                            </div>

                                            <!-- ブログカード表示 -->
                                            <div class="iframely-embed">
                                                <div class="iframely-responsive" style="height: 170px; padding-bottom: 0;">
                                                    <a href="{{ $item->url }}" data-iframely-url="//cdn.iframe.ly/api/iframe?url={{ $item->url }}&media=0&api_key={{ config('iframely.api.key') }}"></a>
                                                </div>
                                            </div>

                                            <!-- 編集・削除ボタン -->
                                            <div class="d-flex" style="height: 2.4em;">
                                                <div class="edit-form m-2 mr-5">
                                                        <input type="hidden" name="id" value="{{ $item->id }}">
                                                        <p id="editBtn_{{ $item->id }}" class="btn btn-primary rounded-pill" onclick="editItem('{{ $item->id }}');" style="padding: 7px 30px;">編集</p>
                                                        <button id="editSubmitBtn_{{ $item->id }}" class="btn btn-primary rounded-pill" style="display: none; padding: 7px 30px;">更新</button>
                                                    </form>
                                                </div>
                                                <div class="delete-form m-2 ms-5">
                                                    <form method="POST" action="{{ url('items/delete') }}">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{ $item->id }}">
                                                        <button type="submit" class="btn btn-danger rounded-pill" style="padding: 7px 30px;">削除</button>
                                                    </form>
                                                </div>
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
            background-color: rgba(144, 238, 144, 0.5);
        }

        .contents::after {
            content: '';
            position: absolute;
            top: -5px;
            left: 0;
            width: 20px;
            height: 3px;
            background-color: rgba(144, 238, 144, 0.5);
        }

        .modal-dialog {
            max-width: 800px;
            max-height: 400px;
        }
    </style>
@stop

@section('js')
<script>
    // モーダルが閉じたときのイベントを検知するためのスクリプト
    $('.modal').on('hidden.bs.modal', function (e) {
        var itemId = $(this).attr('id').replace('urlModal', '');

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
        var nameElement = document.getElementById('urlModalLabel' + itemId).style.display = 'none';
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