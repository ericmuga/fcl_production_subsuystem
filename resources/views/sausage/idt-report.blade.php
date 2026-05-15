@extends('layouts.sausage_master')

@section('content')

<div class="row">
    <div class="col-md-6">
        <div class="col-md-12 text-left" style="margin-bottom: 1%">
            <button class="btn btn-success btn-lg" data-toggle="collapse" data-target="#export_data"><i
                    class="fas fa-file-excel"></i> Export History Data</button>
            <div id="export_data" class="collapse"><br>
                <div class="form-inputs">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <i class="fa fa-user-secret"></i>
                                    Export data</div>
                                <div class="card-body">
                                    <form action="{{ route('export_idt_history') }}" method="post" id="export-logs-form">
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
                                        </div>
                                        <div class="row form-group">
                                            <div class="col-md-6">
                                                <label for="stemplate_date_created_from_flagged">Transfer From</label>
                                                <select class="form-control select2" name="transfer_from"
                                                    id="transfer_from" required>
                                                    <option disabled selected value> -- select an option -- </option>
                                                    <option value="1570">Butchery</option>
                                                    <option value="2055">Sausage</option>
                                                    <option value="2595">Bacon & Ham</option>
                                                    <option value="2500">Bacon & Ham Curing</option>
                                                    <option value="3535">Despatch</option>
                                                    <option value="3035">Petfood</option>
                                                    <option value="3555">old Factory</option>
                                                    <option value="4450">QA</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="stemplate_date_created_from_flagged">Transfer To</label>
                                                <select class="form-control select2" name="transfer_to"
                                                    id="transfer_to" required>
                                                    <option disabled selected value> -- select an option -- </option>
                                                    <option value="1570">Butchery</option>
                                                    <option value="2055">Sausage</option>
                                                    <option value="2595">Bacon & Ham</option>
                                                    <option value="2500">Bacon & Ham Curing</option>
                                                    <option value="3535">Despatch</option>
                                                    <option value="3035">Petfood</option>
                                                    <option value="3555">old Factory</option>
                                                    <option value="3540"> Third Party</option>
                                                    <option value="4450"> QA</option>
                                                    <option value="4300"> Incineration</option>
                                                    <option value="4400"> kitchen Staff meals</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 text-center">
                                                <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"
                                                        aria-hidden="true"></i> Export Lines</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
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
                <h3 class="card-title">Sausage Production Registry | showing <strong>{{ $filter }}</strong> Entries</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table display nowrap table-striped table-bordered table-hover" width="100%">
                        <thead>
                            <tr>
                                <th>IDT No</th>
                                <th>Product Code</th>
                                <th>Product</th>
                                <th>Std Crate Count</th>
                                <th>Std Unit Measure</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Chiller</th>
                                <th>Total Crates</th>
                                <th>Full Crates</th>
                                <th>Incomplete Crate Pieces</th>
                                <th>Issued Pieces</th>
                                <th>Issued Weight</th>
                                <th>Received Pieces</th>
                                <th>Received Weight</th>
                                <th>Batch No</th>
                                <th>Received By</th>
                                <th>Export No</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>IDT No</th>
                                <th>Product Code</th>
                                <th>Product</th>
                                <th>Std Crate Count</th>
                                <th>Std Unit Measure</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Chiller</th>
                                <th>Total Crates</th>
                                <th>Full Crates</th>
                                <th>Incomplete Crate Pieces</th>
                                <th>Issued Pieces</th>
                                <th>Issued Weight</th>
                                <th>Received Pieces</th>
                                <th>Received Weight</th>
                                <th>Batch No</th>
                                <th>Received By</th>
                                <th>Export No</th>
                                <th>Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($transfer_lines as $data)
                            <tr>
                                <td>{{ $data->id }}</td>
                                <td>{{ $data->product_code }}</td>
                                <td>{{ $data->product?? $data->product2 ?? $data->template_output }}</td>
                                <td>{{ $data->unit_count_per_crate ?? 0 }}</td>
                                <td>{{ number_format($data->qty_per_unit_of_measure, 2) }}</td>
                                <td>{{ $data->transfer_from }}</td>
                                <td>{{ $data->location_code }}</td>
                                <td>{{ $data->chiller_code }}</td>
                                <td>{{ $data->total_crates }}</td>
                                <td>{{ $data->full_crates }}</td>
                                <td>{{ $data->incomplete_crate_pieces }}</td>
                                <td>{{ $data->total_pieces }}</td>
                                <td>{{ $data->total_weight }}</td>
                                <td>{{ number_format($data->receiver_total_pieces, 2) }}</td>
                                <td>{{ number_format($data->receiver_total_weight, 2) }}</td>
                                <td>{{ $data->batch_no }}</td>
                                @if($data->username )
                                    <td>{{ $data->username }}</td>
                                @else
                                    <td>
                                        <span class="badge badge-warning">pending receipt</span>
                                    </td>
                                @endif
                                <td>{{ $data->description }}</td>
                                <td>{{ $helpers->amPmDate($data->created_at) }}</td>
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

