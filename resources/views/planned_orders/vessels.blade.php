@extends('layouts.master')

@section('content')
<div class="container-fluid pt-3">
    <p class="mb-2"><a href="{{ route('planned_orders_weigh') }}">&larr; Weigh planned orders</a></p>
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mb-0 mr-auto"><i class="fa fa-box mr-2"></i>Vessels <small class="text-muted">tare per section</small></h3>
            @if($canEdit)
                <button type="button" class="btn btn-sm btn-primary js-vessel" data-vessel="{{ json_encode(['id' => null, 'section' => 'All', 'name' => '', 'tare_kg' => 0, 'is_default' => false, 'active' => true]) }}">
                    <i class="fa fa-plus mr-1"></i>Add vessel
                </button>
            @endif
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead><tr><th>Section</th><th>Vessel</th><th class="text-right">Tare (kg)</th><th>Default</th><th>Active</th><th>Changed</th><th></th></tr></thead>
                <tbody>
                @forelse($vessels as $v)
                    <tr class="{{ $v->active ? '' : 'text-muted' }}">
                        <td>{{ $v->section }}</td>
                        <td>{{ $v->name }}</td>
                        <td class="text-right">{{ number_format($v->tare_kg, 3) }}</td>
                        <td>{!! $v->is_default ? '<span class="badge badge-success">Default</span>' : '' !!}</td>
                        <td>{{ $v->active ? 'Yes' : 'No' }}</td>
                        <td class="small">{{ $v->username }} {{ $v->updated_at ? \Illuminate\Support\Carbon::parse($v->updated_at)->format('d M Y H:i') : '' }}</td>
                        <td>
                            @if($canEdit)
                                <button type="button" class="btn btn-sm btn-link p-0 js-vessel" data-vessel="{{ json_encode(['id' => $v->id, 'section' => $v->section, 'name' => $v->name, 'tare_kg' => (float) $v->tare_kg, 'is_default' => (bool) $v->is_default, 'active' => (bool) $v->active]) }}">Edit</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">No vessels yet. Add the crates, trays and trolleys each section weighs in; the weighing screen takes their weight off the scale reading.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer small text-muted">
            A vessel set to section "All" is offered everywhere. The default vessel is preselected when weighing.
            @unless($canEdit) Only {{ implode(', ', config('bc_plan.vessel_admin_roles', ['admin'])) }} users can change vessels. @endunless
        </div>
    </div>
</div>

@if($canEdit)
<div class="modal fade" id="vesselModal" tabindex="-1" role="dialog" aria-labelledby="vesselModalTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('planned_orders_vessels_save') }}" class="modal-content">
            @csrf
            <input type="hidden" name="id" id="vId">
            <div class="modal-header py-2"><h5 class="modal-title" id="vesselModalTitle">Vessel</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="vSection">Section</label>
                    <select name="section" id="vSection" class="form-control">
                        @foreach($sections as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="vName">Name</label>
                    <input type="text" name="name" id="vName" class="form-control" maxlength="50" required placeholder="e.g. Blue crate">
                </div>
                <div class="form-group">
                    <label for="vTare">Tare (kg)</label>
                    <input type="number" name="tare_kg" id="vTare" class="form-control" step="0.001" min="0" required>
                </div>
                <div class="custom-control custom-checkbox">
                    <input type="hidden" name="is_default" value="0">
                    <input type="checkbox" class="custom-control-input" id="vDefault" name="is_default" value="1">
                    <label class="custom-control-label" for="vDefault">Default for the section</label>
                </div>
                <div class="custom-control custom-checkbox">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" class="custom-control-input" id="vActive" name="active" value="1">
                    <label class="custom-control-label" for="vActive">Active</label>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('click', function (e) {
    var b = e.target.closest('.js-vessel');
    if (!b) return;
    var v = JSON.parse(b.dataset.vessel);
    document.getElementById('vId').value = v.id || '';
    document.getElementById('vSection').value = v.section;
    document.getElementById('vName').value = v.name;
    document.getElementById('vTare').value = v.tare_kg;
    document.getElementById('vDefault').checked = !!v.is_default;
    document.getElementById('vActive').checked = !!v.active;
    document.getElementById('vesselModalTitle').textContent = v.id ? 'Edit vessel' : 'Add vessel';
    window.jQuery('#vesselModal').modal('show');
});
</script>
@endif
@endsection
