@if ($paginator->hasPages())
    <nav class="d-flex justify-items-center justify-content-between">
        <div class="d-flex justify-content-between flex-fill d-sm-none">
            <ul class="pagination pagination-sm mb-0">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">&laquo; Prev</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; Prev</a>
                    </li>
                @endif

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next &raquo;</a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">Next &raquo;</span>
                    </li>
                @endif
            </ul>
        </div>

        <div class="d-none d-sm-flex align-items-center justify-content-between flex-fill gap-3">
            <div>
                <p class="small text-muted mb-0" style="font-size: 13px;">
                    Showing
                    <span class="fw-semibold text-primary">{{ $paginator->firstItem() ?? 0 }}</span>
                    to
                    <span class="fw-semibold text-primary">{{ $paginator->lastItem() ?? 0 }}</span>
                    of
                    <span class="fw-semibold text-primary">{{ $paginator->total() }}</span>
                    results
                </p>
            </div>

            <div>
                <ul class="pagination pagination-sm mb-0 align-items-center gap-1">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                            <span class="page-link rounded-2" aria-hidden="true">
                                <i class="fa-solid fa-chevron-left" style="font-size: 11px;"></i>
                            </span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link rounded-2" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                                <i class="fa-solid fa-chevron-left" style="font-size: 11px;"></i>
                            </a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="page-item disabled" aria-disabled="true"><span class="page-link rounded-2">{{ $element }}</span></li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" aria-current="page"><span class="page-link rounded-2">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link rounded-2" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link rounded-2" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                                <i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i>
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                            <span class="page-link rounded-2" aria-hidden="true">
                                <i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i>
                            </span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif
