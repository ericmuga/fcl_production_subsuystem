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
                    + '<div class="col-md-2 col-6"><span class="text-muted">Location</span><br>' + esc(p.location_code) + '</div>'
                    + '<div class="col-md-2 col-6"><span class="text-muted">Planned</span><br>' + num(p.planned_qty) + ' ' + esc(p.uom) + '</div>'
                    + '<div class="col-md-2 col-6"><span class="text-muted">' + (p.status === 'firm_planned' ? 'Planned order' : 'Released from') + '</span><br>'
                    + esc(p.status === 'firm_planned' ? p.plan_no : (p.released_from || '-')) + '</div></div>'
                    + '<p class="text-muted small">' + esc(notes[p.status] || '') + '</p>';
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
