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
                            <figure class="m-3" data-toggle="modal" data-target="#urlModal{{ $item->id }}">
                                <figcaption class="text-dark font-weight-bold">{{ $item->name }}</figcaption>
                                <p>{{ $item->url }}</p>
                                <form method="post" action="{{ url('items/delete') }}" onsubmit="return confirm('削除します。よろしいでしょうか？');">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                    <input type="submit" value="削除" class="btn btn-danger">
                                </form>
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
                                            <div id="urlContent{{ $item->id }}"></div>
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
@stop

@section('js')
    <script>
        // モーダルが表示される度にAjaxで外部コンテンツを取得
        $('.modal').on('shown.bs.modal', function (e) {
            var modal = $(this);
            var id = modal.attr('id').replace('urlModal', '');
            var url = '{{ $item->url }}';
            $.ajax({
                url: url,
                success: function (data) {
                    $('#urlContent' + id).html(data); // 取得したデータをモーダル内に表示
                }
            });
        });
    </script>
@stop
