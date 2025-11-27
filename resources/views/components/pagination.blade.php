@props(['paginator', 'showSummary' => true])

@php
    $isLengthAware = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    $showNavigation = $isLengthAware && $paginator->hasPages();
    $window = $showNavigation ? \Illuminate\Pagination\UrlWindow::make($paginator) : [];
    $elements = $showNavigation ? array_filter([
        $window['first'] ?? null,
        is_array($window['slider'] ?? null) ? '...' : null,
        $window['slider'] ?? null,
        is_array($window['last'] ?? null) ? '...' : null,
        $window['last'] ?? null,
    ]) : [];
    $from = $paginator->firstItem() ?? ($paginator->count() ? 1 : 0);
    $to = $paginator->lastItem() ?? ($paginator->count() ? $paginator->count() : 0);
@endphp

@if ($isLengthAware)
<div class="row g-0 text-center text-sm-start align-items-center mb-4">
    @if ($showSummary)
    @php($summaryCol = $showNavigation ? 'col-sm-6 mb-2 mb-sm-0' : 'col-sm-12 mb-2 mb-sm-0')
    <div class="{{ $summaryCol }}">
        <div>
            <p class="mb-sm-0 text-muted">
                Showing <span class="fw-semibold">{{ $from }}</span>
                to <span class="fw-semibold">{{ $to }}</span>
                of <span class="fw-semibold">{{ $paginator->total() }}</span> entries
            </p>
        </div>
    </div>
    @endif

    @if ($showNavigation)
    @php($colClass = $showSummary ? 'col-sm-6' : 'col-sm-12')
    <div class="{{ $colClass }}">
        <ul class="pagination pagination-separated justify-content-center justify-content-sm-end mb-sm-0">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">Previous</span>
                </li>
            @else
                <li class="page-item">
                    <button type="button" class="page-link" wire:click="previousPage" wire:loading.attr="disabled"
                        rel="prev">Previous</button>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled">
                        <span class="page-link">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <button type="button" class="page-link" wire:click="gotoPage({{ $page }})"
                                    wire:loading.attr="disabled">{{ $page }}</button>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <button type="button" class="page-link" wire:click="nextPage" wire:loading.attr="disabled"
                        rel="next">Next</button>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">Next</span>
                </li>
            @endif
        </ul>
    </div>
    <!-- end col -->
    @endif
</div>
@endif