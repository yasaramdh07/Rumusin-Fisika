@if ($paginator->hasPages())
    <nav class="rf-pagination" aria-label="Navigasi halaman">
        @if ($paginator->onFirstPage())
            <span class="rf-btn rf-btn--tertiary" aria-disabled="true">Sebelumnya</span>
        @else
            <a class="rf-btn rf-btn--tertiary" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>
        @endif

        <span class="rf-pagination__info rf-num">
            Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}
        </span>

        @if ($paginator->hasMorePages())
            <a class="rf-btn rf-btn--tertiary" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
        @else
            <span class="rf-btn rf-btn--tertiary" aria-disabled="true">Berikutnya</span>
        @endif
    </nav>
@endif
