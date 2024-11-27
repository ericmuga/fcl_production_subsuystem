@extends('layouts.freshcuts-bulk_master')

@section('content')

<button class="btn btn-success btn-lg mb-4" data-toggle="modal" data-target="#export_data">
    <i class="fas fa-file-excel"></i> Export History Data
</button>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"> Transfer Lines Entries | <span id="subtext-h1-title"><small> showing
                            <strong>{{ $range_filter }}</strong> entries
                            ordered by
                            latest</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example1" class="table display nowrap table-striped table-bordered table-hover"
                        width="100%">
                        <thead>
                            <tr>
                                <th>IDT No</th>
                                <th>Product Code</th>
                                <th>Product</th>
                                <th>Transfer To </th>
                                <th>Issued Pieces</th>
                                <th>Issued Weight</th>
                                <th>Received Pieces</th>
                                <th>Received Weight</th>
                                <th>Export No</th>
                                <th>Batch No</th>
                                <th>Created By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>IDT No</th>
                                <th>Product Code</th>
                                <th>Product</th>
                                <th>Transfer To </th>
                                <th>Issued Pieces</th>
                                <th>Issued Weight</th>
                                <th>Received Pieces</th>
                                <th>Received Weight</th>
                                <th>Export No</th>
                                <th>Batch No</th>
                                <th>Created By</th>
                                <th>Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($transfer_lines as $data)
                            <tr>
                                <td>{{ $data->id }}</td>
                                <td>{{ $data->product_code }}</td>
                                <td>{{ $data->product ?? $data->product2 }}</td>
                                <td>{{ $data->location_code }}</td>
                                <td>{{ $data->total_pieces }}</td>
                                <td>{{ $data->total_weight }}</td>
                                <td>{{ number_format($data->receiver_total_pieces, 2) }}</td>
                                <td>{{ number_format($data->receiver_total_weight, 2) }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ $data->batch_no }}</td>
                                <td>{{ $data->username }}</td>
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


<div id="export_data" class="modal">
    <div class="modal-dialog">
        <form class="modal-content" action="{{ route('fresh_idt_report') }}" method="post" id="export-logs-form">
            @csrf
            <div class="modal-header">
                <h4 class="modal-title">Export Data</h4>
            </div>
            <div class="modal-body">
                    <h6>*Filter by date range</h6>
                    <div class="row form-group">
                        <div class="col-md-6">
                            <label for="stemplate_date_created_from_flagged">From: (dd/mm/yyyy)</label>
                            <input type="date" class="form-control" name="from_date"
                                id="stemplate_date_created_from_flagged" required>
                        </div>
                        <div class="col-md-6">
                            <label for="stemplate_date_created_from_flagged">To: (dd/mm/yyyy)</label>
                            <input type="date" class="form-control" name="to_date"
                                id="stemplate_date_created_from_flagged" required>
                        </div>
                        
                    </div>
                    <div class="form-group">
                        <label for="stemplate_date_created_from_flagged">Transfer To</label>
                        <select class="form-control select2" name="transfer_to"
                            id="transfer_from" required>
                            <option disabled selected value> -- select an option -- </option>
                            <option value="1570">Butchery</option>
                            <option value="2055">Sausage</option>
                            <option value="2595">Bacon & Ham</option>
                            <option value="2500">Bacon & Ham Bulk</option>
                        </select>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary ">
                    <i class="fa fa-paper-plane" aria-hidden="true"></i> Export now
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

