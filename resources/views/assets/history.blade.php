@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"> {{ $title }} |<small> Showing all entries </small></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table table-striped table-bordered table-hover" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fa </th>
                                <th>Description </th>
                                <th>Comments </th>
                                <th>Assigned Employee</th>
                                <th>Employee Dept</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Fa </th>
                                <th>Description </th>
                                <th>Comments </th>
                                <th>Assigned Employee</th>
                                <th>Employee Dept</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($data as $e)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $e->No_ }}</td>
                                    <td>{{ $e->Description }}</td>
                                    <td>{{ $e->Comments }}</td>
                                    <td>{{ $e->Responsible_employee }}</td>
                                    <td>{{ $e->LocationName }}</td>
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
</div><br>

@endsection
