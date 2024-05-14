<li class="nav-item">

    {{-- Search toggle button --}}
    <a class="nav-link" data-widget="navbar-search" href="#" role="button" onclick="adjustNavbarSearchBlockWidth();">
        <i class="fas fa-search"></i>
    </a>

    {{-- Search bar --}}
    <div class="navbar-search-block">
        <form class="form-inline" action="" method="post">
            {{ csrf_field() }}

            <div class="input-group">

                {{-- Search input --}}
                <input class="form-control form-control-navbar" type="search"
                    @isset($item['id']) id="{{ $item['id'] }}" @endisset
                    name="{{ $item['text'] }}"
                    placeholder="{{ $item['text'] }}"
                    aria-label="{{ $item['text'] }}">
                <input type="hidden" name="page" value="">

                {{-- Search buttons --}}
                <div class="input-group-append">
                    <button class="btn btn-navbar" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

            </div>
        </form>
    </div>

</li>

<script>
    function adjustNavbarSearchBlockWidth() {
        const navUserNameWidth = document.querySelector('.user-menu a').offsetWidth;
        setTimeout(function() {
            const navbarSearchBlock = document.querySelector('.navbar-search-block.navbar-search-open');
            navbarSearchBlock.style.width = `calc(100% - ${navUserNameWidth}px)`;
        }, 100);
    }
</script>
