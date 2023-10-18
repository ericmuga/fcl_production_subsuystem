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
                                <th>From Dept </th>
                                <th>From Employee </th>
                                <th>Assigned Employee</th>
                                <th>Employee Dept</th>
                                <th>Date </th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Fa </th>
                                <th>Description </th>
                                <th>From Dept </th>
                                <th>From Employee </th>
                                <th>Assigned Employee</th>
                                <th>Employee Dept</th>
                                <th>Date </th>
                        </tfoot>
                        <tbody>
                            @foreach($data as $e)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $e->fa }}</td>
                                    <td>{{ $e->description }}</td>
                                    <td>{{ $e->from_dept }}</td>
                                    <td>{{ $e->from_user }}</td>
                                    <td>{{ $e->to_user }}</td>
                                    <td>{{ $e->to_dept }}</td>
                                    <td>{{ \Carbon\Carbon::parse($e->created_at)->format('d/m/Y H:i') }}
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
