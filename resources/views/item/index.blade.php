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
                        <figure class="m-3 contents" data-toggle="modal" data-target="#urlModal{{ $item->id }}" data-item-url="{{ $item->url }}">
                            <figcaption class="text-dark font-weight-bold">{{ $item->name }}</figcaption>
                            <p>{{ $item->url }}</p>
                        </figure>

                            <!-- URL表示用のモーダル -->
                            <div class="modal fade" id="urlModal{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="urlModalLabel{{ $item->id }}" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="urlModalLabel{{ $item->id }}">URL</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- 削除フォーム -->
                                            <form method="post" action="{{ url('items/delete') }}">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $item->id }}">
                                                <p>削除しますか？</p>
                                                <button type="submit" class="btn btn-danger">削除</button>
                                            </form>
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
    </style>
@stop

@section('js')
    <script>
        // APIキーをセキュアに保持する
        var api_key = 'f8a855acb80aba3b141b3c'; // あなたのAPIキーをここに入力

        // モーダルが表示される度に呼び出し元のitems->urlを取得して表示
        $('.modal').on('shown.bs.modal', function (e) {
            var modal = $(this);
            var url = $(e.relatedTarget).data('item-url'); // モーダルを呼び出した要素からitemsのIDを取得

            // APIキーを含めない形でoEmbed APIにアクセスし、データを取得する
            $.ajax({
                url: `https://iframe.ly/api/oembed?url=${encodeURIComponent(url)}&api_key=${api_key}`,
                success: function (data) {
                    // oEmbedから取得した情報をモーダル内に表示する
                    var modalContent = `
                        <h5>${data.title}</h5>
                        <p>${data.description}</p>
                        ${data.html}
                    `;
                    $('#modalContent').html(modalContent);
                },
                error: function(xhr, status, error) {
                    // エラーハンドリングを行う場合はここに記述する
                    console.error(error);
                }
            });
        });
    </script>
@stop

