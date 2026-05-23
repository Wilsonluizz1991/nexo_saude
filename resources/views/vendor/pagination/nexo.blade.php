@if ($paginator->hasPages())
    <nav class="nexo-pagination" role="navigation" aria-label="Paginação">
        @if ($paginator->onFirstPage())
            <span class="nexo-page-step is-disabled"><i class="bi bi-chevron-left"></i></span>
        @else
            <a class="nexo-page-step" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="bi bi-chevron-left"></i></a>
        @endif

        <div class="nexo-page-numbers">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="nexo-page-dots">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="nexo-page-number is-active">{{ $page }}</span>
                        @else
                            <a class="nexo-page-number" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        @if ($paginator->hasMorePages())
            <a class="nexo-page-step" href="{{ $paginator->nextPageUrl() }}" rel="next"><i class="bi bi-chevron-right"></i></a>
        @else
            <span class="nexo-page-step is-disabled"><i class="bi bi-chevron-right"></i></span>
        @endif
    </nav>
@endif
