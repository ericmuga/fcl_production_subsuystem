@extends('layouts.master')

@section('content')
<div class="container-fluid pt-3">
    <p class="mb-2"><a href="{{ route('home') }}">&larr; Dashboard</a></p>
    @include('planned_orders._dashboard', ['pp' => $pp, 'ppAction' => route('planned_orders')])
</div>

{{-- Order details: firm planned, released or open; loaded on demand --}}
<div class="modal fade" id="planModal" tabindex="-1" role="dialog" aria-labelledby="planModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="planModalTitle">Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="planModalBody">Loading...</div>
            <div class="modal-footer">
                <a href="#" class="btn btn-success d-none" id="planModalFulfil">Fulfil next batch</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var esc = function (v) { var d = document.createElement('div'); d.textContent = v == null ? '' : v; return d.innerHTML; };
    var num = function (v) { return Number(v || 0).toLocaleString(undefined, { maximumFractionDigits: 3 }); };
    var label = function (s) { return String(s || '').replace('_', ' ').replace(/^./, function (c) { return c.toUpperCase(); }); };
    var notes = {
        firm_planned: 'Firm planned in BC. It becomes a WMS order once the planner releases it and pushes it to WMS in batches.',
        released: 'Released in BC, not pushed to WMS yet: no batches to fulfil.',
        open: 'Open in WMS: fulfil it batch by batch.',
        closed: 'Closed: later weighings for this order are rejected.'
    };

    // Change / reset the order's location from the modal; the list reloads to show it.
    var saveLocation = function (form, payload) {
        var msg = form.querySelector('.js-plan-location-msg');
        msg.className = 'w-100 small mt-1 js-plan-location-msg text-muted';
        msg.textContent = 'Saving...';
        fetch(form.dataset.url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(payload)
        })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
            .then(function (res) {
                msg.className = 'w-100 small mt-1 js-plan-location-msg ' + (res.ok ? 'text-success' : 'text-danger');
                msg.textContent = res.j.message || (res.j.errors ? Object.values(res.j.errors).join(' ') : 'Could not save.');
                if (res.ok) {
                    $('#planModal').one('hidden.bs.modal', function () { location.reload(); });
                    setTimeout(function () { $('#planModal').modal('hide'); }, 900);
                }
            })
            .catch(function () { msg.className = 'w-100 small mt-1 js-plan-location-msg text-danger'; msg.textContent = 'Could not save the location.'; });
    };
    document.addEventListener('submit', function (e) {
        var form = e.target.closest('.js-plan-location');
        if (!form) return;
        e.preventDefault();
        saveLocation(form, { location_code: form.querySelector('select').value });
    });
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-plan-location-reset');
        if (!btn) return;
        saveLocation(btn.closest('form'), { reset: true });
    });

    document.addEventListener('click', function (e) {
        var link = e.target.closest('.js-plan-modal');
        if (!link) return;
        e.preventDefault();
        var body = document.getElementById('planModalBody');
        var fulfil = document.getElementById('planModalFulfil');
        body.textContent = 'Loading...';
        fulfil.classList.add('d-none');
        $('#planModal').modal('show');
        fetch(link.dataset.url, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(function (d) {
                var p = d.plan;
                document.getElementById('planModalTitle').textContent = p.plan_no + ' · ' + p.item_no + ' ' + (p.description || '');
                var h = '<div class="row small mb-2">'
                    + '<div class="col-md-2 col-6"><span class="text-muted">Status</span><br>' + esc(label(p.status)) + '</div>'
                    + '<div class="col-md-2 col-6"><span class="text-muted">Date</span><br>' + esc(p.plan_date) + '</div>'
                    + '<div class="col-md-2 col-6"><span class="text-muted">Section</span><br>' + esc(p.section || '-') + '</div>'
                    + '<div class="col-md-2 col-6"><span class="text-muted">Location</span><br>' + esc(p.location_code)
                    + (d.location.overridden ? ' <span class="badge badge-warning" title="BC location: ' + esc(d.location.bc) + '">moved</span>' : '') + '</div>'
                    + '<div class="col-md-2 col-6"><span class="text-muted">Planned</span><br>' + num(p.planned_qty) + ' ' + esc(p.uom) + '</div>'
                    + '<div class="col-md-2 col-6"><span class="text-muted">' + (p.status === 'firm_planned' ? 'Planned order' : 'Released from') + '</span><br>'
                    + esc(p.status === 'firm_planned' ? p.plan_no : (p.released_from || '-')) + '</div></div>'
                    + '<p class="text-muted small">' + esc(notes[p.status] || '') + '</p>';
                if (d.location.editable) {
                    var opts = Object.keys(d.location.options);
                    if (opts.indexOf(p.location_code) < 0) opts.unshift(p.location_code);
                    h += '<form class="form-inline border rounded p-2 mb-3 js-plan-location" data-url="' + esc(d.location.url) + '">'
                        + '<label class="small mr-2 mb-0" for="planLocation">Location</label>'
                        + '<select id="planLocation" name="location_code" class="form-control form-control-sm mr-2">'
                        + opts.map(function (c) {
                            var n = d.location.options[c] || '';
                            return '<option value="' + esc(c) + '"' + (c === p.location_code ? ' selected' : '') + '>'
                                + esc(c + (n ? ' - ' + n : '') + (c === d.location.bc ? ' (BC)' : '')) + '</option>';
                        }).join('')
                        + '</select>'
                        + '<button type="submit" class="btn btn-sm btn-primary mr-2">Save location</button>'
                        + (d.location.overridden ? '<button type="button" class="btn btn-sm btn-outline-secondary mr-2 js-plan-location-reset">Reset to BC (' + esc(d.location.bc) + ')</button>' : '')
                        + '<small class="text-muted">'
                        + (d.location.overridden ? 'Moved' + (d.location.changed_by ? ' by ' + esc(d.location.changed_by) : '') + (d.location.changed_at ? ' on ' + esc(String(d.location.changed_at).substring(0, 16)) : '') + '. ' : '')
                        + 'Output, components at the BC location and batches not yet done follow; BC syncs keep it.</small>'
                        + '<div class="w-100 small mt-1 js-plan-location-msg"></div></form>';
                }
                h += '<h6>Components</h6><table class="table table-sm table-striped"><thead><tr><th>Item</th><th>Description</th><th>Location</th>'
                    + '<th class="text-right">Qty per</th><th class="text-right">Expected</th><th>Unit</th><th></th></tr></thead><tbody>';
                if (!d.components.length) h += '<tr><td colspan="7" class="text-muted">No components.</td></tr>';
                d.components.forEach(function (c) {
                    h += '<tr><td>' + esc(c.item_no) + '</td><td>' + esc(c.description) + '</td><td>' + esc(c.location_code) + '</td>'
                        + '<td class="text-right">' + num(c.qty_per) + '</td><td class="text-right">' + num(c.expected_qty) + '</td><td>' + esc(c.uom) + '</td>'
                        + '<td>' + (Number(c.is_by_product) ? '<span class="badge badge-warning">By-product</span>' : '') + '</td></tr>';
                });
                h += '</tbody></table>';
                if (d.batches.length) {
                    h += '<h6>Batches</h6><table class="table table-sm"><thead><tr><th>Batch</th><th class="text-right">Size</th>'
                        + '<th class="text-right">Posted in BC</th><th>WMS order</th><th>WMS</th><th>BC</th></tr></thead><tbody>';
                    d.batches.forEach(function (b) {
                        h += '<tr><td>' + esc(b.batch_code) + '</td><td class="text-right">' + num(b.quantity_base) + ' ' + esc(b.base_uom) + '</td>'
                            + '<td class="text-right">' + num(b.bc_executed_qty_base) + '</td><td>' + esc(b.wms_order_no) + '</td>'
                            + '<td>' + esc(label(b.wms_status)) + '</td><td>' + esc(label(b.bc_status)) + '</td></tr>';
                    });
                    h += '</tbody></table>';
                }
                body.innerHTML = h;
                if (d.fulfil_url) { fulfil.href = d.fulfil_url; fulfil.classList.remove('d-none'); }
            })
            .catch(function () { body.textContent = 'Could not load the order.'; });
    });
})();
</script>
@endsection
