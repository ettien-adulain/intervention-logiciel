@if ($paginator->hasPages())
    <nav style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; font-size: 0.875rem;">
        @if ($paginator->onFirstPage())
            <span style="opacity: 0.5;">&laquo; Précédent</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" style="color: var(--ycs-red, #c41e3a);">&laquo; Précédent</a>
        @endif
        <span style="color: #64748b;">Page {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" style="color: var(--ycs-red, #c41e3a);">Suivant &raquo;</a>
        @else
            <span style="opacity: 0.5;">Suivant &raquo;</span>
        @endif
    </nav>
@endif
