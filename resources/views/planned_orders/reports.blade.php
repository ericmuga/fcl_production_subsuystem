@extends('layouts.master')

@section('content')
@php
    $qty = fn($v) => $v === null ? '–' : number_format((float) $v, 1);
    $pct = fn($v) => $v === null ? '–' : number_format((float) $v, 1) . '%';
    $rateClass = fn($v) => $v === null ? '' : ($v >= 95 ? 'text-success' : ($v >= 80 ? 'text-warning' : 'text-danger'));
    $text = ['date', 'item_no', 'description', 'location_code', 'uom'];
    $query = array_filter(['view' => $view, 'from' => $from, 'to' => $to, 'location' => $location, 'item' => $item,
        'section' => $section, 'by_day' => $byDay ? 1 : null, 'shortages' => $shortagesOnly ? 1 : null]);
@endphp
<div class="container-fluid pt-3">
    <div class="d-flex flex-wrap align-items-center mb-2">
        <a href="{{ route('planned_orders') }}" class="mr-3">&larr; Planned orders</a>
        <h4 class="mb-0 mr-auto"><i class="fa fa-chart-bar mr-2"></i>MRP reports</h4>
        <a href="{{ route('planned_orders_reports', array_merge($query, ['export' => 'csv'])) }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-download mr-1"></i>Export CSV</a>
    </div>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><a class="nav-link @if($view === 'requirements') active @endif" href="{{ route('planned_orders_reports', ['view' => 'requirements']) }}">Material requirements</a></li>
        <li class="nav-item"><a class="nav-link @if($view === 'execution') active @endif" href="{{ route('planned_orders_reports', ['view' => 'execution']) }}">Execution rate</a></li>
    </ul>

    <form method="GET" action="{{ route('planned_orders_reports') }}" class="form-inline mb-3">
        <input type="hidden" name="view" value="{{ $view }}">
        <label class="small mr-1" for="rFrom">Due from</label>
        <input id="rFrom" type="date" name="from" value="{{ $from }}" class="form-control form-control-sm mr-2">
        <label class="small mr-1" for="rTo">to</label>
        <input id="rTo" type="date" name="to" value="{{ $to }}" class="form-control form-control-sm mr-3">
        <select name="section" class="form-control form-control-sm mr-2" aria-label="Section">
            <option value="all">All sections</option>
            @foreach($sections as $s)<option value="{{ $s }}" @if($section === $s) selected @endif>{{ $s }}</option>@endforeach
        </select>
        <select name="location" class="form-control form-control-sm mr-2" aria-label="Location">
            <option value="">All locations</option>
            @foreach($locations as $l)<option value="{{ $l }}" @if($location === $l) selected @endif>{{ $l }}</option>@endforeach
        </select>
        <input type="text" name="item" value="{{ $item }}" placeholder="Item, e.g. G2*" class="form-control form-control-sm mr-3" aria-label="Item filter" style="width:130px">
        <div class="form-check mr-3">
            <input class="form-check-input" type="checkbox" name="by_day" value="1" id="rByDay" @if($byDay) checked @endif>
            <label class="form-check-label small" for="rByDay">Per due date</label>
        </div>
        @if($view === 'requirements')
            <div class="form-check mr-3">
                <input class="form-check-input" type="checkbox" name="shortages" value="1" id="rShort" @if($shortagesOnly) checked @endif>
                <label class="form-check-label small" for="rShort">Shortages only</label>
            </div>
        @endif
        <button type="submit" class="btn btn-sm btn-primary">Show</button>
    </form>

    @if($bcError)<div class="alert alert-warning small">{{ $bcError }}</div>@endif

    @if($view === 'execution')
        <div class="row mb-3">
            <div class="col-md-3 col-6"><div class="border rounded p-2"><div class="small text-muted">Planned</div><div class="h5 mb-0">{{ $qty($totals['planned'] ?? 0) }}</div></div></div>
            <div class="col-md-3 col-6"><div class="border rounded p-2"><div class="small text-muted">Posted in WMS</div><div class="h5 mb-0">{{ $qty($totals['posted_wms'] ?? 0) }}</div></div></div>
            <div class="col-md-3 col-6"><div class="border rounded p-2"><div class="small text-muted">Execution % (WMS)</div><div class="h5 mb-0 {{ $rateClass($totals['rate_wms']) }}">{{ $pct($totals['rate_wms']) }}</div></div></div>
            <div class="col-md-3 col-6"><div class="border rounded p-2"><div class="small text-muted">Execution % (BC)</div><div class="h5 mb-0 {{ $rateClass($totals['rate_bc']) }}">{{ $pct($totals['rate_bc']) }}</div></div></div>
        </div>
        <p class="small text-muted">Plans due in the period that were released to WMS. WMS counts batch orders posted at the weighing station; BC counts output BC has posted. Firm planned output is not part of the rate.</p>
    @else
        <p class="small text-muted">Components of plans due in the period (firm planned, released, open and closing). Remaining need = need less what WMS has consumed. Stock and purchase orders are per location in BC; worksheet columns are the latest planning proposals not yet carried out.</p>
    @endif

    <div class="table-responsive">
        <table class="table table-sm table-striped table-hover">
            <thead class="thead-light"><tr>
                @foreach($columns as $k => $h)<th class="@if(!in_array($k, $text)) text-right @endif">{{ $h }}</th>@endforeach
            </tr></thead>
            <tbody>
            @forelse($rows as $r)
                <tr>
                    @foreach($columns as $k => $h)
                        @if(in_array($k, $text))
                            <td>{{ $r[$k] }}</td>
                        @elseif(in_array($k, ['rate_wms', 'rate_bc']))
                            <td class="text-right {{ $rateClass($r[$k]) }}">{{ $pct($r[$k]) }}</td>
                        @elseif($k === 'shortage')
                            <td class="text-right @if($r[$k] > 0) text-danger font-weight-bold @endif">{{ $qty($r[$k]) }}</td>
                        @elseif(in_array($k, ['orders', 'closed', 'ws_lines']))
                            <td class="text-right">{{ $r[$k] }}</td>
                        @else
                            <td class="text-right">{{ $qty($r[$k]) }}</td>
                        @endif
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($columns) }}" class="text-muted">Nothing due in this period.</td></tr>
            @endforelse
            </tbody>
            @if(count($rows))
                <tfoot><tr class="font-weight-bold">
                    @foreach($columns as $k => $h)
                        @if($k === 'item_no')<td>Total</td>
                        @elseif(in_array($k, $text))<td></td>
                        @elseif(in_array($k, ['rate_wms', 'rate_bc']))<td class="text-right">{{ $pct($totals[$k] ?? null) }}</td>
                        @elseif(in_array($k, ['orders', 'closed', 'ws_lines']))<td class="text-right">{{ (int) ($totals[$k] ?? 0) }}</td>
                        @else<td class="text-right">{{ $qty($totals[$k] ?? 0) }}</td>@endif
                    @endforeach
                </tr></tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
