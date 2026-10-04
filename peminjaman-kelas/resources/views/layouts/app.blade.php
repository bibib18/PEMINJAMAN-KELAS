<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Peminjaman Kelas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
@php
    $me = auth()->user();
    $pendingCount = $me->isAdmin() ? \App\Models\Booking::where('status', 'pending')->count() : 0;

    $nav = [
        ['dashboard', 'Dashboard', 'dashboard', 'home'],
        ['schedule.index', 'Jadwal ruang', 'jadwal*', 'calendar'],
        ['rooms.index', 'Ruang kelas', 'ruang*', 'building'],
        ['bookings.index', $me->isAdmin() ? 'Semua peminjaman' : 'Peminjaman saya', 'peminjaman*', 'clipboard'],
    ];
@endphp

<div class="min-h-screen lg:grid lg:grid-cols-[15rem_minmax(0,1fr)]">
    <div id="sidebar-backdrop" class="sidebar-backdrop" hidden></div>

    <aside id="sidebar" class="sidebar" aria-label="Navigasi utama">
        <div class="flex items-center gap-3 border-b border-line px-4 py-4">
            <span class="brand-mark">PK</span>
            <span class="leading-tight">
                <span class="block text-sm font-bold">Peminjaman Kelas</span>
                <span class="block text-xs text-muted">{{ $me->isAdmin() ? 'Panel admin' : 'Portal kampus' }}</span>
            </span>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto p-3">
            @foreach ($nav as [$route, $label, $pattern, $icon])
                <a href="{{ route($route) }}" class="nav-item" @if (request()->is($pattern)) aria-current="page" @endif>
                    <x-icon :name="$icon" /> {{ $label }}
                </a>
            @endforeach

            @if ($me->isAdmin())
                <a href="{{ route('approvals.index') }}" class="nav-item" @if (request()->is('persetujuan*')) aria-current="page" @endif>
                    <x-icon name="check-circle" /> Persetujuan
                    @if ($pendingCount)<span class="nav-count" aria-label="{{ $pendingCount }} menunggu">{{ $pendingCount }}</span>@endif
                </a>
            @endif
        </nav>

        <div class="border-t border-line p-3">
            <div class="px-2 pb-3">
                <div class="truncate text-sm font-semibold">{{ $me->name }}</div>
                <div class="mono truncate text-muted">{{ $me->isAdmin() ? 'Admin' : $me->nim_nip }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item w-full cursor-pointer"><x-icon name="logout" /> Keluar</button>
            </form>
        </div>
    </aside>

    <div class="min-w-0">
        <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-line bg-surface px-4 py-3 lg:hidden">
            <button type="button" id="sidebar-open" class="btn btn-secondary btn-sm" aria-controls="sidebar" aria-expanded="false" aria-label="Buka menu"><x-icon name="menu" /></button>
            <span class="brand-mark">PK</span>
            <span class="text-sm font-bold">Peminjaman Kelas</span>
        </header>

        <main class="mx-auto w-full max-w-5xl px-4 py-6 sm:px-8 sm:py-10">
            @if (session('success'))
                <div class="alert alert-success mb-6" role="status"><x-icon name="check" class="mt-0.5" /><span>{{ session('success') }}</span></div>
            @endif
            @if (session('error'))
                <div class="alert alert-error mb-6" role="alert"><x-icon name="alert" class="mt-0.5" /><span>{{ session('error') }}</span></div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<dialog id="confirm-dialog" class="dlg" aria-labelledby="confirm-title">
    <form method="dialog">
        <h2 id="confirm-title" class="section-title">Konfirmasi tindakan</h2>
        <p id="confirm-text" class="mt-2 text-sm text-muted"></p>
        <div class="mt-5 flex justify-end gap-2">
            <button type="submit" value="cancel" class="btn btn-secondary">Batal</button>
            <button type="submit" value="ok" id="confirm-ok" class="btn btn-primary">Lanjutkan</button>
        </div>
    </form>
</dialog>

<script>
(function () {
    // --- Sidebar mobile ---
    var sidebar = document.getElementById('sidebar');
    var backdrop = document.getElementById('sidebar-backdrop');
    var openBtn = document.getElementById('sidebar-open');
    function setSidebar(open) {
        sidebar.classList.toggle('open', open);
        backdrop.hidden = !open;
        if (openBtn) openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) { var first = sidebar.querySelector('a, button'); if (first) first.focus(); }
        else if (openBtn && document.activeElement && sidebar.contains(document.activeElement)) openBtn.focus();
    }
    if (openBtn) openBtn.addEventListener('click', function () { setSidebar(true); });
    backdrop.addEventListener('click', function () { setSidebar(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setSidebar(false); });

    // --- Dialog konfirmasi: <form data-confirm="..." data-confirm-label="..." data-confirm-tone="danger"> ---
    var dlg = document.getElementById('confirm-dialog');
    var okBtn = document.getElementById('confirm-ok');
    var pending = null;

    function lock(form) {
        setTimeout(function () {
            form.querySelectorAll('button[type="submit"]').forEach(function (b) {
                b.dataset.label = b.textContent;
                b.disabled = true;
                b.textContent = 'Memproses…';
            });
        }, 0);
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (form === dlg.querySelector('form')) return;
        if (form.dataset.confirmed || !form.dataset.confirm) { lock(form); return; }

        e.preventDefault();
        pending = form;
        document.getElementById('confirm-text').textContent = form.dataset.confirm;
        okBtn.textContent = form.dataset.confirmLabel || 'Lanjutkan';
        okBtn.className = 'btn ' + (form.dataset.confirmTone === 'danger' ? 'btn-danger' : 'btn-primary');
        dlg.showModal();
    });

    dlg.addEventListener('close', function () {
        var ok = dlg.returnValue === 'ok' && pending;
        var form = pending;
        pending = null;
        dlg.returnValue = '';
        if (ok) {
            form.dataset.confirmed = '1';
            form.requestSubmit();
        }
    });

    // Setelah tombol "kembali" di browser, kembalikan tombol yang sempat dikunci.
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        document.querySelectorAll('button[type="submit"][data-label]').forEach(function (b) {
            b.disabled = false;
            b.textContent = b.dataset.label;
        });
        document.querySelectorAll('form[data-confirmed]').forEach(function (f) { delete f.dataset.confirmed; });
    });
})();
</script>
@stack('scripts')
</body>
</html>
