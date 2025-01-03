@extends('layouts.template_master')

@section('navbar')
    @include('layouts.headers.beef_header')
@endsection

@section('content-header')
<h1 class="m-2">
    {{ $title }}
</h1>
@endsection

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header row">
                <div class="col-md-7">
                    <h3 class="card-title">Beef IDT Combined Report | <span id="subtext-h1-title"><small>entries</small> </span></h3>
                </div>
                <div class="col-md-5">
                    <button class="btn btn-success float-right" data-toggle="modal" data-target="#export_data"><i
                            class="fas fa-file-excel"></i>
                        Generate Combined Report</button>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-striped " width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Code</th>
                                <th>Product Description</th>
                                <th>Sent Weight</th>
                                <th>Sent Pieces</th>
                                <th>Received Weight</th>
                                <th>Received Pieces</th>
                                <th>From Location</th>
                                <th>To Location</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Product Code</th>
                                <th>Product Description</th>
                                <th>Sent Weight</th>
                                <th>Sent Pieces</th>
                                <th>Received Weight</th>
                                <th>Received Pieces</th>
                                <th>From Location</th>
                                <th>To Location</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($summary as $transfer)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $transfer->product_code }}</td>
                                <td>{{ $transfer->item_description }}</td>
                                <td>{{ number_format($transfer->sent_weight, 2) }}</td>
                                <td>{{ $transfer->sent_pieces }}</td>
                                <td>{{ number_format($transfer->received_weight, 2) }}</td>
                                <td>{{ $transfer->received_pieces }}</td>
                                <td>CM Despatch</td>
                                <td>Beef Butchery</td>
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
    <!-- /.col -->
</div>


<!-- Start Export combined Modal -->
<div class="modal fade" id="export_data" tabindex="-1" role="dialog" aria-hidden="true">
    <form id="form-orders-export" action="{{ route('beef_combined_export') }}" method="POST" class="form-prevent-multiple-submits">
        @csrf
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Beef Combined Summary Report</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="from_date">From Date:</label>
                            <input type="date" class="form-control" name="from_date"
                                id="stemplate_date_created_from_flagged" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="to_date">To Date:</label>
                            <input type="date" class="form-control" name="to_date"
                                id="stemplate_date_created_from_flagged" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary float-left" type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-lg  float-right  btn-prevent-multiple-submits"><i class="fa fa-send"></i>
                        Export</button>
                </div>
            </div>
        </div>
    </form>
</div>
<!-- End Export combined Modal -->

@endsection
