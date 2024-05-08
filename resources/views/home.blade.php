@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
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
                    <p class="card-title" style="font-size: 1rem;">登録数ランキング上位者</p>
                    <div class="card-tools">
                        <button class="add-btn" style="font-weight: bold;" onclick="location.href='{{ url('items/add') }}';">
                            記事登録
                        </button>
                    </div>
                    <div class="d-flex justify-content-between" style="width: 100%;">
                        <figure class="myChart-responsive" style="width: 70%;">
                            <canvas id="myChart"></canvas>
                        </figure>
                        @if($randomItem)
                            <div class="iframely-embed iframely-embed-responsive" style="position: relative; top: 10px; width: calc(30% - 10px);">
                                <div class="iframely-responsive">
                                    <a href="{{ $randomItem->url }}" data-iframely-url="//cdn.iframe.ly/api/iframe?url={{ $randomItem->url }}&media=0&api_key={{ config('iframely.api.key') }}"></a>
                                </div>
                                <div class="form-group" style="position: relative; top: 145px;">
                                    <form method="POST" action="{{ url('items/update') }}">
                                        @csrf
                                        @php
                                            $userPost = $randomItem->posts->where('user_id', auth()->id())->first();
                                            $userPostComment = $userPost ? str_replace(" by " . Auth::user()->name, "", $userPost->post) : '';
                                        @endphp
                                        <input type="hidden" name="title" value="{{ $randomItem->title }}">
                                        <input type="hidden" name="url" value="{{ $randomItem->url }}">
                                        <input type="hidden" name="id" value="{{ $randomItem->id }}">
                                        <input type="text" class="form-control" id="post" name="post" placeholder="{{ $userPost ? $userPostComment : '記事へコメントしましょう！' }}" value="{{ $userPost ? $userPostComment : '' }}">
                                        <button id="submit-button" class="add-btn mt-2" style="display: none; font-weight: bold;">Submit</button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <p>記事へコメントする私は偉い！</p>
                    <ul class="posts mx-3 mt-1 mb-0 list-unstyled list-inline">
                        @foreach ($posts as $post)
                            <li class="list-inline-item mb-2" style="background-color: rgba(250, 250, 250, 0.5); border-radius: 10px; padding: 1px 20px; font-size: 0.8em; color: rgba(33, 37, 41, 0.8);">
                                {{ $post->post }}
                            </li>
                        @endforeach
                    </ul>
                </div>
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
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('myChart').getContext('2d');
            const itemsPerUser = @json($itemsPerUser);
            const sortedItems = itemsPerUser.sort((a, b) => b.total - a.total);
            const labels = sortedItems.map(item => item.user.name);
            const data = sortedItems.map(item => item.total);
            const urls = sortedItems.map(item => `/items/user/${item.user.id}`);

            // 上位5ユーザーとその他のデータを分ける
            const topFiveLabels = labels.slice(0, 5);
            const topFiveData = data.slice(0, 5);
            const othersData = data.slice(5).reduce((acc, cur) => acc + cur, 0);
            const topFiveUrls = urls.slice(0, 5);

            const finalLabels = [...topFiveLabels, 'その他'];
            const finalData = [...topFiveData, othersData];

            const myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: finalLabels,
                    datasets: [{
                        label: '記事点数 / 1アカウントあたり',
                        data: finalData,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    onClick: function(e) {
                        const activePoints = myChart.getElementsAtEventForMode(e, 'nearest', { intersect: true }, true);
                        if (activePoints.length > 0) {
                            const index = activePoints[0].index;
                            const url = index < topFiveUrls.length ? topFiveUrls[index] : null;
                            if (url) {
                                window.location.href = url;
                            }
                        }
                    }
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('post');
            const submitButton = document.getElementById('submit-button');

            // input要素にフォーカスが当たったときのイベントリスナーを追加
            input.addEventListener('focus', function() {
                // submitボタンを表示する
                submitButton.style.display = 'block';
            });

            // submitボタンにマウスが乗ったときのイベントリスナーを追加
            submitButton.addEventListener('mouseenter', function() {
                // input要素がフォーカスされているかどうかをチェック
                if (!input.matches(':focus')) {
                    // input要素がフォーカスされていない場合、submitボタンを非表示にする
                    submitButton.style.display = 'none';
                }
            });

            // submitボタンをクリックしたときのイベントリスナーを追加
            submitButton.addEventListener('click', function() {
                // ここにsubmitの処理を追加する（例えば、フォームの送信など）
                // ここでは単純にsubmitがクリックされたことをログに出力する
                console.log('Submit button clicked');
            });
        });
    </script>
@stop
