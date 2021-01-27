@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="">
            <h1 class="m-0"> Reports |<small> Pork Deboning Report</small></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"> showing all Entries | <span id="subtext-h1-title"><small> filter, view,
                    export</small> </span></h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
        <div class="hidden" hidden>{{ $i = 1 }}</div>
        <table id="example1" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Code </th>
                    <th>product </th>
                    <th>Weight(kgs)</th>
                    <th>Date </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>#</th>
                    <th>Code </th>
                    <th>product </th>
                    <th>Weight(kgs)</th>
                    <th>Date </th>
                </tr>
            </tfoot>
            <tbody>
                @foreach($deboning_data as $data)
                    <tr>
                        <td>{{ $i++ }}</td>
                        <td> {{ $data->item_code }}</td>
                        <td>{{ $helpers->getProductName($data->item_code) }}</td>
                        <td> {{ number_format($data->net_weight, 2) }}</td>
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
