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
                @foreach(['all' => 'All', 'open' => 'Open', 'closed' => 'Closed', 'withdrawn' => 'Withdrawn'] as $value => $label)
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
                        <td>{{ $p->orders }}</td>
                        <td>
                            <span class="badge @if($p->status === 'open') badge-success @elseif($p->status === 'closed') badge-secondary @else badge-warning @endif">{{ ucfirst($p->status) }}</span>
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
                @empty
                    <tr><td colspan="13" class="text-center text-muted">No BC plans for {{ $date }}. Plans appear once the planner releases and publishes them in BC.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <p class="text-muted small mb-0">
            Target = quantity BC published for the day. Produced = output of WMS execution orders carrying the plan no.
            Posted in BC = output BC has netted against the plan. Output above the target is allowed while the plan is open.
        </p>
    </div>
</div>
@endsection
