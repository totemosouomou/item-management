@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <p>登録記事点数の多い順ランキング上位者</p>
    <!-- resources/views/home.blade.php -->
    <div class="mb-5">
        <canvas id="myChart" height="100%"></canvas>
    </div>

    <p class="mt-5">各記事へ詳細情報を入力した　私は偉い！</p>
    <ul class="details ms-5 mt-1 mb-0 list-unstyled list-inline">
        @foreach ($details as $detail)
            <li class="list-inline-item" style="background-color: rgba(240, 240, 240, 0.6); border-radius: 100000px; padding: 1px 20px; font-size: 0.8em; color: rgba(0, 0, 0, 0.6);">
                {{ $detail->detail }}
            </li>
        @endforeach
    </ul>
@stop

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
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
    </script>
@stop
