@extends('adminlte::page')

@section('title', '記事登録')

@section('content_header')
    <h1>記事登録</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-10">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card card-primary">
            <div class="card-header" id="accordionHeader">
                <h3 class="card-title">記事のURL・見出しの入力は必須項目です</h3>
                <div class="card-tools">
                    <button class="btn btn-tool" data-toggle="collapse" data-target="#accordionContent" aria-expanded="false" aria-controls="accordionContent">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <form method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group row">
                            <label for="url" class="col-sm-2 col-form-label">URL</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="url" name="url" placeholder="URL" value="{{ old('url') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="name" class="col-sm-2 col-form-label">見出し</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="name" name="name" placeholder="見出し" value="{{ old('name') }}">
                            </div>
                        </div>
                    </div>
                    <div id="accordionContent" class="collapse" aria-labelledby="accordionHeader">
                        <div class="card-body pt-0">
                            <div class="form-group row">
                                <label for="detail" class="col-sm-2 col-form-label">詳細</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="detail" name="detail" placeholder="詳細" value="{{ old('detail') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">登録</button>
                    </div>
            </form>

        </div>
    </div>
</div>

@stop

@section('css')
@stop

@section('js')
@stop
