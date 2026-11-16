@extends('layouts.sausage_master')

@section('content')
@php
    $selBatch = request('batch');
    $selItem = request('item');
    $fmt = fn($v) => number_format((float) $v, 2);
@endphp
<div class="container-fluid pt-3">
    <div class="d-flex flex-wrap align-items-center mb-2">
        <a href="{{ route('stuffing_weights') }}" class="mr-3">&larr; Stuffing</a>
        <h4 class="mb-0 mr-auto"><i class="fa fa-balance-scale mr-2"></i>Harmonize stuffing batches</h4>
    </div>

    <div class="alert {{ $holds ? 'alert-info' : 'alert-secondary' }} py-2 small">
        Stuffing weighs the <strong>input</strong>; the transfer to dispatch weighs the <strong>output</strong>; the lines in between come from recipe yields.
        Harmonizing rebuilds a batch from both measured weights and places the difference on one step.
        @if($holds)
            <strong>Review mode is on:</strong> new orders wait here and go to ProductionData only when you <em>apply and release</em>.
        @else
            Review mode is off (<code>PRODUCTION_ORDERS_HARMONIZE=off</code>): orders go to ProductionData when generated, and lines already there are not changed.
        @endif
        @unless($writes) Write target is <code>local</code>, so nothing is written to ProductionData. @endunless
    </div>

    <form method="GET" action="{{ route('stuffing_harmonize') }}" class="form-inline mb-2">
        <label class="mr-1 small" for="hFrom">From</label>
        <input id="hFrom" type="date" name="from" value="{{ $from }}" class="form-control form-control-sm mr-2">
        <label class="mr-1 small" for="hTo">To</label>
        <input id="hTo" type="date" name="to" value="{{ $to }}" class="form-control form-control-sm mr-2">
        <select name="state" class="form-control form-control-sm mr-2" aria-label="State">
            @foreach(['open' => 'Not released', 'pending' => 'Not harmonized', 'harmonized' => 'Harmonized, not released', 'released' => 'Released', 'all' => 'All'] as $k => $v)
                <option value="{{ $k }}" @if($state === $k) selected @endif>{{ $v }}</option>
            @endforeach
        </select>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Batch or item" class="form-control form-control-sm mr-2" aria-label="Batch or item">
        <input type="hidden" name="mode" value="{{ $mode }}">
        <button class="btn btn-sm btn-primary">Show</button>
    </form>

    <div class="card mb-3">
        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover mb-0">
                <thead><tr>
                    <th>Batch</th><th>Packed item</th><th>Date</th><th class="text-right">Weighings</th>
                    <th class="text-right">Stuffed kg (in)</th><th class="text-right">Packed kg on orders</th>
                    <th class="text-right">Dispatched kg (out)</th><th class="text-right">Yield</th><th>State</th><th></th>
                </tr></thead>
                <tbody>
                @forelse($batches as $b)
                    @php $sel = $b->batch_no === $selBatch && $b->packed_item === $selItem; @endphp
                    <tr class="{{ $sel ? 'table-primary' : '' }}">
                        <td>{{ $b->batch_no }}</td>
                        <td>{{ $b->packed_item }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($b->first_date)->format('d M') }}</td>
                        <td class="text-right">{{ $b->weighings }}</td>
                        <td class="text-right">{{ $fmt($b->stuffed_kg) }}</td>
                        <td class="text-right">{{ $fmt($b->packed_out_kg) }}</td>
                        <td class="text-right">{{ $b->transfers ? $fmt($b->dispatched_kg) : '-' }} @if($b->transfers)<small class="text-muted">({{ $b->transfers }})</small>@endif</td>
                        <td class="text-right">{{ $b->stuffed_kg > 0 && $b->transfers ? number_format(100 * $b->dispatched_kg / $b->stuffed_kg, 1) . '%' : '' }}</td>
                        <td>
                            @if($b->state === 'released')<span class="badge badge-success">Released</span>
                            @elseif($b->state === 'harmonized')<span class="badge badge-info">Harmonized</span> <small class="text-muted">{{ $b->harmonize_mode }}</small>
                            @else<span class="badge badge-warning">Pending</span>@endif
                        </td>
                        <td><a href="{{ route('stuffing_harmonize', request()->except('batch', 'item') + ['batch' => $b->batch_no, 'item' => $b->packed_item, 'mode' => $mode]) }}">Review</a></td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-muted">No stuffing batches in this range.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($preview)
    <div class="card" id="preview">
        <div class="card-header">
            <form method="GET" action="{{ route('stuffing_harmonize') }}#preview" class="form-inline">
                @foreach(request()->except('mode') as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                <strong class="mr-3">{{ $selBatch }} / {{ $selItem }}</strong>
                <label class="mr-1 small" for="hMode">How</label>
                <select id="hMode" name="mode" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                    @foreach($modes as $k => $v)<option value="{{ $k }}" @if($mode === $k) selected @endif>{{ $v }}</option>@endforeach
                </select>
                <span class="small text-muted">
                    in {{ $fmt($preview['measured_in']) }} kg &middot; recipe output {{ $fmt($preview['expected_out']) }} kg &middot;
                    dispatched {{ $fmt($preview['measured_out']) }} kg
                    @if($preview['expected_out'] > 0 && $preview['measured_out'] > 0)
                        &middot; variance {{ $fmt($preview['measured_out'] - $preview['expected_out']) }} kg
                    @endif
                </span>
            </form>
        </div>
        @foreach($preview['errors'] as $e)<div class="alert alert-warning m-2 py-1 small">{{ $e }}</div>@endforeach
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr>
                    <th>Order</th><th>Step</th><th>Process</th><th>Line</th><th>Type</th><th>Item</th>
                    <th class="text-right">Now</th><th class="text-right">New</th><th class="text-right">Change</th><th>Location</th><th></th>
                </tr></thead>
                <tbody>
                @foreach($preview['lines'] as $l)
                    @php $d = $l->new_quantity - (float) $l->quantity; @endphp
                    <tr class="{{ $l->line_type === 'output' ? 'font-weight-bold' : '' }}">
                        <td>{{ $l->production_order_no }}</td>
                        <td>{{ $l->step }}</td>
                        <td>{{ $l->process }}</td>
                        <td>{{ $l->line_no }}</td>
                        <td>{{ $l->line_type }}</td>
                        <td>{{ $l->item_no }}</td>
                        <td class="text-right">{{ number_format((float) $l->quantity, 3) }}</td>
                        <td class="text-right">{{ number_format($l->new_quantity, 3) }}</td>
                        <td class="text-right {{ abs($d) > 0.0005 ? ($d < 0 ? 'text-danger' : 'text-success') : 'text-muted' }}">{{ abs($d) > 0.0005 ? number_format($d, 3) : '' }}</td>
                        <td>{{ $l->new_location }} @if($l->new_location !== $l->location_code)<small class="text-warning">(was {{ $l->location_code ?: 'blank' }})</small>@endif</td>
                        <td class="small">@if($l->locked)<span class="badge badge-secondary">{{ $l->production_data_at ? 'In ProductionData' : 'Posted' }}</span>@endif</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex flex-wrap">
            <form method="POST" action="{{ route('stuffing_harmonize_apply') }}" class="mr-2">
                @csrf
                @foreach(request()->only('from', 'to', 'state', 'q') as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                <input type="hidden" name="batch" value="{{ $selBatch }}">
                <input type="hidden" name="item" value="{{ $selItem }}">
                <input type="hidden" name="mode" value="{{ $mode }}">
                <input type="hidden" name="release" value="0">
                <button class="btn btn-outline-primary">Save</button>
            </form>
            @if($writes)
            <form method="POST" action="{{ route('stuffing_harmonize_apply') }}" onsubmit="return confirm('Write this batch to ProductionData? BC picks it up on its next run and it cannot be changed here afterwards.')">
                @csrf
                @foreach(request()->only('from', 'to', 'state', 'q') as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                <input type="hidden" name="batch" value="{{ $selBatch }}">
                <input type="hidden" name="item" value="{{ $selItem }}">
                <input type="hidden" name="mode" value="{{ $mode }}">
                <input type="hidden" name="release" value="1">
                <button class="btn btn-primary">Save and release to BC</button>
            </form>
            @endif
            <small class="text-muted ml-auto align-self-center">Locations are refreshed from the recipe table. Lines already in ProductionData or posted are not changed.</small>
        </div>
    </div>
    @endif
</div>
@endsection
