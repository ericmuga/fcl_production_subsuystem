@extends('layouts.master')

@section('content')
<style>
    .wg-orders { max-height: 70vh; overflow-y: auto; }
    .wg-orders .list-group-item { cursor: pointer; }
    .wg-orders .list-group-item.active .text-muted { color: #e9ecef !important; }
    .wg-net { font-size: 2.6rem; font-weight: 700; line-height: 1; }
    .wg-gross { font-size: 1.8rem; font-weight: 600; height: auto; }
    .wg-item-btn { text-align: left; white-space: normal; }
    .wg-line-row { cursor: pointer; }
    .btn-xl { padding: .75rem 1.25rem; font-size: 1.15rem; }
    .wg-progress { height: 6px; }
</style>
<div class="container-fluid pt-3">
    <div class="d-flex flex-wrap align-items-center mb-2">
        <a href="{{ route('planned_orders') }}" class="mr-3">&larr; Planned orders</a>
        <h4 class="mb-0 mr-auto"><i class="fa fa-balance-scale mr-2"></i>Weigh planned orders</h4>
        <a href="{{ route('planned_orders_vessels') }}" class="btn btn-sm btn-outline-secondary mr-2"><i class="fa fa-box mr-1"></i>Vessels</a>
        <a href="{{ route('planned_orders_end_of_day', ['section' => $section]) }}" class="btn btn-sm btn-outline-danger"><i class="fa fa-moon mr-1"></i>End of day</a>
    </div>

    <div class="row">
        {{-- 1. Pick the order --}}
        <div class="col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-header py-2">
                    <form method="GET" action="{{ route('planned_orders_weigh') }}" class="form-row mb-0">
                        <div class="col-5 mb-1">
                            <select name="section" class="form-control form-control-sm" onchange="this.form.submit()" aria-label="Section">
                                <option value="all">All sections</option>
                                @foreach($sections as $s)
                                    <option value="{{ $s }}" @if($section === $s) selected @endif>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-7 mb-1">
                            <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm" onchange="this.form.submit()" aria-label="Date">
                        </div>
                        <div class="col-12">
                            <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search order, plan, item...">
                        </div>
                    </form>
                </div>
                <div class="list-group list-group-flush wg-orders">
                    @forelse($orders as $o)
                        @php $pct = $o->batches ? round($o->batches_done / $o->batches * 100) : 0; @endphp
                        <a href="{{ route('planned_orders_weigh', ['plan' => $o->plan_no, 'section' => $section, 'date' => $date, 'q' => $search ?: null]) }}"
                           class="list-group-item list-group-item-action py-2 @if($plan && $plan->plan_no === $o->plan_no) active @endif">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $o->item_no }}</strong>
                                <small>{{ $o->batches_done }} / {{ $o->batches }} batches</small>
                            </div>
                            <div class="small">{{ $o->description }}</div>
                            <div class="small text-muted">
                                {{ $o->plan_no }} @if($o->released_from) &middot; plan {{ $o->released_from }} @endif
                                &middot; {{ \Illuminate\Support\Carbon::parse($o->plan_date)->format('d M') }} &middot; {{ $o->section }}
                            </div>
                            <div class="progress wg-progress mt-1"><div class="progress-bar bg-success" style="width: {{ $pct }}%"></div></div>
                        </a>
                    @empty
                        <div class="list-group-item text-muted">No open orders. Orders appear once BC sends them (released and pushed in batches).</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- 2. Weigh the batch --}}
        <div class="col-lg-8 mb-3">
            @if(!$plan)
                <div class="card"><div class="card-body text-center text-muted py-5">
                    <i class="fa fa-hand-pointer fa-2x mb-2"></i><br>Select an order on the left.
                </div></div>
            @else
            <div class="card mb-3">
                <div class="card-body py-2">
                    <div class="d-flex flex-wrap align-items-start">
                        <div class="mr-auto">
                            <h5 class="mb-0">{{ $plan->item_no }} {{ $plan->description }}</h5>
                            <div class="small text-muted">
                                BC order <strong>{{ $plan->plan_no }}</strong>
                                &middot; Plan <strong>{{ $plan->released_from ?: '-' }}</strong>
                                &middot; Date <strong>{{ \Illuminate\Support\Carbon::parse($plan->plan_date)->format('d M Y') }}</strong>
                                &middot; {{ $plan->section ?? '-' }} / {{ $plan->location_code }}
                            </div>
                        </div>
                        @if($batches->count())
                        <form method="GET" action="{{ route('planned_orders_weigh') }}" class="form-inline mt-1">
                            <input type="hidden" name="plan" value="{{ $plan->plan_no }}">
                            <label class="small mr-2" for="wgBatch">Batch</label>
                            <select id="wgBatch" name="batch" class="form-control form-control-sm" onchange="this.form.submit()">
                                @foreach($batches as $b)
                                    <option value="{{ $b->batch_code }}" @if($batch && $batch->batch_code === $b->batch_code) selected @endif>
                                        {{ $b->batch_code }} &middot; {{ number_format($b->quantity_base, 2) }} kg{{ $b->wms_status === 'in_progress' ? ' (started)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                        @endif
                    </div>
                </div>
            </div>

            @if(!$batch)
                <div class="alert alert-info">No open batch on this order: every batch is done.</div>
            @else
            <div class="card mb-3 border-success">
                <div class="card-body d-flex flex-wrap align-items-center py-2">
                    <div class="mr-4">
                        <div class="small text-muted">Batch target (output)</div>
                        <div class="h3 mb-0 text-success">{{ number_format($batch->quantity_base, 2) }} kg</div>
                    </div>
                    <div class="small text-muted mr-auto">{{ $batch->batch_code }}<br>{{ $batch->batch_uom === 'BATCH' ? '1 batch = ' . number_format($plan->batch_size_base ?: $batch->quantity_base, 0) . ' kg' : '' }}</div>
                    <button type="button" class="btn btn-success btn-xl js-weigh"><i class="fa fa-weight mr-2"></i>Weigh item</button>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header py-2"><strong>Items</strong> <small class="text-muted">tap a row to weigh it; everything in kg</small></div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Item</th><th>Description</th><th></th><th class="text-right">Target</th><th class="text-right">Weighed</th><th class="text-right">Left</th></tr></thead>
                        <tbody>
                        @foreach($lines as $l)
                            @php $left = $l['target'] !== null ? $l['target'] - $l['weighed'] : null; @endphp
                            <tr class="wg-line-row js-weigh" data-item="{{ json_encode(['item_no' => $l['item_no'], 'description' => $l['description'], 'type' => $l['type'], 'by_product' => (bool) $l['by_product'], 'location_code' => $l['location_code'], 'target' => $l['target']]) }}">
                                <td class="text-nowrap">{{ $l['item_no'] }}</td>
                                <td>{{ $l['description'] }}</td>
                                <td>
                                    @if($l['type'] === 'output')<span class="badge badge-success">Output</span>
                                    @elseif($l['by_product'])<span class="badge badge-warning">By-product</span>
                                    @else<span class="badge badge-light">Input</span>@endif
                                    @if(!empty($l['extra']))<span class="badge badge-info">Not on order</span>@endif
                                </td>
                                <td class="text-right">{{ $l['target'] !== null ? number_format($l['target'], 2) : '-' }}</td>
                                <td class="text-right">{{ number_format($l['weighed'], 2) }} @if($l['count'])<small class="text-muted">({{ $l['count'] }})</small>@endif</td>
                                <td class="text-right {{ $left !== null && $left < 0 ? 'text-danger' : '' }}">{{ $left !== null ? number_format($left, 2) : '' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header py-2 d-flex align-items-center">
                    <strong class="mr-auto">Weighings on this batch</strong>
                    @if($weighings->count())
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#postModal"><i class="fa fa-check mr-1"></i>Post batch</button>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Time</th><th>Item</th><th class="text-right">Gross</th><th>Vessels</th><th class="text-right">Tare</th><th class="text-right">Net kg</th><th>By</th><th></th></tr></thead>
                        <tbody>
                        @forelse($weighings as $w)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($w->created_at)->format('H:i') }}</td>
                                <td>{{ $w->item_no }} <small class="text-muted">{{ $w->description }}</small></td>
                                <td class="text-right">{{ number_format($w->gross_kg, 2) }}@if($w->manual) <span class="badge badge-secondary" title="typed, not read from a scale">typed</span>@endif</td>
                                <td>{{ $w->vessel_count ? $w->vessel_count . ' x ' . $w->vessel_name : '-' }}</td>
                                <td class="text-right">{{ number_format($w->tare_kg, 2) }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($w->net_kg, 2) }}</td>
                                <td>{{ $w->username }}</td>
                                <td><button type="button" class="btn btn-sm btn-link text-danger p-0 js-del" data-url="{{ route('planned_orders_weighing_delete', $w->id) }}" title="Delete this weighing"><i class="fa fa-trash"></i></button></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-muted">Nothing weighed yet on this batch.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
            @endif
        </div>
    </div>
</div>

@if($plan && $batch)
{{-- Weigh: 1. search the item, 2. read the weight, the vessels come off --}}
<div class="modal fade" id="weighModal" tabindex="-1" role="dialog" aria-labelledby="weighModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="weighModalTitle">Which item are you weighing?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="wgStepItem">
                    <input type="search" id="wgSearch" class="form-control form-control-lg mb-2" placeholder="Search item no. or name" autocomplete="off">
                    <div id="wgResults" class="list-group"></div>
                </div>
                <div id="wgStepWeight" class="d-none">
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-auto">
                            <div class="h5 mb-0" id="wgItemName"></div>
                            <div class="small text-muted" id="wgItemMeta"></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="wgChange">Change item</button>
                    </div>
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="wgScale">Scale</label>
                            <div class="input-group">
                                <select id="wgScale" class="form-control">
                                    @forelse($scales as $s)
                                        <option value="{{ $s['url'] }}" data-name="{{ $s['scale'] }}">{{ $s['scale'] }}</option>
                                    @empty
                                        <option value="">No scale set up for {{ $plan->section ?? 'this section' }}</option>
                                    @endforelse
                                </select>
                                <div class="input-group-append"><button type="button" class="btn btn-primary" id="wgRead"><i class="fa fa-sync-alt mr-1"></i>Read scale</button></div>
                            </div>
                            <label class="small mb-1 mt-2" for="wgGross">Gross weight (kg)</label>
                            <input type="number" step="0.01" min="0" id="wgGross" class="form-control wg-gross" @unless($manual) readonly @endunless placeholder="0.00">
                            @if($manual)<small class="text-muted">Read the scale, or type the weight.</small>@endif
                            <div class="text-danger small mt-1" id="wgScaleErr"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="wgVessel">Vessel</label>
                            <select id="wgVessel" class="form-control">
                                <option value="" data-tare="0">No vessel (0 kg)</option>
                                @foreach($vessels as $v)
                                    <option value="{{ $v->id }}" data-tare="{{ $v->tare_kg }}" @if($v->is_default) selected @endif>{{ $v->name }} ({{ rtrim(rtrim(number_format($v->tare_kg, 3), '0'), '.') }} kg)</option>
                                @endforeach
                            </select>
                            @if(!count($vessels))<small class="text-muted">No vessels set up for {{ $plan->section ?? 'this section' }}: <a href="{{ route('planned_orders_vessels') }}">add them</a>.</small>@endif
                            <label class="small mb-1 mt-2" for="wgCount">Number of vessels</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><button type="button" class="btn btn-outline-secondary" data-step="-1">&minus;</button></div>
                                <input type="number" min="0" step="1" id="wgCount" class="form-control text-center" value="1">
                                <div class="input-group-append"><button type="button" class="btn btn-outline-secondary" data-step="1">+</button></div>
                            </div>
                            <div class="mt-3 p-2 bg-light rounded text-right">
                                <div class="small text-muted">Gross <span id="wgGrossShow">0.00</span> &minus; tare <span id="wgTare">0.00</span></div>
                                <div class="small text-muted">Net</div>
                                <div class="wg-net text-success"><span id="wgNet">0.00</span> <small>kg</small></div>
                            </div>
                        </div>
                    </div>
                    <div id="wgMsg" class="small"></div>
                </div>
            </div>
            <div class="modal-footer py-2 d-none" id="wgFooter">
                <button type="button" class="btn btn-outline-success btn-lg" id="wgSaveNext">Save &amp; weigh another</button>
                <button type="button" class="btn btn-success btn-lg" id="wgSave"><i class="fa fa-check mr-1"></i>Save</button>
            </div>
        </div>
    </div>
</div>

{{-- Post the batch: totals per item become one WMS production order, sent to BC now --}}
<div class="modal fade" id="postModal" tabindex="-1" role="dialog" aria-labelledby="postModalTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('planned_orders_batch_post', [$plan->plan_no, $batch->batch_code]) }}" class="modal-content">
            @csrf
            <div class="modal-header py-2"><h5 class="modal-title" id="postModalTitle">Post batch {{ $batch->batch_code }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
            <div class="modal-body">
                <table class="table table-sm">
                    <thead><tr><th>Item</th><th></th><th class="text-right">Net kg</th></tr></thead>
                    <tbody>
                    @foreach(collect($lines)->where('weighed', '>', 0) as $l)
                        <tr><td>{{ $l['item_no'] }} <small class="text-muted">{{ $l['description'] }}</small></td>
                            <td>{{ $l['type'] === 'output' ? 'Output' : ($l['by_product'] ? 'By-product' : 'Input') }}</td>
                            <td class="text-right">{{ number_format($l['weighed'], 2) }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="custom-control custom-checkbox">
                    <input type="hidden" name="batch_completed" value="0">
                    <input type="checkbox" class="custom-control-input" id="postCompleted" name="batch_completed" value="1" checked>
                    <label class="custom-control-label" for="postCompleted">Batch completed (untick if more of this batch will be weighed later)</label>
                </div>
                <p class="small text-muted mt-2 mb-0">This creates WMS order WB{{ $batch->id }} for BC order {{ $plan->plan_no }} (plan {{ $plan->released_from ?: '-' }}, {{ \Illuminate\Support\Carbon::parse($plan->plan_date)->format('d M Y') }}) and sends it to BC now; BC posts it within a minute.</p>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check mr-1"></i>Post batch</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var itemsUrl = @json(route('planned_orders_items', $plan->plan_no));
    var storeUrl = @json(route('planned_orders_weighing_store', [$plan->plan_no, $batch->batch_code]));
    var csrf = @json(csrf_token());
    var manualAllowed = @json($manual);
    var current = null, readFromScale = false, searchTimer = null;
    var $ = window.jQuery, el = function (id) { return document.getElementById(id); };
    var esc = function (v) { var d = document.createElement('div'); d.textContent = v == null ? '' : v; return d.innerHTML; };
    var num = function (v) { var n = parseFloat(v); return isNaN(n) ? 0 : n; };

    function showStep(step) {
        el('wgStepItem').classList.toggle('d-none', step !== 'item');
        el('wgStepWeight').classList.toggle('d-none', step !== 'weight');
        el('wgFooter').classList.toggle('d-none', step !== 'weight');
        el('weighModalTitle').textContent = step === 'item' ? 'Which item are you weighing?' : 'Weigh';
        if (step === 'item') setTimeout(function () { el('wgSearch').focus(); }, 200);
    }
    function recalc() {
        var gross = num(el('wgGross').value);
        var opt = el('wgVessel').selectedOptions[0];
        var tare = num(opt ? opt.dataset.tare : 0) * (el('wgVessel').value ? Math.max(0, parseInt(el('wgCount').value || '0', 10)) : 0);
        el('wgGrossShow').textContent = gross.toFixed(2);
        el('wgTare').textContent = tare.toFixed(2);
        var net = gross - tare;
        el('wgNet').textContent = net.toFixed(2);
        el('wgNet').parentNode.classList.toggle('text-danger', net <= 0 && gross > 0);
        el('wgNet').parentNode.classList.toggle('text-success', !(net <= 0 && gross > 0));
    }
    function pick(item) {
        current = item;
        el('wgItemName').textContent = item.item_no + ' ' + (item.description || '');
        el('wgItemMeta').textContent = (item.type === 'output' ? 'Output' : (item.by_product ? 'By-product' : 'Input'))
            + (item.target != null ? ' · target ' + num(item.target).toFixed(2) + ' kg for this batch' : '')
            + (item.on_order === false ? ' · not on this order' : '');
        el('wgGross').value = ''; readFromScale = false; el('wgMsg').textContent = ''; el('wgScaleErr').textContent = '';
        recalc();
        showStep('weight');
        if (el('wgScale').value) readScale(); else if (manualAllowed) el('wgGross').focus();
    }
    function search() {
        var q = el('wgSearch').value.trim();
        fetch(itemsUrl + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                var h = '';
                d.items.forEach(function (it, i) {
                    var badge = it.type === 'output' ? '<span class="badge badge-success">Output</span>' : (it.by_product ? '<span class="badge badge-warning">By-product</span>' : '<span class="badge badge-light">Input</span>');
                    h += '<button type="button" class="list-group-item list-group-item-action wg-item-btn" data-i="' + i + '">'
                        + '<strong>' + esc(it.item_no) + '</strong> ' + esc(it.description) + ' ' + badge
                        + (it.on_order ? '' : ' <span class="badge badge-info">Item list</span>')
                        + (it.target != null ? '<span class="float-right text-muted small">target ' + num(it.target).toFixed(2) + ' kg</span>' : '') + '</button>';
                });
                el('wgResults').innerHTML = h || '<div class="list-group-item text-muted">No item matches. Type at least 2 letters to search the item list.</div>';
                el('wgResults').querySelectorAll('[data-i]').forEach(function (b) { b.addEventListener('click', function () { pick(d.items[b.dataset.i]); }); });
            });
    }
    function readScale() {
        var url = el('wgScale').value;
        if (!url) { el('wgScaleErr').textContent = 'No scale set up for this section.'; return; }
        var btn = el('wgRead'); btn.disabled = true; el('wgScaleErr').textContent = '';
        var ctrl = new AbortController(); var t = setTimeout(function () { ctrl.abort(); }, 5000);
        fetch(url, { signal: ctrl.signal }).then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) throw new Error('Scale did not return a weight');
                el('wgGross').value = num(d.response).toFixed(2); readFromScale = true; recalc();
            })
            .catch(function (e) { el('wgScaleErr').textContent = (e.name === 'AbortError' ? 'No response from the scale.' : e.message) + (manualAllowed ? ' Type the weight instead.' : ''); })
            .finally(function () { clearTimeout(t); btn.disabled = false; });
    }
    function save(next) {
        if (!current) return;
        var gross = num(el('wgGross').value);
        if (gross <= 0) { el('wgMsg').className = 'small text-danger'; el('wgMsg').textContent = 'Read the scale first.'; return; }
        el('wgSave').disabled = el('wgSaveNext').disabled = true;
        fetch(storeUrl, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({
                item_no: current.item_no, description: current.description, line_type: current.type === 'output' ? 'output' : 'consumption',
                by_product: !!current.by_product, location_code: current.location_code, gross_kg: gross,
                vessel_id: el('wgVessel').value || null, vessel_count: parseInt(el('wgCount').value || '0', 10),
                scale: el('wgScale').selectedOptions[0] ? el('wgScale').selectedOptions[0].dataset.name : null, manual: !readFromScale
            })
        }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
            .then(function (res) {
                if (!res.ok) throw new Error(res.j.message || (res.j.errors ? Object.values(res.j.errors).join(' ') : 'Could not save.'));
                if (window.toastr) toastr.success(res.j.message);
                if (next) { sessionStorage.setItem('wgReopen', '1'); }
                location.reload();
            })
            .catch(function (e) { el('wgMsg').className = 'small text-danger'; el('wgMsg').textContent = e.message; el('wgSave').disabled = el('wgSaveNext').disabled = false; });
    }

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('.js-weigh');
        if (trigger) {
            $('#weighModal').modal('show');
            if (trigger.dataset.item) { var it = JSON.parse(trigger.dataset.item); it.on_order = true; pick(it); }
            else { showStep('item'); el('wgSearch').value = ''; search(); }
            return;
        }
        var del = e.target.closest('.js-del');
        if (del && confirm('Delete this weighing?')) {
            fetch(del.dataset.url, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
                .then(function (r) { return r.json(); }).then(function (d) { if (d.ok) location.reload(); else alert(d.message); });
        }
        var step = e.target.closest('[data-step]');
        if (step) { el('wgCount').value = Math.max(0, parseInt(el('wgCount').value || '0', 10) + parseInt(step.dataset.step, 10)); recalc(); }
    });
    el('wgSearch').addEventListener('input', function () { clearTimeout(searchTimer); searchTimer = setTimeout(search, 200); });
    el('wgSearch').addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); var f = el('wgResults').querySelector('[data-i]'); if (f) f.click(); } });
    el('wgChange').addEventListener('click', function () { showStep('item'); search(); });
    el('wgRead').addEventListener('click', readScale);
    ['wgGross', 'wgCount'].forEach(function (id) { el(id).addEventListener('input', function () { if (id === 'wgGross') readFromScale = false; recalc(); }); });
    el('wgVessel').addEventListener('change', recalc);
    el('wgSave').addEventListener('click', function () { save(false); });
    el('wgSaveNext').addEventListener('click', function () { save(true); });
    try { if (sessionStorage.getItem('wgReopen')) { sessionStorage.removeItem('wgReopen'); $(function () { $('#weighModal').modal('show'); showStep('item'); search(); }); } } catch (e) {}
})();
</script>
@endif
@endsection
