@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-wrap items-center justify-between gap-3 text-sm">
        <p class="text-muted">
            Menampilkan <span class="mono text-ink">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
            dari <span class="mono text-ink">{{ $paginator->total() }}</span>
        </p>

        <div class="flex items-center gap-2">
            @if ($paginator->onFirstPage())
                <span class="btn btn-secondary btn-sm" aria-disabled="true" style="opacity:.5;cursor:not-allowed">Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-secondary btn-sm">Sebelumnya</a>
            @endif

            <span class="mono px-1 text-muted" aria-current="page">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-secondary btn-sm">Berikutnya</a>
            @else
                <span class="btn btn-secondary btn-sm" aria-disabled="true" style="opacity:.5;cursor:not-allowed">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
