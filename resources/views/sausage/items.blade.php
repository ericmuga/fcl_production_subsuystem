@extends('layouts.sausage_master')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Items Registry | showing {{ $category }} category</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table table-striped table-bordered table-hover"
                        style="scroll-behavior: smooth;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item Code</th>
                                <th>Barcode</th>
                                <th>Item Name </th>
                                <th>Item category</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Item Code</th>
                                <th>Barcode</th>
                                <th>Item Name </th>
                                <th>Item category</th>
                            </tr>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($items as $data)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $data->code }}</td>
                                <td>{{ $data->barcode }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ $data->category }}</td>
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
