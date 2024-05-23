@extends('adminlte::page')

@section('title', '記事管理：Bookmark')

@section('content_header')
    <h1>投稿＆ブックマーク</h1>
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
                    <a class="card bookmarks" href="{{ $item->url }}">
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
                                    <img src="{{ asset('storage/bookmarks/' . $item->id . '.png') }}" alt="Thumbnail" class="img-fluid" onmouseover="openImageInNewTab('{{ asset('storage/bookmarks/' . $item->id . '.png') }}')">
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
        function openImageInNewTab(url) {
            window.open(url, '_blank');
        }
    </script>
@stop
