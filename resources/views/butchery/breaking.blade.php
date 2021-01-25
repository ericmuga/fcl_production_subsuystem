@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="">
            <h1 class="m-0"> Reports |<small> Pork Breaking Report</small></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"> Scale 2 output data | <span id="subtext-h1-title"><small> entries ordered by
                    latest</small> </span></h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
        <table id="example2" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Code</th>
                    <th>Product</th>
                    <th>Weight (kgs)</th>
                    <th>Date </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>#</th>
                    <th>Product Code</th>
                    <th>Product</th>
                    <th>Weight (kgs)</th>
                    <th>Date </th>
                </tr>
            </tfoot>
            <tbody>
                @foreach($butchery_data as $data)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td> {{ $data->item_code }} </td>
                        <td> {{ $data->description }}</td>
                        <td> {{ $data->net_weight }}</td>
                        <td> {{ $data->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->
<!-- /.col -->
@endsection
