@extends('layouts.spices_master')

@section('content')

<!-- Start Export combined Modal -->
<div class="modal fade" id="export_data" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <form id="form-orders-export" action="{{ route('chopping_v2_export') }}" method="post">
        @csrf
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Export Lines Data</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    *Filter by date (format:dd/mm/yyyy)
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="stemplate_date_created_from_flagged">From:</label>
                            <input type="date" class="form-control" name="from_date"
                                id="stemplate_date_created_from_flagged" autofocus required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="stemplate_date_created_from_flagged">To:</label>
                            <input type="date" class="form-control" name="to_date"
                                id="stemplate_date_created_from_flagged" autofocus required>
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
                        <h3 class="card-title"> Chopping runs Report registry | Showing <strong>Today's</strong> Entries <span id="subtext-h1-title">
                        </h3>
                    </div>
                    <div class="col-md-5">
                        <button class="btn btn-success" data-toggle="modal" data-target="#export_data"><i
                                class="fas fa-file-excel"></i>
                            Export Posted Lines</button>
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
                                <th>Chopping No</th>
                                <th>Template Name</th>
                                <th>Item No</th>
                                <th>Item Name</th>
                                <th>Item Type</th>
                                <th>Weight</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Chopping No</th>
                                <th>Template Name</th>
                                <th>Item No</th>
                                <th>Item Name</th>
                                <th>Item Type</th>
                                <th>Weight</th>
                                <th>Timestamp</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($lines as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->chopping_id }}</td>
                                    <td>{{ $data->template_name }}</td>
                                    <td>{{ $data->item_code }}</td>
                                    <td>{{ $data->description }}</td>
                                    <td>
                                        <span class="badge {{ $data->output == 1 ? 'badge-info' : 'badge-success' }}">
                                            {{ $data->output == 1 ? 'Output' : 'Input' }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($data->weight, 2) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($data->created_at)->format('d/m/Y H:i') }}</td>
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
</div>
<!-- slicing ouput data show -->

@endsection
