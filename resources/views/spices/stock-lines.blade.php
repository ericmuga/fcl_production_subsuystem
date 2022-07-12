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
                                <th>Unit Measure </th>
                                <th>Entry Type</th>
                                <th>Date</th>
                                <th>Action</th>
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
                            @foreach($lines as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->item_code }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ $data->percentage }}</td>
                                <td>{{ $data->type }}</td>
                                <td>{{ $data->main_product }}</td>
                                <td>{{ $data->shortcode }}</td>
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
    </div>
    <!-- /.col -->
</div>
@endsection
