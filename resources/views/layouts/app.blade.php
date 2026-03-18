<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Kendaraan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="hold-transition sidebar-mini layout-fixed modern-ui">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item d-flex align-items-center mr-3 text-sm text-muted">
                {{ auth()->user()->nama }} ({{ auth()->user()->role }})
            </li>
            <li class="nav-item">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
                </form>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <span class="brand-text font-weight-light ml-2">Booking Vehicle</span>
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    @if(auth()->user()->role === 'admin')
                        <li class="nav-item">
                            <a href="{{ route('pemesanan.index') }}" class="nav-link {{ request()->routeIs('pemesanan.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-car"></i>
                                <p>Pemesanan</p>
                            </a>
                        </li>
                    @endif
                    @if(auth()->user()->role === 'penyetujui')
                        <li class="nav-item">
                            <a href="{{ route('persetujuan.index') }}" class="nav-link {{ request()->routeIs('persetujuan.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-check-circle"></i>
                                <p>
                                    Persetujuan
                                    @if(($approvalPendingCount ?? 0) > 0)
                                        <span class="right badge badge-warning">{{ $approvalPendingCount }}</span>
                                    @endif
                                </p>
                            </a>
                        </li>
                    @endif
                    @if(auth()->user()->role === 'admin')
                        <li class="nav-item {{ request()->routeIs('laporan.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>
                                    Laporan
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview report-submenu">
                                <li class="nav-item">
                                    <a href="{{ route('laporan.index') }}" class="nav-link {{ request()->routeIs('laporan.index') || request()->routeIs('laporan.export') ? 'active' : '' }}">
                                        <i class="fas fa-calendar-alt nav-icon"></i>
                                        <p>Periodik Pemesanan</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('laporan.log-persetujuan') }}" class="nav-link {{ request()->routeIs('laporan.log-persetujuan') ? 'active' : '' }}">
                                        <i class="fas fa-check-double nav-icon"></i>
                                        <p>Log Persetujuan</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('laporan.log-aktivitas') }}" class="nav-link {{ request()->routeIs('laporan.log-aktivitas') ? 'active' : '' }}">
                                        <i class="fas fa-history nav-icon"></i>
                                        <p>Log Aktivitas</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger shadow-sm">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger shadow-sm">
                        <ul class="mb-0 pl-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $('.js-search-select').each(function () {
        const $element = $(this);
        const ajaxUrl = $element.data('ajaxUrl');

        const config = {
            theme: 'bootstrap4',
            width: '100%',
            placeholder: $element.data('placeholder') || 'Ketik untuk mencari...',
            allowClear: true,
        };

        if (ajaxUrl) {
            config.minimumInputLength = 1;
            config.ajax = {
                url: ajaxUrl,
                dataType: 'json',
                delay: 250,
                cache: true,
                data: function (params) {
                    return {
                        q: params.term || '',
                        page: params.page || 1,
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results || [],
                        pagination: data.pagination || { more: false },
                    };
                },
            };
        }

        $element.select2(config);
    });

    document.querySelectorAll('.js-time-picker').forEach((element) => {
        flatpickr(element, {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true,
            minuteIncrement: 5,
            allowInput: false,
            clickOpens: true,
            disableMobile: true,
        });
    });

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-confirm]');

        if (!button) {
            return;
        }

        const form = button.closest('form');

        if (!form) {
            return;
        }

        event.preventDefault();

        const result = await Swal.fire({
            title: button.dataset.confirmTitle || 'Konfirmasi',
            text: button.dataset.confirm,
            icon: button.dataset.confirmIcon || 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, lanjutkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6b7280',
        });

        if (result.isConfirmed) {
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit(button);
                return;
            }

            form.submit();
        }
    });
</script>
</body>
</html>
