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
                                <th>Consumed Stock(calc)</th>
                                <th>Physical Stock</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Item No</th>
                                <th>Desc</th>
                                <th>Unit Measure </th>
                                <th>Book Stock(calc)</th>
                                <th>Consumed Stock(calc)</th>
                                <th>Physical Stock</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($stock as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->item_code }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ $data->unit_measure }}</td>
                                <td>{{ $data->book_stock }}</td>
                                <td></td>
                                <td></td>
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
