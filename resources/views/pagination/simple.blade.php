@if ($paginator->hasPages())
    <nav class="pagination-nav" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="disabled">&laquo; Précédent</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}">&laquo; Précédent</a>
        @endif
        <span class="muted">Page {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}">Suivant &raquo;</a>
        @else
            <span class="disabled">Suivant &raquo;</span>
        @endif
    </nav>
@endif
