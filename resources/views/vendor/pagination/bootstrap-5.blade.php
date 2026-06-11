@if ($paginator->hasPages())
<style>
    .pagination-pro {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 1rem 0;
        margin: 0;
        list-style: none;
    }
    .pagination-pro .page-item {
        display: inline-flex;
    }
    .pagination-pro .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 10px;
        font-size: 0.875rem;
        font-weight: 450;
        color: #555;
        text-decoration: none;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        transition: all .2s cubic-bezier(.4,0,.2,1);
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
        position: relative;
    }
    .pagination-pro .page-link:hover {
        background: #f0f2ff;
        color: #1a237e;
        border-color: #c5cae9;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(26,35,126,.12);
        z-index: 1;
    }
    .pagination-pro .page-link:active {
        transform: translateY(0);
        box-shadow: none;
    }
    .pagination-pro .page-item.active .page-link {
        background: linear-gradient(135deg, #1a237e, #283593);
        color: #fff;
        border-color: #1a237e;
        font-weight: 600;
        box-shadow: 0 4px 14px rgba(26,35,126,.3);
        cursor: default;
    }
    .pagination-pro .page-item.disabled .page-link {
        color: #cbd5e1;
        background: #f8fafc;
        border-color: #f1f5f9;
        cursor: not-allowed;
        pointer-events: none;
        box-shadow: none;
        transform: none;
    }
    .pagination-pro .page-link .bi {
        font-size: .75rem;
        line-height: 1;
    }
    .pagination-pro .page-link.ellipsis {
        border: none;
        background: none;
        color: #94a3b8;
        cursor: default;
        min-width: 28px;
        padding: 0 2px;
        letter-spacing: 2px;
        box-shadow: none;
        pointer-events: none;
    }

    /* Info text */
    .pagination-info {
        text-align: center;
        font-size: 0.8rem;
        color: #94a3b8;
        margin-top: 0;
        margin-bottom: 0.5rem;
        font-weight: 400;
    }
    .pagination-info strong {
        color: #475569;
        font-weight: 600;
    }

    /* Dark theme */
    @media (prefers-color-scheme: dark) {
        .pagination-pro .page-link {
            color: #cbd5e1;
            background: #1e293b;
            border-color: #334155;
        }
        .pagination-pro .page-link:hover {
            background: #1e2a4a;
            color: #93a3ff;
            border-color: #3b4a7a;
            box-shadow: 0 4px 12px rgba(0,0,0,.3);
        }
        .pagination-pro .page-item.active .page-link {
            background: linear-gradient(135deg, #3b4a7a, #1a237e);
            color: #fff;
            border-color: #3b4a7a;
            box-shadow: 0 4px 14px rgba(0,0,0,.4);
        }
        .pagination-pro .page-item.disabled .page-link {
            color: #475569;
            background: #0f172a;
            border-color: #1e293b;
        }
        .pagination-pro .page-link.ellipsis {
            color: #475569;
        }
        .pagination-info { color: #64748b; }
        .pagination-info strong { color: #94a3b8; }
    }

    /* Mobile responsive */
    @media (max-width: 576px) {
        .pagination-pro .page-link {
            min-width: 32px;
            height: 32px;
            padding: 0 7px;
            font-size: 0.8rem;
            border-radius: 6px;
        }
        .pagination-pro .page-link.ellipsis {
            min-width: 20px;
            padding: 0;
        }
        .pagination-pro .page-link .page-label {
            display: none;
        }
        .pagination-pro .page-link .page-label-short {
            display: inline;
        }
    }
    @media (min-width: 577px) {
        .pagination-pro .page-link .page-label-short {
            display: none;
        }
    }
</style>

@php
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $window = 2; // pages on each side of current
@endphp

@if($paginator->total() > 0)
<div class="pagination-info">
    Showing <strong>{{ $paginator->firstItem() }}</strong>
    to <strong>{{ $paginator->lastItem() }}</strong>
    of <strong>{{ $paginator->total() }}</strong>
</div>
@endif

<nav aria-label="Pagination">
    <ul class="pagination-pro">
        {{-- First --}}
        @if($current > 1)
        <li class="page-item" title="First page">
            <a class="page-link" href="{{ $paginator->url(1) }}" aria-label="First page">
                <i class="bi bi-chevron-double-left" aria-hidden="true"></i>
            </a>
        </li>
        @else
        <li class="page-item disabled" title="First page">
            <span class="page-link" aria-label="First page">
                <i class="bi bi-chevron-double-left" aria-hidden="true"></i>
            </span>
        </li>
        @endif

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
        <li class="page-item disabled">
            <span class="page-link" aria-label="Previous">
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
                <span class="page-label">&nbsp;Previous</span>
                <span class="page-label-short">&nbsp;Prev</span>
            </span>
        </li>
        @else
        <li class="page-item">
            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
                <span class="page-label">&nbsp;Previous</span>
                <span class="page-label-short">&nbsp;Prev</span>
            </a>
        </li>
        @endif

        {{-- Page Numbers with Ellipsis --}}
        @if($last <= 7)
            {{-- Few pages: show all --}}
            @for($i = 1; $i <= $last; $i++)
                @if($i == $current)
                <li class="page-item active" aria-current="page">
                    <span class="page-link">{{ $i }}</span>
                </li>
                @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($i) }}" aria-label="Go to page {{ $i }}">{{ $i }}</a>
                </li>
                @endif
            @endfor
        @else
            {{-- Many pages: show with ellipsis --}}
            @php
                $start = max(1, $current - $window);
                $end = min($last, $current + $window);
                $showStartEllipsis = $start > 2;
                $showEndEllipsis = $end < $last - 1;
            @endphp

            {{-- Page 1 --}}
            @if($start > 1)
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->url(1) }}" aria-label="Go to page 1">1</a>
            </li>
            @endif

            {{-- Start ellipsis --}}
            @if($showStartEllipsis)
            <li class="page-item disabled">
                <span class="page-link ellipsis" aria-hidden="true">&hellip;</span>
            </li>
            @endif

            {{-- Window pages --}}
            @for($i = $start; $i <= $end; $i++)
                @if($i == $current)
                <li class="page-item active" aria-current="page">
                    <span class="page-link">{{ $i }}</span>
                </li>
                @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($i) }}" aria-label="Go to page {{ $i }}">{{ $i }}</a>
                </li>
                @endif
            @endfor

            {{-- End ellipsis --}}
            @if($showEndEllipsis)
            <li class="page-item disabled">
                <span class="page-link ellipsis" aria-hidden="true">&hellip;</span>
            </li>
            @endif

            {{-- Last page --}}
            @if($end < $last)
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->url($last) }}" aria-label="Go to page {{ $last }}">{{ $last }}</a>
            </li>
            @endif
        @endif

        {{-- Next --}}
        @if ($paginator->hasMorePages())
        <li class="page-item">
            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">
                <span class="page-label">Next&nbsp;</span>
                <span class="page-label-short">Next&nbsp;</span>
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            </a>
        </li>
        @else
        <li class="page-item disabled">
            <span class="page-link" aria-label="Next">
                <span class="page-label">Next&nbsp;</span>
                <span class="page-label-short">Next&nbsp;</span>
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            </span>
        </li>
        @endif

        {{-- Last --}}
        @if($current < $last)
        <li class="page-item" title="Last page">
            <a class="page-link" href="{{ $paginator->url($last) }}" aria-label="Last page">
                <i class="bi bi-chevron-double-right" aria-hidden="true"></i>
            </a>
        </li>
        @else
        <li class="page-item disabled" title="Last page">
            <span class="page-link" aria-label="Last page">
                <i class="bi bi-chevron-double-right" aria-hidden="true"></i>
            </span>
        </li>
        @endif
    </ul>
</nav>
@endif
