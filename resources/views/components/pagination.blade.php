@props(['paginator', 'showSummary' => true])

@php
    $isLengthAware = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    $showNavigation = $isLengthAware && $paginator->hasPages();
    $pageName = $isLengthAware && method_exists($paginator, 'getPageName') ? $paginator->getPageName() : 'page';

    // Limit visible page digits for responsive UI.
    $maxLinksDesktop = 6;
    $maxLinksMobile = 3;
    $currentPage = $isLengthAware ? $paginator->currentPage() : 1;
    $lastPage = $isLengthAware ? $paginator->lastPage() : 1;

    // Desktop (sm+): max 6 page numbers, with first/last and ellipsis.
    $start = 1;
    $end = $lastPage;
    if ($showNavigation) {
        $half = intdiv($maxLinksDesktop, 2);
        $start = max(1, $currentPage - $half);
        $end = min($lastPage, $start + $maxLinksDesktop - 1);
        $start = max(1, $end - $maxLinksDesktop + 1);
    }

    $showFirst = $showNavigation && $start > 1;
    $showLast = $showNavigation && $end < $lastPage;
    $showLeadingEllipsis = $showNavigation && $start > 2;
    $showTrailingEllipsis = $showNavigation && $end < ($lastPage - 1);

    // Mobile (xs): max 3 page numbers, with ellipsis only (no first/last buttons to keep count <= 3).
    $mStart = 1;
    $mEnd = $lastPage;
    if ($showNavigation) {
        $mHalf = intdiv($maxLinksMobile, 2); // 1
        $mStart = max(1, $currentPage - $mHalf);
        $mEnd = min($lastPage, $mStart + $maxLinksMobile - 1);
        $mStart = max(1, $mEnd - $maxLinksMobile + 1);
    }
    $mShowLeadingEllipsis = $showNavigation && $mStart > 1;
    $mShowTrailingEllipsis = $showNavigation && $mEnd < $lastPage;

    $from = $paginator->firstItem() ?? ($paginator->count() ? 1 : 0);
    $to = $paginator->lastItem() ?? ($paginator->count() ? $paginator->count() : 0);
    $total = $paginator->total();
@endphp

@if ($isLengthAware)
<div class="row g-0 text-center text-sm-start align-items-center mb-4">
    @if ($showSummary)
    @php($summaryCol = $showNavigation ? 'col-sm-6 mb-2 mb-sm-0' : 'col-sm-12 mb-2 mb-sm-0')
    <div class="{{ $summaryCol }}">
        <div>
            {{-- Desktop summary (full text) --}}
            <p class="mb-sm-0 text-muted d-none d-sm-block">
                Showing <span class="fw-semibold">{{ number_format($from) }}</span>
                to <span class="fw-semibold">{{ number_format($to) }}</span>
                of <span class="fw-semibold">{{ number_format($total) }}</span> entries
            </p>

            {{-- Mobile summary (compact to avoid overflow) --}}
            <p class="mb-0 text-muted d-sm-none small">
                <span class="fw-semibold">{{ number_format($from) }}</span>-<span class="fw-semibold">{{ number_format($to) }}</span>
                / <span class="fw-semibold">{{ number_format($total) }}</span>
            </p>
        </div>
    </div>
    @endif

    @if ($showNavigation)
    @php($colClass = $showSummary ? 'col-sm-6' : 'col-sm-12')
    <div class="{{ $colClass }}">
        <ul class="pagination pagination-separated justify-content-center justify-content-sm-end mb-sm-0 flex-wrap gap-1">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link px-2 px-sm-3">
                        <i class="ri-arrow-left-s-line" aria-hidden="true"></i>
                        <span class="visually-hidden">Previous</span>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <button type="button" class="page-link px-2 px-sm-3" wire:click="previousPage('{{ $pageName }}')" wire:loading.attr="disabled"
                        rel="prev" aria-label="Previous page">
                        <i class="ri-arrow-left-s-line" aria-hidden="true"></i>
                        <span class="visually-hidden">Previous</span>
                    </button>
                </li>
            @endif

            {{-- Mobile page digits (max 3) --}}
            @if ($mShowLeadingEllipsis)
                <li class="page-item disabled d-sm-none">
                    <span class="page-link px-2">...</span>
                </li>
            @endif

            @for ($page = $mStart; $page <= $mEnd; $page++)
                <li class="page-item d-sm-none {{ $page === $currentPage ? 'active' : '' }}">
                    @if ($page === $currentPage)
                        <span class="page-link px-2">{{ $page }}</span>
                    @else
                        <button type="button" class="page-link px-2" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')" wire:loading.attr="disabled">{{ $page }}</button>
                    @endif
                </li>
            @endfor

            @if ($mShowTrailingEllipsis)
                <li class="page-item disabled d-sm-none">
                    <span class="page-link px-2">...</span>
                </li>
            @endif

            {{-- Desktop page digits (max 6) --}}
            @if ($showFirst)
                <li class="page-item d-none d-sm-inline-block {{ $currentPage === 1 ? 'active' : '' }}">
                    @if ($currentPage === 1)
                        <span class="page-link">1</span>
                    @else
                        <button type="button" class="page-link" wire:click="gotoPage(1, '{{ $pageName }}')" wire:loading.attr="disabled">1</button>
                    @endif
                </li>
            @endif

            @if ($showLeadingEllipsis)
                <li class="page-item disabled d-none d-sm-inline-block">
                    <span class="page-link">...</span>
                </li>
            @endif

            @for ($page = $start; $page <= $end; $page++)
                <li class="page-item d-none d-sm-inline-block {{ $page === $currentPage ? 'active' : '' }}">
                    @if ($page === $currentPage)
                        <span class="page-link">{{ $page }}</span>
                    @else
                        <button type="button" class="page-link" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')" wire:loading.attr="disabled">{{ $page }}</button>
                    @endif
                </li>
            @endfor

            @if ($showTrailingEllipsis)
                <li class="page-item disabled d-none d-sm-inline-block">
                    <span class="page-link">...</span>
                </li>
            @endif

            @if ($showLast)
                <li class="page-item d-none d-sm-inline-block {{ $currentPage === $lastPage ? 'active' : '' }}">
                    @if ($currentPage === $lastPage)
                        <span class="page-link">{{ $lastPage }}</span>
                    @else
                        <button type="button" class="page-link" wire:click="gotoPage({{ $lastPage }}, '{{ $pageName }}')" wire:loading.attr="disabled">{{ $lastPage }}</button>
                    @endif
                </li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <button type="button" class="page-link px-2 px-sm-3" wire:click="nextPage('{{ $pageName }}')" wire:loading.attr="disabled"
                        rel="next" aria-label="Next page">
                        <i class="ri-arrow-right-s-line" aria-hidden="true"></i>
                        <span class="visually-hidden">Next</span>
                    </button>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link px-2 px-sm-3">
                        <i class="ri-arrow-right-s-line" aria-hidden="true"></i>
                        <span class="visually-hidden">Next</span>
                    </span>
                </li>
            @endif
        </ul>
    </div>
    <!-- end col -->
    @endif
</div>
@endif