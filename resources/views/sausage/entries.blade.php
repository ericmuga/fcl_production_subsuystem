@extends('layouts.sausage_master')

@section('content')

<div class="col-md-12 text-left" style="margin-bottom: 1%">
    <button class="btn btn-success btn-lg" data-toggle="collapse" data-target="#export_data"><i
            class="fas fa-file-excel"></i> Export History Data</button>
    <div id="export_data" class="collapse"><br>
        <div class="form-inputs">
            <div class="row">
                <div class="col-lg-6" style="margin: 0 auto; float: none;">
                    <div class="card mb-3">
                        <div class="card-header">
                            <i class="fa fa-user-secret"></i>
                            Export data</div>
                        <div class="card-body">
                            <form action="{{ route('export_sausage_entries') }}" method="post"
                                id="export-logs-form">
                                @csrf

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
                                </div> <br>
                                <div class="div" align="center">
                                    <button type="submit" class="btn btn-primary "><i class="fa fa-paper-plane"
                                            aria-hidden="true"></i> Export now</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sausage Production Registry | showing <strong>{{ $filter? : "All" }} </strong>Entries for today</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table table-striped table-bordered table-hover" style="scroll-behavior: smooth;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Barcode</th>
                                <th>Item Code </th>
                                <th>Item description</th>
                                <th>Entry Count</th>
                                <th>Qty Per Unit Measure</th>
                                <th>Tonnage (kgs)</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Barcode</th>
                                <th>Item Code </th>
                                <th>Item description</th>
                                <th>Entry Count</th>
                                <th>Qty Per Unit Measure</th>
                                <th>Tonnage (kgs)</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($entries as $data)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $data->barcode }}</td>
                                <td>{{ $data->code }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ $data->total_count }}</td>
                                <td>{{ number_format($data->qty_per_unit_of_measure, 2) }}</td>
                                <td>{{ number_format($data->total_count * $data->qty_per_unit_of_measure, 2) }}</td>
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
@endsection

