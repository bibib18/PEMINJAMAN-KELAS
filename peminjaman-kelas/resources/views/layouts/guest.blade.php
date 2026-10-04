<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') · Peminjaman Kelas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
<div class="mx-auto flex min-h-screen w-full max-w-md flex-col justify-center px-4 py-10">
    <div class="mb-8 flex items-center gap-3">
        <span class="brand-mark">PK</span>
        <div class="leading-tight">
            <div class="text-sm font-bold">Sistem Peminjaman Kelas</div>
            <div class="text-xs text-muted">Portal akademik kampus</div>
        </div>
    </div>

    <div class="panel panel-pad !p-6 sm:!p-8">
        @yield('content')
    </div>

    <p class="mt-6 text-center text-xs text-muted">Kendala akses? Hubungi bagian akademik atau admin kampus.</p>
</div>
</body>
</html>
