@extends('layouts.spices_master')

@section('content')

<div class="row">
    <div class="col-md-7">
        <h3>Items List Registry </h3>
    </div>
    <div class="col-md-5">
        <button class="btn btn-primary" id="createItemModalShow"><i class="fas fa-plus"></i> Add
            New Item
        </button>
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
                                <th>Item Code</th>
                                <th>Description</th>
                                <th>Unit of Measure </th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Item Code</th>
                                <th>Description</th>
                                <th>Unit of Measure </th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($items as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->item_code }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ $data->unit_measure }}</td>
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
