@extends('layouts.butchery_master')

@section('content-header')
<div class="container">

@section('content')
<div class="row mb-2">
    <div class="col-md-12">
        <h4 class="m-0"> {{ $title }} |<small> Showing entries for the last <strong>{{ $daysFilter }}</strong> days </small></h4>
    </div><!-- /.col -->
    <div class="btn">
        <a href="{{ route('asset_list') }}" class="btn btn-primary">Export List</a>
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#movementExportModal">
            <i class="fas fa-file-excel"></i> Export Movement History
        </button>
    </div>
</div><!-- /.row -->

<div class="modal fade" id="movementExportModal" tabindex="-1" role="dialog" aria-labelledby="movementExportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('movement_history_export') }}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="movementExportModalLabel">Export Movement History</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Select the date range to export.</p>
                    <div class="form-group">
                        <label for="movement_export_from_date">From</label>
                        <input type="date" class="form-control" id="movement_export_from_date" name="from_date"
                            value="{{ old('from_date') }}" required>
                        @error('from_date')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="movement_export_to_date">To</label>
                        <input type="date" class="form-control" id="movement_export_to_date" name="to_date"
                            value="{{ old('to_date') }}" required>
                        @error('to_date')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Download Excel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table table-striped table-bordered table-hover" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fa </th>
                                <th>Description </th>
                                <th>Narration</th>
                                <th>From Dept </th>
                                <th>From Employee </th>
                                <th>Assigned Employee</th>
                                <th>Employee Dept</th>
                                <th>Created By</th>
                                <th>Authenticated username</th>
                                <th>Status</th>
                                <th>Date </th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Fa </th>
                                <th>Description </th>
                                <th>Narration</th>
                                <th>From Dept </th>
                                <th>From Employee </th>
                                <th>Assigned Employee</th>
                                <th>Employee Dept</th>
                                <th>Created By</th>
                                <th>Authenticated username</th>
                                <th>Status</th>
                                <th>Date </th>
                        </tfoot>
                        <tbody>
                            @foreach($data as $e)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $e->fa }}</td>
                                    <td>{{ $e->description }}</td>
                                    <td style="white-space: pre-wrap; min-width: 250px;">{{ $e->narration }}</td>
                                    <td>{{ $e->from_dept }}</td>
                                    <td>{{ $e->from_user }}</td>
                                    <td>{{ $e->to_user }}</td>
                                    <td>{{ $e->to_dept }}</td>
                                    <td>{{ $e->username }}</td>
                                    <td>{{ $e->authenticated_username }}</td>
                                    <td>
                                        @if ($e->status == 2)
                                        <span class="badge badge-danger">cancelled</span>
                                        @else
                                         <span class="badge badge-success">unedited</span>   
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($e->created_at)->format('d/m/Y H:i') }}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
        <!-- /.col -->
    </div>
</div><br>

@endsection
</div><!-- /.container-fluid -->

@endsection

@section('scripts')
@if ($errors->has('from_date') || $errors->has('to_date'))
<script>
    $(function () {
        $('#movementExportModal').modal('show');
    });
</script>
@endif
@endsection
