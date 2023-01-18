@extends('layouts.spices_master')

@section('content')

<div class="row">
    <div class="col-md-7">
        <h3>Stocks List Registry </h3>
    </div>
    <div class="col-md-5">
        {{-- <button class="btn btn-primary" id="createItemModalShow"><i class="fas fa-plus"></i> Incoming
            Stock
        </button> --}}
        <a href="{{ route('spices_stock_lines') }}" class="btn btn-warning"><i class="fas fa-eye"></i> View
            Stock Lines
        </a>
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
                                <th>Unit Measure </th>
                                <th>Book Stock(calc)</th>
                                <th>Actions</th>
                                {{-- <th>Consumed Stock(calc)</th>
                                <th>Physical Stock</th> --}}
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Item No</th>
                                <th>Desc</th>
                                <th>Unit Measure </th>
                                <th>Book Stock(calc)</th>
                                <th>Actions</th>
                                {{-- <th>Consumed Stock(calc)</th>
                                <th>Physical Stock</th> --}}
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($stock as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->item_code }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ $data->unit_measure }}</td>
                                <td>{{ number_format($data->book_stock, 2) }}</td>
                                <td><a href="{{ route('spices_stock_line_info', $data->item_code ) }}" title="more info" class="btn btn-info btn-xs"> <i class="fa fa-info-circle" aria-hidden="true"></i></a>
                                </td>
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
