@extends('layouts.spices_master')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="card-title"> Chopping runs for <strong>{{ $run_no }}</strong> Entries <span id="subtext-h1-title">
                        </h3>
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
                                <th>Item No</th>
                                <th>Item Name</th>
                                <th>Item Type</th>
                                <th>Weight</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Item No</th>
                                <th>Item Name</th>
                                <th>Item Type</th>
                                <th>Weight</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($lines as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->item_code }}</td>
                                    <td>{{ $data->description }}</td>
                                    <td>
                                        <span class="badge {{ $data->output == 1 ? 'badge-info' : 'badge-success' }}">
                                            {{ $data->output == 1 ? 'Output' : 'Input' }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($data->weight, 2) }}</td>
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
