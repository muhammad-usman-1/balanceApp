@if ($paginator->hasPages())
<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">

    {{-- Info text --}}
    <span style="font-size:.78rem; color:#6b7280;">
        Showing <strong>{{ $paginator->firstItem() }}</strong> to <strong>{{ $paginator->lastItem() }}</strong>
        of <strong>{{ $paginator->total() }}</strong> results
    </span>

    {{-- Page buttons --}}
    <div style="display:flex; gap:4px; flex-wrap:wrap;">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span style="display:inline-flex; align-items:center; padding:5px 11px; border-radius:7px;
                         font-size:.78rem; font-weight:600; background:#f3f4f6; color:#d1d5db; cursor:default;">
                &laquo; Prev
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               style="display:inline-flex; align-items:center; padding:5px 11px; border-radius:7px;
                      font-size:.78rem; font-weight:600; background:#f3f4f6; color:#374151;
                      text-decoration:none; transition:background .15s;"
               onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                &laquo; Prev
            </a>
        @endif

        {{-- Page numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span style="display:inline-flex; align-items:center; padding:5px 10px; font-size:.78rem; color:#9ca3af;">
                    …
                </span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span style="display:inline-flex; align-items:center; padding:5px 11px; border-radius:7px;
                                     font-size:.78rem; font-weight:700; background:#111827; color:#fff; cursor:default;">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                           style="display:inline-flex; align-items:center; padding:5px 11px; border-radius:7px;
                                  font-size:.78rem; font-weight:600; background:#f3f4f6; color:#374151;
                                  text-decoration:none; transition:background .15s;"
                           onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               style="display:inline-flex; align-items:center; padding:5px 11px; border-radius:7px;
                      font-size:.78rem; font-weight:600; background:#f3f4f6; color:#374151;
                      text-decoration:none; transition:background .15s;"
               onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                Next &raquo;
            </a>
        @else
            <span style="display:inline-flex; align-items:center; padding:5px 11px; border-radius:7px;
                         font-size:.78rem; font-weight:600; background:#f3f4f6; color:#d1d5db; cursor:default;">
                Next &raquo;
            </span>
        @endif

    </div>
</div>
@endif
