@extends('layouts.master')

@section('content')
<div class="container-fluid pt-3">
    <p><a href="{{ route('planned_orders') }}">&larr; Planned production orders</a></p>

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title mb-0">Plan {{ $plan->plan_no }} &middot; {{ $plan->item_no }} {{ $plan->description }}</h3>
        </div>
        <div class="card-body py-2">
            <div class="row small">
                <div class="col-md-2 col-6"><span class="text-muted">Date</span><br>{{ \Illuminate\Support\Carbon::parse($plan->plan_date)->format('d M Y') }}</div>
                <div class="col-md-2 col-6"><span class="text-muted">Section / location</span><br>{{ $plan->section ?? '-' }} / {{ $plan->location_code }}</div>
                <div class="col-md-2 col-6"><span class="text-muted">Planned</span><br>{{ number_format($plan->planned_qty, 2) }} {{ $plan->uom }}</div>
                <div class="col-md-2 col-6"><span class="text-muted">Posted in BC</span><br>{{ number_format($plan->executed_qty, 2) }} {{ $plan->uom }}</div>
                <div class="col-md-2 col-6"><span class="text-muted">Batches done</span><br>{{ $done }} / {{ $total }}</div>
                <div class="col-md-2 col-6"><span class="text-muted">Status</span><br>{{ ucfirst(str_replace('_', ' ', $plan->status)) }}</div>
            </div>
        </div>
    </div>

    @if(!$batch)
        <div class="alert alert-info">No open batch on this plan: all batches are done, or the plan has not been pushed to WMS in batches yet.</div>
    @else
    <form method="POST" action="{{ route('planned_orders_fulfil_store', $plan->plan_no) }}" id="fulfil-form">
        @csrf
        <input type="hidden" name="batch_code" value="{{ $batch->batch_code }}">
        <div class="card mb-3 border-success">
            <div class="card-body d-flex flex-wrap align-items-center">
                <div class="mr-4">
                    <div class="text-muted small">Batch</div>
                    <div class="h4 mb-0">{{ $batch->batch_code }}</div>
                </div>
                <div class="mr-4">
                    <div class="text-muted small">Target output</div>
                    <div class="h4 mb-0">{{ rtrim(rtrim(number_format($batch->quantity, 3), '0'), '.') }} {{ $batch->batch_uom }}
                        <small class="text-muted">= {{ number_format($batch->quantity_base, 2) }} {{ $batch->base_uom ?: 'KG' }}</small></div>
                </div>
                @if($openBatches->count() > 1)
                <div class="ml-auto">
                    <label class="small text-muted mb-0">Other open batch</label>
                    <select class="form-control form-control-sm" onchange="if(this.value) location.href='{{ route('planned_orders_fulfil', $plan->plan_no) }}?batch=' + encodeURIComponent(this.value)">
                        @foreach($openBatches as $ob)
                            <option value="{{ $ob->batch_code }}" @if($ob->batch_code === $batch->batch_code) selected @endif>{{ $ob->batch_code }} ({{ $ob->wms_status }})</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title mb-0">Actual weights (kg)</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr><th>Type</th><th>Item</th><th>Description</th><th>Location</th><th class="text-right">For this batch</th><th style="width:170px">Actual</th><th>Unit</th></tr>
                    </thead>
                    <tbody>
                    @foreach($lines as $i => $l)
                        @php $old = old("lines.$i.quantity", $l['quantity']); @endphp
                        <tr class="@if($l['type'] === 'output') table-success @elseif($l['by_product']) table-warning @endif">
                            <td>{{ $l['type'] === 'output' ? 'Output' : ($l['by_product'] ? 'By-product' : 'Consumption') }}</td>
                            <td>{{ $l['item_no'] }}</td>
                            <td>{{ $l['description'] }}</td>
                            <td>{{ $l['location_code'] }}</td>
                            <td class="text-right text-muted">{{ number_format($l['target'], 3) }}</td>
                            <td>
                                <input type="hidden" name="lines[{{ $i }}][type]" value="{{ $l['type'] }}">
                                <input type="hidden" name="lines[{{ $i }}][item_no]" value="{{ $l['item_no'] }}">
                                <input type="hidden" name="lines[{{ $i }}][uom]" value="{{ $l['uom'] }}">
                                <input type="hidden" name="lines[{{ $i }}][location_code]" value="{{ $l['location_code'] }}">
                                <input type="hidden" name="lines[{{ $i }}][by_product]" value="{{ $l['by_product'] ? 1 : 0 }}">
                                <input type="number" step="0.001" min="0" name="lines[{{ $i }}][quantity]" value="{{ $old }}"
                                       class="form-control form-control-sm text-right" @if($l['type'] === 'output') required autofocus @endif>
                            </td>
                            <td>{{ $l['uom'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex align-items-center">
                <div class="custom-control custom-checkbox mr-auto">
                    <input type="hidden" name="batch_completed" value="0">
                    <input type="checkbox" class="custom-control-input" id="batch_completed" name="batch_completed" value="1" checked>
                    <label class="custom-control-label" for="batch_completed">Batch complete (untick for a partial run; the batch stays open)</label>
                </div>
                <button type="submit" class="btn btn-success"
                        onclick="return confirm('Record batch {{ $batch->batch_code }} with these weights? It is sent to BC as a production order.');">Record batch</button>
            </div>
        </div>
        <p class="text-muted small mt-2">
            The quantities are filled in for one batch from the BC plan; enter what was actually used and produced, in kg.
            A zero line is left out. By-products are posted to BC as negative consumption.
        </p>
    </form>
    @endif
</div>
@endsection
