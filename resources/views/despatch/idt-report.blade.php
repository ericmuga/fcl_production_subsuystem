@extends('layouts.despatch_master')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Despatch Production Registry | showing <strong>{{ $filter }}</strong> Entries</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table table-striped table-bordered table-hover" style="scroll-behavior: smooth;">
                        <thead>
                            <tr>
                                <th>IDT No</th>
                                <th>Product Code</th>
                                <th>Product</th>
                                {{-- <th>Std Crate Count</th>
                                <th>Std Unit Measure</th> --}}
                                <th>Location </th>
                                {{-- <th>Chiller</th> 
                                <th>Issued Total Crates</th>
                                <th>Issued Full Crates</th>
                                <th>Issued Incomplete Crate Pieces</th> --}}
                                <th>Issued Total Pieces</th>
                                <th>Issued Total Weight</th>
                                {{-- <th>Received Total Crates</th>
                                <th>Received Full Crates</th>
                                <th>Received Incomplete Crate Pieces</th> --}}
                                <th>Received Total Pieces</th>
                                <th>Received Total Weight</th>
                                <th>Received By</th>
                                <th>Export No</th>
                                <th>Batch No</th>
                                <th>Date</th>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>IDT No</th>
                                <th>Product Code</th>
                                <th>Product</th>
                                {{-- <th>Std Crate Count</th>
                                <th>Std Unit Measure</th> --}}
                                <th>Location </th>
                                {{-- <th>Chiller</th> 
                                <th>Issued Total Crates</th>
                                <th>Issued Full Crates</th>
                                <th>Issued Incomplete Crate Pieces</th> --}}
                                <th>Issued Total Pieces</th>
                                <th>Issued Total Weight</th>
                                {{-- <th>Received Total Crates</th>
                                <th>Received Full Crates</th>
                                <th>Received Incomplete Crate Pieces</th> --}}
                                <th>Received Total Pieces</th>
                                <th>Received Total Weight</th>
                                <th>Received By</th>
                                <th>Export No</th>
                                <th>Batch No</th>
                                <th>Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($transfer_lines as $data)
                            <tr>
                                <td>{{ $data->id }}</td>
                                <td>{{ $data->product_code }}</td>
                                <td>{{ $data->product }}</td>
                                {{-- <td>{{ $data->unit_count_per_crate }}</td>
                                <td>{{ number_format($data->qty_per_unit_of_measure, 2) }}</td> --}}
                                <td>{{ $data->location_code }}</td>
                                {{-- <td>{{ $data->chiller_code }}</td>
                                <td>{{ $data->total_crates }}</td>
                                <td>{{ $data->full_crates }}</td>
                                <td>{{ number_format($data->incomplete_crate_pieces, 1) }}</td> --}}
                                <td>{{ $data->total_pieces }}</td>
                                <td>{{ $data->total_weight }}</td>
                                {{-- <td>{{ number_format($data->receiver_total_crates, 1) }}</td>
                                <td>{{ number_format($data->receiver_full_crates, 1) }}</td>
                                <td>{{ number_format($data->receiver_incomplete_crate_pieces, 1) }}</td> --}}
                                <td>{{ number_format($data->receiver_total_pieces, 1) }}</td>
                                <td>{{ number_format($data->receiver_total_weight, 1) }}</td>
                                <td>{{ $data->username }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ $data->batch_no }}</td>
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

