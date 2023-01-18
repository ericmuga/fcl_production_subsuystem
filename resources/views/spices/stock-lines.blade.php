@extends('layouts.spices_master')

@section('content')

<div class="row">
    <div class="col-md-7">
        <h3>Stocks Lines Registry </h3>
    </div>
</div><br>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item No</th>
                                <th>Desc</th>
                                <th>Quantity</th>
                                <th>Unit Measure</th>
                                <th>Entry Type</th>
                                <th>Unit Measure </th>
                                <th>Created By</th>
                                <th>Physical Stock Ref </th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Item No</th>
                                <th>Desc</th>
                                <th>Quantity</th>
                                <th>Unit Measure</th>
                                <th>Entry Type</th>
                                <th>Unit Measure </th>
                                <th>Created By</th>
                                <th>Physical Stock Ref </th>
                                <th>Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($lines as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->item_code }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ number_format($data->quantity, 2) }}</td>
                                <td>{{ $data->unit_measure }}</td>
                                @if ($data->entry_type == 1)
                                    <td><span class="badge badge-primary">Transfer</span></td>
                                @elseif($data->entry_type == 2)
                                    <td><span class="badge badge-success">Consumption</span></td>
                                @elseif($data->entry_type == 3)
                                    <td><span class="badge badge-warning">Physical Stock</span></td>
                                @endif  
                                <td>{{ $data->unit_measure }}</td>
                                <td>{{ $data->user }}</td>
                                <td>{{ $data->physical_stock_ref }}</td>
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
