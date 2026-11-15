@extends('layouts.master')

@section('content')
@php
    $closable = $orders->whereIn('status', \App\Services\BcPlans\EndOfDay::CLOSABLE);
    $closing = $orders->where('status', 'closing');
@endphp
<div class="container-fluid pt-3">
    <div class="d-flex flex-wrap align-items-center mb-2">
        <a href="{{ route('planned_orders_weigh', ['section' => $section]) }}" class="mr-3">&larr; Weigh planned orders</a>
        <h4 class="mb-0 mr-auto"><i class="fa fa-moon mr-2"></i>End of day</h4>
        <form method="GET" action="{{ route('planned_orders_end_of_day') }}" class="form-inline">
            <select name="section" class="form-control form-control-sm mr-2" onchange="this.form.submit()" aria-label="Section">
                <option value="all">All sections</option>
                @foreach($sections as $s)<option value="{{ $s }}" @if($section === $s) selected @endif>{{ $s }}</option>@endforeach
            </select>
            <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm" onchange="this.form.submit()" aria-label="Date">
        </form>
    </div>
    @if($error)<div class="alert alert-warning">{{ $error }}</div>@endif

    <form method="POST" action="{{ route('planned_orders_close_day') }}" id="closeForm">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">
        <input type="hidden" name="section" value="{{ $section }}">
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center">
                <strong class="mr-auto">Orders to close <small class="text-muted">due {{ \Illuminate\Support\Carbon::parse($date)->format('d M Y') }} or earlier</small></strong>
                @if($closable->count())
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#closeModal"><i class="fa fa-lock mr-1"></i>Close selected in BC</button>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr>
                        <th><input type="checkbox" id="checkAll" checked aria-label="Select all"></th>
                        <th>BC order</th><th>Plan</th><th>Date</th><th>Section</th><th>Item</th>
                        <th class="text-right">Planned kg</th><th class="text-right">Produced kg</th><th class="text-right">Short kg</th><th>Batches</th><th></th>
                    </tr></thead>
                    <tbody>
                    @forelse($closable as $o)
                        @php $short = max((float) $o->planned_qty - (float) $o->produced, 0); @endphp
                        <tr>
                            <td><input type="checkbox" name="plans[]" value="{{ $o->plan_no }}" class="js-plan" @if(!$o->unposted) checked @else disabled @endif aria-label="Close {{ $o->plan_no }}"></td>
                            <td>{{ $o->plan_no }}</td>
                            <td>{{ $o->released_from ?: '-' }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($o->plan_date)->format('d M') }}</td>
                            <td>{{ $o->section }}</td>
                            <td>{{ $o->item_no }} <small class="text-muted">{{ $o->description }}</small></td>
                            <td class="text-right">{{ number_format($o->planned_qty, 2) }}</td>
                            <td class="text-right">{{ number_format($o->produced, 2) }}</td>
                            <td class="text-right {{ $short > 0 ? 'text-danger' : '' }}">{{ number_format($short, 2) }}</td>
                            <td>{{ $o->batches_done }} / {{ $o->batches }}</td>
                            <td class="small">
                                @if($o->unposted)<span class="text-danger">{{ $o->unposted }} weighing(s) not posted</span>
                                    <a href="{{ route('planned_orders_weigh', ['plan' => $o->plan_no]) }}">post</a>@endif
                                @if($o->status === 'released')<span class="badge badge-info">Not pushed</span>@endif
                                {{ $o->close_message }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-muted">Nothing left to close.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="closeModal" tabindex="-1" role="dialog" aria-labelledby="closeModalTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header py-2"><h5 class="modal-title" id="closeModalTitle">Close <span id="closeCount">0</span> order(s) in BC?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                    <div class="modal-body">
                        <p>BC finishes these production orders: what was not produced drops out of supply and the next MRP run plans it again.</p>
                        <p class="mb-0">Weighing stops on them now, and batches nobody started are cancelled. This cannot be undone from WMS.</p>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Close in BC</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if($closing->count())
    <div class="card">
        <div class="card-header"><strong>Waiting for BC</strong> <small class="text-muted">closed once BC's job has finished the order; this page checks on every load</small></div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th>BC order</th><th>Plan</th><th>Item</th><th>Requested</th><th>BC</th></tr></thead>
                <tbody>
                @foreach($closing as $o)
                    <tr>
                        <td>{{ $o->plan_no }}</td>
                        <td>{{ $o->released_from ?: '-' }}</td>
                        <td>{{ $o->item_no }} <small class="text-muted">{{ $o->description }}</small></td>
                        <td>{{ $o->close_requested_at ? \Illuminate\Support\Carbon::parse($o->close_requested_at)->format('d M H:i') : '' }}</td>
                        <td class="small">{{ $o->close_message }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
<script>
(function () {
    var all = document.getElementById('checkAll');
    var boxes = function () { return Array.prototype.slice.call(document.querySelectorAll('.js-plan:not(:disabled)')); };
    if (all) all.addEventListener('change', function () { boxes().forEach(function (b) { b.checked = all.checked; }); });
    // jQuery and Bootstrap load at the end of the layout, after this script: attach once the page has loaded.
    window.addEventListener('load', function () {
        window.jQuery('#closeModal').on('show.bs.modal', function (e) {
            var n = boxes().filter(function (b) { return b.checked; }).length;
            document.getElementById('closeCount').textContent = n;
            if (!n) { e.preventDefault(); alert('Select at least one order.'); }
        });
    });
})();
</script>
@endsection
