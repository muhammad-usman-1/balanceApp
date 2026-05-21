@extends('layouts.admin')

@section('styles')
<style>
/* ── Card ── */
.card-do { border: none; border-radius: 8px; box-shadow: 0 1px 6px rgba(0,0,0,.08); overflow: hidden; }

/* ── Toolbar ── */
.do-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 10px;
    padding: 14px 18px;
    background: #fff; border-bottom: 1px solid #e3e6f0;
}
.do-toolbar-left  { display:flex; align-items:center; gap:8px; font-weight:600; font-size:.95rem; color:#333; }
.do-toolbar-right { display:flex; align-items:center; flex-wrap:wrap; gap:8px; }

/* ── Search ── */
.do-search { position:relative; }
.do-search input {
    padding: 5px 12px 5px 30px;
    border-radius: 20px; border: 1px solid #d1d3e2;
    font-size: .82rem; height: 32px; width: 200px;
    outline: none;
}
.do-search input:focus { border-color: #4e73df; box-shadow: 0 0 0 2px rgba(78,115,223,.15); }
.do-search .fa-search { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#aaa; font-size:.72rem; }

/* ── Per-page select ── */
.do-perpage {
    display: flex; align-items: center; gap: 6px; font-size: .82rem; color: #5a5c69;
}
.do-perpage select {
    border: 1px solid #d1d3e2; border-radius: 6px;
    padding: 3px 6px; font-size: .82rem; color: #5a5c69; outline: none;
}

/* ── Table ── */
.do-table-wrap { overflow: hidden; }     /* kills horizontal scroll */
.do-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
.do-table thead th {
    background: #f8f9fc; color: #5a5c69;
    font-size: .75rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .04em;
    padding: 10px 14px;
    border-bottom: 2px solid #e3e6f0; white-space: nowrap;
}
.do-table tbody tr { border-bottom: 1px solid #eaecf4; transition: background .12s; }
.do-table tbody tr:hover { background: #f8f9fc; }
.do-table tbody td { padding: 10px 14px; font-size: .87rem; vertical-align: middle; }

/* column widths */
.col-customer { width: 17%; }
.col-time     { width: 140px; }
.col-meals    { width: auto;  }
.col-snacks   { width: auto;  }
.col-status   { width: 148px; }
.col-actions  { width: 64px;  text-align: center; }

/* ── Meal items ── */
.meal-item { display:flex; align-items:center; gap:9px; margin-bottom:6px; }
.meal-item:last-child { margin-bottom:0; }
.meal-thumb { width:38px; height:38px; border-radius:7px; object-fit:cover; flex-shrink:0; border:1px solid #e3e6f0; }
.meal-name  { font-size:.83rem; font-weight:600; line-height:1.3; color:#333; }
.meal-sub   { font-size:.72rem; color:#aaa; }
.no-items   { color:#ccc; font-size:.8rem; font-style:italic; }

/* ── Status dropdown ── */
.status-select {
    -webkit-appearance: none; appearance: none;
    border-radius: 20px; padding: 4px 30px 4px 13px;
    font-size: .8rem; font-weight: 600; cursor: pointer;
    border: 1.5px solid; width: 100%;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23666'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center;
    outline: none; transition: all .15s;
}
.status-pending   { border-color:#f6c23e; color:#856404; background-color:#fff8e1; }
.status-delivered { border-color:#1cc88a; color:#155724; background-color:#e8f5e9; }

/* ── Slot badge ── */
.slot-badge {
    display: inline-block; background: #eef0f8; color: #5a5c69;
    border-radius: 20px; padding: 4px 11px;
    font-size: .77rem; font-weight: 600; white-space: nowrap;
}

/* ── Footer / Pagination ── */
.do-footer {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 8px;
    padding: 10px 18px;
    background: #f8f9fc; border-top: 1px solid #e3e6f0;
    font-size: .8rem; color: #858796;
}
.do-pagination { display:flex; align-items:center; gap:3px; }
.do-pagination button {
    min-width: 30px; height: 28px;
    border: 1px solid #d1d3e2; background: #fff;
    border-radius: 4px; padding: 0 8px;
    font-size: .78rem; cursor: pointer; color: #5a5c69;
    transition: all .12s;
}
.do-pagination button:hover:not(:disabled) { background: #eef0f8; border-color: #bbbdd6; }
.do-pagination button.pg-active { background: #4e73df; color: #fff; border-color: #4e73df; font-weight:700; }
.do-pagination button:disabled  { opacity: .4; cursor: default; }
</style>
@endsection

@section('content')
@php
$formatSlot = function($slot) {
    if (!$slot) return '—';
    if (strpos($slot, '_') === false) return $slot;
    $nums = [
        'zero'=>'0','one'=>'1','two'=>'2','three'=>'3','four'=>'4','five'=>'5',
        'six'=>'6','seven'=>'7','eight'=>'8','nine'=>'9','ten'=>'10',
        'eleven'=>'11','twelve'=>'12','midnight'=>'Midnight','noon'=>'Noon',
        'am'=>'am','pm'=>'pm','to'=>'to',
    ];
    $parts = explode('_', strtolower($slot));
    $out = [];
    for ($i = 0; $i < count($parts); $i++) {
        $val = $nums[$parts[$i]] ?? ucfirst($parts[$i]);
        if (is_numeric($val) && isset($parts[$i+1]) && in_array($parts[$i+1], ['am','pm'])) {
            $out[] = $val . ($nums[$parts[$i+1]] ?? $parts[$i+1]);
            $i++;
        } else {
            $out[] = $val;
        }
    }
    return implode(' ', $out);
};
@endphp

<div class="card card-do">

    {{-- ── Toolbar ── --}}
    <div class="do-toolbar">
        <div class="do-toolbar-left">
            <i class="fas fa-truck" style="color:#4e73df;"></i>
            Today's Delivery Orders
            <span class="badge badge-primary" id="orderCount">{{ $deliveryOrders->count() }}</span>
        </div>
        <div class="do-toolbar-right">
            <div class="do-search">
                <i class="fas fa-search"></i>
                <input type="text" id="doSearch" placeholder="Filter customer…">
            </div>

            <form method="GET" action="{{ route('admin.delivery-orders.index') }}"
                  style="display:flex;align-items:center;gap:6px;">
                <div class="input-group input-group-sm" style="width:175px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text" style="padding:0 8px;">
                            <i class="fas fa-calendar-alt" style="font-size:.72rem;"></i>
                        </span>
                    </div>
                    <input type="date" name="date" class="form-control form-control-sm"
                           value="{{ $date->format('Y-m-d') }}"
                           onchange="this.form.submit()">
                </div>
            </form>

            <a href="{{ route('admin.delivery-orders.print-all', ['date' => $date->format('Y-m-d')]) }}"
               target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-print mr-1"></i> Print All Delivery Notes
            </a>

            <button id="makeAllDeliveredBtn" class="btn btn-sm btn-success">
                <i class="fas fa-check-double mr-1"></i> Make All Delivered
            </button>
        </div>
    </div>

    {{-- ── Per-page row ── --}}
    @if(!$deliveryOrders->isEmpty())
    <div style="padding:8px 18px; background:#fff; border-bottom:1px solid #e3e6f0; display:flex; align-items:center; gap:16px;">
        <div class="do-perpage">
            Show
            <select id="doPerPage">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            entries
        </div>
    </div>
    @endif

    {{-- ── Table ── --}}
    <div class="do-table-wrap">
        @if($deliveryOrders->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-box-open fa-2x mb-2 d-block" style="color:#d1d3e2;"></i>
                No delivery orders scheduled for <strong>{{ $date->format('l, d M Y') }}</strong>.
            </div>
        @else
        <table class="do-table" id="doTable">
            <thead>
                <tr>
                    <th class="col-customer">Customer</th>
                    <th class="col-time">Delivery Time</th>
                    <th class="col-meals">Meals</th>
                    <th class="col-snacks">Snacks</th>
                    <th class="col-status">Status</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody id="doTableBody">
            @foreach($deliveryOrders as $order)
                @php
                    $sub    = $order->subscription;
                    $addr   = $sub->address;
                    $day    = $order->subscriptionDay;
                    $meals  = $day->subscription_meals->where('type', 'is meal');
                    $snacks = $day->subscription_meals->where('type', 'is snack');
                @endphp
                <tr data-order-id="{{ $order->id }}"
                    data-customer="{{ strtolower($sub->user->name ?? '') }}">

                    <td class="col-customer">
                        <div style="font-weight:600;color:#333;">{{ $sub->user->name ?? '—' }}</div>
                        <div style="font-size:.77rem;color:#aaa;margin-top:2px;">
                            <i class="fas fa-phone" style="font-size:.65rem;"></i>
                            {{ $addr?->phone_number ?? $sub->user->mobile ?? '—' }}
                        </div>
                    </td>

                    <td class="col-time">
                        <span class="slot-badge">
                            {{ $formatSlot($addr?->preferred_delivery_slot ?? '') }}
                        </span>
                    </td>

                    <td class="col-meals">
                        @forelse($meals as $sm)
                            <div class="meal-item">
                                @if($sm->meal?->image)
                                    <img src="{{ $sm->meal->image->getUrl('thumb') }}" class="meal-thumb" alt="">
                                @else
                                    <img src="/images/placeholder.png" class="meal-thumb" alt="">
                                @endif
                                <div>
                                    <div class="meal-name">{{ $sm->meal->title ?? '—' }}</div>
                                    @if($sm->meal?->extras)
                                        <div class="meal-sub">{{ $sm->meal->extras }}</div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <span class="no-items">No meals assigned</span>
                        @endforelse
                    </td>

                    <td class="col-snacks">
                        @forelse($snacks as $sm)
                            <div class="meal-item">
                                @if($sm->meal?->image)
                                    <img src="{{ $sm->meal->image->getUrl('thumb') }}" class="meal-thumb" alt="">
                                @else
                                    <img src="/images/placeholder.png" class="meal-thumb" alt="">
                                @endif
                                <div>
                                    <div class="meal-name">{{ $sm->meal->title ?? '—' }}</div>
                                </div>
                            </div>
                        @empty
                            <span class="no-items">No snacks assigned</span>
                        @endforelse
                    </td>

                    <td class="col-status">
                        <select class="status-select status-{{ $order->status }}"
                                data-order-id="{{ $order->id }}">
                            <option value="pending"   {{ $order->status === 'pending'   ? 'selected' : '' }}>Pending</option>
                            <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        </select>
                    </td>

                    <td class="col-actions">
                        <a href="{{ route('admin.delivery-orders.print', $order->id) }}"
                           target="_blank"
                           class="btn btn-sm btn-light border"
                           title="Print Delivery Note"
                           style="padding:4px 8px;">
                            <i class="fas fa-file-alt text-secondary"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- ── Footer / Pagination ── --}}
    @if(!$deliveryOrders->isEmpty())
    <div class="do-footer">
        <span id="pageInfo">—</span>
        <div style="display:flex;align-items:center;gap:16px;">
            <div style="font-size:.76rem;color:#aaa;">
                <i class="fas fa-circle" style="color:#f6c23e;font-size:.55rem;"></i> Pending &nbsp;
                <i class="fas fa-circle" style="color:#1cc88a;font-size:.55rem;"></i> Delivered
            </div>
            <div class="do-pagination" id="doPagination"></div>
        </div>
    </div>
    @endif

</div>
@endsection

@section('scripts')
@parent
<script>
$(function () {
    var csrfToken     = '{{ csrf_token() }}';
    var deliveryDate  = @json($date->format('Y-m-d'));
    var deliveryLabel = @json($date->format('d M Y'));

    /* ── Pagination state ── */
    var allRows   = $('#doTableBody tr').toArray();
    var perPage   = 25;
    var curPage   = 1;
    var filtered  = allRows.slice(); // current visible set after search

    function totalPages() { return Math.max(1, Math.ceil(filtered.length / perPage)); }

    function renderPage() {
        var tp    = totalPages();
        if (curPage > tp) curPage = tp;
        var start = (curPage - 1) * perPage;
        var end   = start + perPage;

        // hide all, show slice
        $(allRows).hide();
        filtered.slice(start, end).forEach(function (r) { $(r).show(); });

        // page info
        var showing = Math.min(end, filtered.length);
        $('#pageInfo').text(
            'Showing ' + (filtered.length ? start + 1 : 0) + '–' + showing +
            ' of ' + filtered.length + ' orders · ' + deliveryLabel
        );

        // pagination buttons
        var $pg = $('#doPagination').empty();
        var $prev = $('<button>').html('&lsaquo; Prev').prop('disabled', curPage === 1)
            .on('click', function() { curPage--; renderPage(); });
        $pg.append($prev);

        var range = buildRange(curPage, tp);
        range.forEach(function(p) {
            if (p === '…') {
                $pg.append($('<button>').text('…').prop('disabled', true));
            } else {
                var $b = $('<button>').text(p);
                if (p === curPage) $b.addClass('pg-active');
                $b.on('click', function() { curPage = p; renderPage(); });
                $pg.append($b);
            }
        });

        var $next = $('<button>').html('Next &rsaquo;').prop('disabled', curPage === tp)
            .on('click', function() { curPage++; renderPage(); });
        $pg.append($next);
    }

    /* Build page number range with ellipsis */
    function buildRange(cur, total) {
        if (total <= 7) {
            var r = [];
            for (var i = 1; i <= total; i++) r.push(i);
            return r;
        }
        var pages = [1];
        if (cur > 3) pages.push('…');
        for (var i = Math.max(2, cur-1); i <= Math.min(total-1, cur+1); i++) pages.push(i);
        if (cur < total - 2) pages.push('…');
        pages.push(total);
        return pages;
    }

    /* ── Live search ── */
    $('#doSearch').on('input', function () {
        var q = $(this).val().toLowerCase().trim();
        filtered = allRows.filter(function(r) {
            return !q || $(r).data('customer').indexOf(q) !== -1;
        });
        curPage = 1;
        renderPage();
    });

    /* ── Per-page change ── */
    $('#doPerPage').on('change', function () {
        perPage = parseInt($(this).val(), 10);
        curPage = 1;
        renderPage();
    });

    /* ── Status change via AJAX ── */
    $(document).on('change', '.status-select', function () {
        var $sel    = $(this);
        var orderId = $sel.data('order-id');
        var status  = $sel.val();
        $sel.removeClass('status-pending status-delivered').addClass('status-' + status);
        $.post('/admin/delivery-orders/' + orderId + '/status', {
            _token: csrfToken, status: status
        }).fail(function () { alert('Failed to update status.'); });
    });

    /* ── Make All Delivered ── */
    $('#makeAllDeliveredBtn').on('click', function () {
        if (!confirm('Mark all orders as Delivered for ' + deliveryLabel + '?')) return;
        $.post('{{ route("admin.delivery-orders.make-all-delivered") }}', {
            _token: csrfToken, date: deliveryDate
        }).done(function () {
            $('.status-select').val('delivered')
                .removeClass('status-pending').addClass('status-delivered');
        }).fail(function () { alert('Failed to update.'); });
    });

    /* ── Init ── */
    renderPage();
});
</script>
@endsection
