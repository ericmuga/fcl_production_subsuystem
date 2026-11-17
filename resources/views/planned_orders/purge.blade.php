@extends('layouts.master')

@section('content')
@php
    $free = $plans->filter(fn($p) => !$p->actioned && in_array($p->status, \App\Services\BcPlans\PlanPurge::PURGEABLE, true));
    $kept = $plans->filter(fn($p) => $p->actioned && in_array($p->status, \App\Services\BcPlans\PlanPurge::PURGEABLE, true));
    $purging = $plans->where('status', 'purging');
    $withdrawn = $plans->where('status', 'withdrawn');
    $forgettable = $withdrawn->where('actioned', false);
@endphp
<div class="container-fluid pt-3">
    <div class="d-flex flex-wrap align-items-center mb-2">
        <a href="{{ route('planned_orders') }}" class="mr-3">&larr; Planned orders</a>
        <h4 class="mb-0 mr-auto"><i class="fa fa-trash-alt mr-2"></i>Purge orders nobody has acted on</h4>
        <form method="GET" action="{{ route('planned_orders_purge') }}" class="form-inline">
            <select name="section" class="form-control form-control-sm mr-2" onchange="this.form.submit()" aria-label="Section">
                <option value="all">All sections</option>
                @foreach($sections as $s)<option value="{{ $s['value'] }}" @if($filters['section'] === $s['value']) selected @endif>{!! $s['step'] ? '&nbsp;&nbsp;&nbsp;' : '' !!}{{ $s['label'] }}</option>@endforeach
            </select>
            <label class="small mr-1">From</label>
            <input type="date" name="from" value="{{ $filters['from'] }}" class="form-control form-control-sm mr-2" onchange="this.form.submit()" aria-label="From">
            <label class="small mr-1">To</label>
            <input type="date" name="to" value="{{ $filters['to'] }}" class="form-control form-control-sm" onchange="this.form.submit()" aria-label="To">
        </form>
    </div>
    @if($error)<div class="alert alert-warning">{{ $error }}</div>@endif
    <p class="text-muted small">
        BC deletes the orders you select, with their lines, components and reservations; WMS then shows them withdrawn.
        An order is never deleted once anything was done on it: a weighing, a started batch or an execution order in WMS,
        or postings, journal lines or picks in BC. Run BC's regenerative plan afterwards to plan the demand again.
    </p>

    <form method="POST" action="{{ route('planned_orders_purge_request') }}" id="purgeForm">
        @csrf
        @foreach(['from', 'to', 'section'] as $k)<input type="hidden" name="{{ $k }}" value="{{ $filters[$k] }}">@endforeach
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center">
                <strong class="mr-auto">Never acted on <span class="badge badge-secondary">{{ $free->count() }}</span></strong>
                @if($free->count())
                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#purgeModal"><i class="fa fa-trash-alt mr-1"></i>Delete selected in BC</button>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr>
                        <th><input type="checkbox" id="checkAll" checked aria-label="Select all"></th>
                        <th>BC order</th><th>Date</th><th>Status</th><th>Section</th><th>Item</th><th class="text-right">Planned</th><th></th>
                    </tr></thead>
                    <tbody>
                    @forelse($free as $p)
                        <tr>
                            <td><input type="checkbox" name="plans[]" value="{{ $p->plan_no }}" class="js-plan" checked aria-label="Delete {{ $p->plan_no }}"></td>
                            <td>{{ $p->plan_no }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($p->plan_date)->format('d M Y') }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $p->status)) }}</td>
                            <td>{{ $p->section }}@if($p->process) <small class="text-muted">{{ $p->process }}</small>@endif</td>
                            <td>{{ $p->item_no }} <small class="text-muted">{{ $p->description }}</small></td>
                            <td class="text-right text-nowrap">{{ number_format($p->planned_qty, 2) }} {{ $p->uom }}</td>
                            <td class="small text-muted">{{ $p->purge_message }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-muted">No order in these filters is free to delete.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="purgeModal" tabindex="-1" role="dialog" aria-labelledby="purgeModalTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header py-2"><h5 class="modal-title" id="purgeModalTitle">Delete <span id="purgeCount">0</span> order(s) in BC?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                    <div class="modal-body">
                        <p>Weighing stops on them now. BC's job deletes each order unless it finds something posted on it in BC, in which case BC keeps it and tells you why here.</p>
                        <p class="mb-0">This cannot be undone: BC planning creates new orders for the demand.</p>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete in BC</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if($purging->count())
    <div class="card mb-3">
        <div class="card-header"><strong>Waiting for BC</strong> <small class="text-muted">deleted once BC's job has run; this page checks on every load</small></div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th>BC order</th><th>Date</th><th>Item</th><th>Requested</th><th>BC</th></tr></thead>
                <tbody>
                @foreach($purging as $p)
                    <tr>
                        <td>{{ $p->plan_no }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($p->plan_date)->format('d M') }}</td>
                        <td>{{ $p->item_no }} <small class="text-muted">{{ $p->description }}</small></td>
                        <td>{{ $p->purge_requested_at ? \Illuminate\Support\Carbon::parse($p->purge_requested_at)->format('d M H:i') : '' }}</td>
                        <td class="small">{{ $p->purge_message }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($kept->count())
    <div class="card mb-3">
        <div class="card-header"><strong>Acted on, kept</strong> <span class="badge badge-secondary">{{ $kept->count() }}</span> <small class="text-muted">close these from End of day instead</small></div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th>BC order</th><th>Date</th><th>Status</th><th>Item</th><th>What was done</th></tr></thead>
                <tbody>
                @foreach($kept as $p)
                    <tr>
                        <td>{{ $p->plan_no }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($p->plan_date)->format('d M') }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $p->status)) }}</td>
                        <td>{{ $p->item_no }} <small class="text-muted">{{ $p->description }}</small></td>
                        <td class="small">{{ implode(', ', $p->activity) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($withdrawn->count())
    <div class="card mb-3">
        <div class="card-header d-flex align-items-center">
            <strong class="mr-auto">Withdrawn <span class="badge badge-secondary">{{ $withdrawn->count() }}</span> <small class="text-muted">deleted in BC</small></strong>
            @if($forgettable->count())
            <form method="POST" action="{{ route('planned_orders_purge_forget') }}" class="mb-0" onsubmit="return confirm('Remove {{ $forgettable->count() }} withdrawn order(s) from WMS? Orders with weighings stay.');">
                @csrf
                @foreach(['from', 'to', 'section'] as $k)<input type="hidden" name="{{ $k }}" value="{{ $filters[$k] }}">@endforeach
                @foreach($forgettable as $p)<input type="hidden" name="plans[]" value="{{ $p->plan_no }}">@endforeach
                <button type="submit" class="btn btn-outline-danger btn-sm">Remove {{ $forgettable->count() }} from WMS</button>
            </form>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th>BC order</th><th>Date</th><th>Item</th><th></th></tr></thead>
                <tbody>
                @foreach($withdrawn as $p)
                    <tr>
                        <td>{{ $p->plan_no }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($p->plan_date)->format('d M') }}</td>
                        <td>{{ $p->item_no }} <small class="text-muted">{{ $p->description }}</small></td>
                        <td class="small text-muted">{{ $p->actioned ? 'kept in WMS: ' . implode(', ', $p->activity) : $p->purge_message }}</td>
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
    var boxes = function () { return Array.prototype.slice.call(document.querySelectorAll('.js-plan')); };
    if (all) all.addEventListener('change', function () { boxes().forEach(function (b) { b.checked = all.checked; }); });
    window.addEventListener('load', function () {
        window.jQuery('#purgeModal').on('show.bs.modal', function (e) {
            var n = boxes().filter(function (b) { return b.checked; }).length;
            document.getElementById('purgeCount').textContent = n;
            if (!n) { e.preventDefault(); alert('Select at least one order.'); }
        });
    });
})();
</script>
@endsection
