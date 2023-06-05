@extends('layouts.spices_master')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="card-title"> Chopping Posted Production Summary Report <span
                                id="subtext-h1-title"></h3>
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
                                <th>Item Code </th>
                                <th>Description </th>
                                <th>Used Qty</th>
                                <th>Main Product</th>
                                <th>Type</th>
                                <th>Unit Measure</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Item Code </th>
                                <th>Description </th>
                                <th>Used Qty</th>
                                <th>Main Product</th>
                                <th>Type</th>
                                <th>Unit Measure</th>
                                <th>Location</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($lines as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->item_code }}</td>
                                    <td>{{ $data->description }}</td>
                                    <td>{{ number_format($data->used_quantity, 2) }}</td>
                                    @if($data->main_product == 'No')
                                        <td><span class="badge badge-warning">No</span></td>
                                    @else
                                        <td><span class="badge badge-success">Yes</span></td>
                                    @endif

                                    @if($data->type == 'Intake')
                                        <td><span class="badge badge-warning">Intake</span></td>
                                    @else
                                        <td><span class="badge badge-success">Output</span></td>
                                    @endif

                                    <td>{{ $data->unit_measure }}</td>
                                    <td>{{ $data->location }}</td>
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
