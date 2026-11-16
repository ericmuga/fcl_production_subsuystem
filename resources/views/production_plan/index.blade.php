@extends('layouts.sausage_master')

@section('content-header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-12">
            <h1 class="m-0">{{ $title }} | <small>day targets from the BC MRP plan</small></h1>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="card m-3">
    <div class="card-body">
        <form method="GET" action="{{ route('production_plan') }}" class="form-inline mb-3">
            <label class="mr-2">Date</label>
            <input type="date" name="date" value="{{ $date }}" class="form-control mr-3">
            <label class="mr-2">Location</label>
            <select name="location" class="form-control mr-3">
                <option value="all">All</option>
                @foreach($locations as $loc)
                    <option value="{{ $loc }}" @if($location === $loc) selected @endif>{{ $loc }}</option>
                @endforeach
            </select>
            <label class="mr-2">Status</label>
            <select name="status" class="form-control mr-3">
                @foreach(['all' => 'All', 'released' => 'Released (not pushed)', 'open' => 'Open', 'closed' => 'Closed', 'withdrawn' => 'Withdrawn'] as $value => $label)
                    <option value="{{ $value }}" @if($status === $value) selected @endif>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary mr-3">Show</button>
        </form>
        <form method="POST" action="{{ route('production_plan_sync') }}" class="mb-3">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <button type="submit" class="btn btn-outline-secondary btn-sm">Sync from BC</button>
            <small class="text-muted ml-2">Last sync: {{ $lastSync ?? 'never' }}</small>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Plan No.</th>
                        <th>Location</th>
                        <th>Item</th>
                        <th>Description</th>
                        <th class="text-right">Target</th>
                        <th class="text-right">Produced (WMS)</th>
                        <th class="text-right">Posted in BC</th>
                        <th class="text-right">Remaining</th>
                        <th>Progress</th>
                        <th>UoM</th>
                        <th title="WMS orders done / posted in BC / total batches">Batches</th>
                        <th>Orders</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($plans as $p)
                    @php
                        $pct = $p->planned_qty > 0 ? min(100, round(100 * $p->produced / $p->planned_qty)) : 0;
                        $remaining = max($p->planned_qty - $p->produced, 0);
                    @endphp
                    <tr>
                        <td>{{ $p->plan_no }}</td>
                        <td>{{ $p->location_code }}</td>
                        <td>{{ $p->item_no }}</td>
                        <td>{{ $p->item_description }}</td>
                        <td class="text-right">{{ number_format($p->planned_qty, 2) }}</td>
                        <td class="text-right">{{ number_format($p->produced, 2) }}</td>
                        <td class="text-right">{{ number_format($p->executed_qty, 2) }}</td>
                        <td class="text-right">{{ number_format($remaining, 2) }}</td>
                        <td style="min-width:120px">
                            <div class="progress" title="{{ $pct }}%">
                                <div class="progress-bar @if($p->produced > $p->planned_qty) bg-warning @endif" style="width: {{ $pct }}%">{{ $pct }}%</div>
                            </div>
                        </td>
                        <td>{{ $p->uom }}</td>
                        <td class="text-nowrap">
                            @if($p->batches > 0)
                                <a href="#" class="js-batches" data-url="{{ route('production_plan_batches', $p->plan_no) }}" data-target="batches-{{ $loop->index }}"
                                   title="{{ $p->batches_running }} running">{{ $p->batches_done_wms }} / {{ $p->batches_posted }} / {{ $p->batches }} {{ $p->batch_uom }}</a>
                            @else
                                <span class="text-muted">{{ $p->status === 'released' ? 'not pushed' : '-' }}</span>
                            @endif
                        </td>
                        <td>{{ $p->orders }}</td>
                        <td>
                            <span class="badge @if($p->status === 'open') badge-success @elseif($p->status === 'closed') badge-secondary @elseif($p->status === 'released') badge-info @else badge-warning @endif">{{ ucfirst($p->status) }}</span>
                        </td>
                        <td>
                            @if($p->status === 'open')
                                <form method="POST" action="{{ route('production_plan_close', $p->plan_no) }}"
                                      onsubmit="return confirm('Close the day for plan {{ $p->plan_no }}? Later weighings for it will be rejected.');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Close day</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    <tr id="batches-{{ $loop->index }}" class="d-none"><td colspan="14" class="bg-light p-2"></td></tr>
                @empty
                    <tr><td colspan="14" class="text-center text-muted">No BC plans for {{ $date }}. Plans appear once the planner releases them in BC; batches appear once they are pushed to WMS.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($plans->total() > 0)
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">Showing {{ $plans->firstItem() }}–{{ $plans->lastItem() }} of {{ $plans->total() }} plans</span>
                {{ $plans->links('pagination::bootstrap-4') }}
            </div>
        @endif
        <p class="text-muted small mb-0">
            Target = quantity BC published for the day. Produced = output of WMS execution orders carrying the plan no.
            Posted in BC = output BC has netted against the plan. Output above the target is allowed while the plan is open.
            Batches = WMS orders done / posted in BC / total: every WMS production order is one batch of the BC released order.
        </p>
    </div>
</div>
<script>
document.addEventListener('click', function (e) {
    var link = e.target.closest('.js-batches');
    if (!link) return;
    e.preventDefault();
    var row = document.getElementById(link.dataset.target);
    if (!row.classList.contains('d-none')) { row.classList.add('d-none'); return; }
    var cell = row.firstElementChild;
    cell.textContent = 'Loading...';
    row.classList.remove('d-none');
    fetch(link.dataset.url, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var esc = function (v) { var d = document.createElement('div'); d.textContent = v == null ? '' : v; return d.innerHTML; };
            var num = function (v) { return Number(v || 0).toLocaleString(undefined, { maximumFractionDigits: 2 }); };
            var html = '<table class="table table-sm mb-0"><thead><tr><th>Batch</th><th class="text-right">Size</th><th class="text-right">Produced (WMS)</th>'
                + '<th class="text-right">Posted in BC</th><th>WMS order</th><th>WMS</th><th>BC</th></tr></thead><tbody>';
            data.batches.forEach(function (b) {
                html += '<tr><td>' + esc(b.batch_code) + '</td><td class="text-right">' + num(b.quantity_base) + ' ' + esc(b.base_uom) + '</td>'
                    + '<td class="text-right">' + num(b.wms_produced) + '</td><td class="text-right">' + num(b.bc_executed_qty_base) + '</td>'
                    + '<td>' + esc(b.wms_order_no) + '</td><td>' + esc(b.wms_status) + '</td><td>' + esc(b.bc_status) + '</td></tr>';
            });
            cell.innerHTML = html + '</tbody></table>';
        })
        .catch(function () { cell.textContent = 'Could not load the batches.'; });
});
</script>
@endsection
