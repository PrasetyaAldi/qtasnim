<nav class="navbar navbar-expand-lg bg-white mb-4">
    <div class="container">
        <a class="navbar-brand fs-4 fw-bold" href="#">
            <img width="100" height="27.6" src="https://qtasnim.com/wp-content/uploads/2023/12/logo-qtasnim.png"
                class="custom-logo" alt="logo qtasnim oranye" decoding="async" fetchpriority="high">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-center" id="navbarSupportedContent">
            <ul class="navbar-nav mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link fw-medium {{ !Request::is('transactions/compare') && (Request::is('transactions/*') || Request::is('transactions')) ? 'active' : '' }}"
                        href="{{ route('transactions.index') }}">Transaksi</a>
                </li>

                <li class="nav-item dropdown fw-medium">
                    <a class="nav-link dropdown-toggle {{ !Request::is('transactions/*') && !Request::is('transactions') ? 'text-black' : '' }}"
                        href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Referensi
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item fw-medium {{ Request::is('products') || Request::is('products/*') ? 'active' : '' }}"
                                href="{{ route('products.index') }}">Barang</a></li>
                        <li><a class="dropdown-item fw-medium {{ Request::is('stocks') || Request::is('stocks/*') ? 'active' : '' }}"
                                href="{{ route('stocks.index') }}">Stock</a>
                        </li>
                        <li><a class="dropdown-item fw-medium {{ Request::is('product-types') || Request::is('product-types/*') ? 'active' : '' }}"
                                href="{{ route('product-types.index') }}">Jenis Barang</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium {{ Request::is('transactions/compare') ? 'active' : '' }}"
                        href="{{ route('transactions.compare') }}">Perbandingan</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
