@extends('layouts.sausage_master')

@section('content')

<!-- Start Export combined Modal -->
<div class="modal fade" id="export_data" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <form id="form-stuffing-history-export" action="{{ route('stuffing_weights_history_export') }}" method="post">
        @csrf
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Export Stuffing Weights History</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    *Filter by date (format:dd/mm/yyyy)
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="stuffing_history_from_date">From:</label>
                            <input type="date" class="form-control" name="from_date"
                                id="stuffing_history_from_date" autofocus required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="stuffing_history_to_date">To:</label>
                            <input type="date" class="form-control" name="to_date"
                                id="stuffing_history_to_date" autofocus required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary float-left" type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-lg  float-right"><i class="fas fa-paper-plane"></i>
                        Export</button>
                </div>
            </div>
        </div>
    </form>
</div>
<!-- End Export combined Modal -->

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="card-title"> Stuffing Weights History | Showing cumulative totals for the
                            <strong>last 7 days</strong> ({{ $from_date->format('d/m/Y') }} - {{ $to_date->format('d/m/Y') }})</h3>
                    </div>
                    <div class="col-md-5">
                        <button class="btn btn-success" data-toggle="modal" data-target="#export_data"><i
                                class="fas fa-file-excel"></i>
                            Export by Date Range</button>
                    </div>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example1" class="table table-striped table-bordered table-hover" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Code</th>
                                <th>Description</th>
                                <th>Entries</th>
                                <th>Total Weight</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Product Code</th>
                                <th>Description</th>
                                <th>Entries</th>
                                <th>Total Weight</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($summary as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->product_code }}</td>
                                    <td>{{ $data->description }}</td>
                                    <td>{{ $data->entries }}</td>
                                    <td>{{ number_format($data->total_weight, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
</div>

@endsection
