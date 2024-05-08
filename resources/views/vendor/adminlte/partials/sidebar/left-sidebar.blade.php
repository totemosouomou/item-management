<aside class="main-sidebar {{ config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4') }}">

    {{-- Sidebar brand logo --}}
    @if(config('adminlte.logo_img_xl'))
        @include('adminlte::partials.common.brand-logo-xl')
    @else
        @include('adminlte::partials.common.brand-logo-xs')
    @endif

    {{-- Sidebar menu --}}
    <div class="sidebar" style="background: url(https://www.techis-learning.jp/images/back-min.png) no-repeat center/cover;">
        <nav class="pt-2">
            <ul class="nav nav-pills nav-sidebar flex-column {{ config('adminlte.classes_sidebar_nav', '') }}"
                data-widget="treeview" role="menu"
                @if(config('adminlte.sidebar_nav_animation_speed') != 300)
                    data-animation-speed="{{ config('adminlte.sidebar_nav_animation_speed') }}"
                @endif
                @if(!config('adminlte.sidebar_nav_accordion'))
                    data-accordion="false"
                @endif>
                {{-- Configured sidebar links --}}
                @each('adminlte::partials.sidebar.menu-item', $adminlte->menu('sidebar'), 'item')
            </ul>
        </nav>

        <!-- 学習進捗の円グラフ -->
        <div class="sidebar-image" style="padding-top: 20px; margin: 0 auto; width: 80%;">
            <canvas id="myChartPie"></canvas>
            <div id="centerText" style="position: relative; top: -95px; left: 140px; transform: translate(-50%, -50%); font-size: 48px; font-weight: bold; color: #00abae;"></div>
            <p class="my-2" style="position: relative; top: -70px; text-align: center; font-size: 0.75em;">只今の進捗率</p>
            <p style="position: relative; top: -70px;font-size: 0.75em;">
                基礎課題(<?php $fundamental = 31; $fundamentalScore = mt_rand(15, $fundamental); echo $fundamentalScore; ?>/<?php echo $fundamental; ?>)　
                応用課題(<?php $applied = 25; $appliedScore = $fundamentalScore < 20 ? 0 : mt_rand(0, $applied); echo $appliedScore; ?>/<?php echo $applied; ?>)

        </div>
    </div>

</aside>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('myChartPie').getContext('2d');
        const finaldata = [<?php $progress_rate = ($fundamentalScore + $appliedScore) / ($fundamental + $applied) * 100; echo $progress_rate; ?>, <?php echo 100 - $progress_rate; ?>];
        const total = finaldata.reduce((acc, val) => acc + val, 0);

        const myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: finaldata,
                    backgroundColor: [
                        'rgba(0, 171, 174, 0.9)',
                        'rgba(230, 230, 230, 1)',
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '65%',
                plugins: {
                    legend: {
                        display: false
                    }
                },
            }
        });

        const centerText = document.getElementById('centerText');
        centerText.innerText = `${Math.round((finaldata[0] / total) * 100)}%`;
    });
</script>
