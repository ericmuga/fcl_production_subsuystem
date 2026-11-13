{{-- Planned production orders from BC. $pp = PlannedOrdersController::dashboardData(); $ppAction = form target --}}
@php $f = $pp['filters']; @endphp
<div class="card mb-3" id="planned-orders">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0"><i class="fa fa-industry mr-2"></i>Planned production orders <small class="text-muted">from BC</small></h3>
        <small class="text-muted ml-auto">Last sync: {{ $pp['lastSync'] ?? 'never' }}</small>
    </div>
    <div class="card-body">
        @if($pp['error'])
            <div class="alert alert-warning mb-0">{{ $pp['error'] }}</div>
        @else
        <form method="GET" action="{{ $ppAction }}#planned-orders" class="form-row align-items-end mb-3">
            <div class="col-md-2 col-6 mb-2">
                <label class="small mb-0">Item</label>
                <input type="text" name="pp_item" value="{{ $f['item'] }}" class="form-control form-control-sm" placeholder="No. or description">
            </div>
            <div class="col-md-2 col-6 mb-2">
                <label class="small mb-0">Order no.</label>
                <input type="text" name="pp_order" value="{{ $f['order'] }}" class="form-control form-control-sm" placeholder="Released / plan no.">
            </div>
            <div class="col-md-2 col-6 mb-2">
                <label class="small mb-0">Planned order no.</label>
                <input type="text" name="pp_planned" value="{{ $f['planned'] }}" class="form-control form-control-sm" placeholder="Firm planned no.">
            </div>
            <div class="col-md-1 col-6 mb-2">
                <label class="small mb-0">Section</label>
                <select name="pp_section" class="form-control form-control-sm">
                    <option value="all">All</option>
                    @foreach($pp['sections'] as $s)
                        <option value="{{ $s }}" @if($f['section'] === $s) selected @endif>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <label class="small mb-0">Status</label>
                <select name="pp_status" class="form-control form-control-sm">
                    @foreach($pp['statuses'] as $value => $label)
                        <option value="{{ $value }}" @if($f['status'] === $value) selected @endif>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 col-6 mb-2">
                <label class="small mb-0">From</label>
                <input type="date" name="pp_from" value="{{ $f['from'] }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-1 col-6 mb-2">
                <label class="small mb-0">To</label>
                <input type="date" name="pp_to" value="{{ $f['to'] }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-1 col-6 mb-2">
                <button type="submit" class="btn btn-sm btn-primary btn-block">Filter</button>
                <a href="{{ $ppAction }}#planned-orders" class="btn btn-sm btn-link btn-block p-0">Clear</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered mb-2">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Order no.</th>
                        <th>Planned order</th>
                        <th>Section</th>
                        <th>Location</th>
                        <th>Item</th>
                        <th>Description</th>
                        <th class="text-right">Planned</th>
                        <th class="text-right">Produced</th>
                        <th class="text-right">Remaining</th>
                        <th title="WMS orders done / batches">Batches</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($pp['plans'] as $p)
                    @php $remaining = max((float) $p->planned_qty - (float) $p->produced, 0); @endphp
                    <tr>
                        <td class="text-nowrap">{{ \Illuminate\Support\Carbon::parse($p->plan_date)->format('d M Y') }}</td>
                        <td><a href="#" class="js-plan-modal" data-url="{{ route('planned_orders_details', $p->plan_no) }}">{{ $p->plan_no }}</a></td>
                        <td>
                            @if($p->status === 'firm_planned')
                                <a href="#" class="js-plan-modal" data-url="{{ route('planned_orders_details', $p->plan_no) }}">{{ $p->plan_no }}</a>
                            @else
                                {{ $p->released_from ?? '' }}
                            @endif
                        </td>
                        <td title="{{ str_replace('|', ', ', (string) $p->sections) }}">{{ $p->section }}</td>
                        <td>{{ $p->location_code }}</td>
                        <td>{{ $p->item_no }}</td>
                        <td>{{ $p->description }}</td>
                        <td class="text-right text-nowrap">{{ number_format($p->planned_qty, 2) }} {{ $p->uom }}</td>
                        <td class="text-right">{{ number_format($p->produced, 2) }}</td>
                        <td class="text-right">{{ number_format($remaining, 2) }}</td>
                        <td class="text-nowrap">{{ $p->batches ? $p->batches_done . ' / ' . $p->batches . ' ' . $p->batch_uom : '-' }}</td>
                        <td>
                            <span class="badge @switch($p->status) @case('open') badge-success @break @case('released') badge-info @break @case('firm_planned') badge-light @break @case('closed') badge-secondary @break @default badge-warning @endswitch">
                                {{ ucfirst(str_replace('_', ' ', $p->status)) }}
                            </span>
                        </td>
                        <td class="text-nowrap">
                            @if($p->status === 'open' && $p->batches > $p->batches_done)
                                <a href="{{ route('planned_orders_fulfil', $p->plan_no) }}" class="btn btn-sm btn-success py-0">Fulfil</a>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 js-plan-modal" data-url="{{ route('planned_orders_details', $p->plan_no) }}">View</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="13" class="text-center text-muted">No planned orders match. Orders appear after BC planning creates them and <code>php artisan bc:sync-plans</code> runs.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $pp['plans']->fragment('planned-orders')->links() }}
        <p class="text-muted small mb-0">
            Planned and produced are in kg. Firm planned orders become released when the planner releases them in BC; a released order
            is cut into batches when it is pushed to WMS (open), and every WMS order fulfils one batch.
        </p>
        @endif
    </div>
</div>
