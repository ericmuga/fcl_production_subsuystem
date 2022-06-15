@extends('layouts.spices_master')

@section('content')


<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"></h3>
                <h3 class="card-title">Production Lines| <span id="subtext-h1-title"><small> Showing lines with status: <strong>{{ $status }}</strong> </small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Batch No</th>
                                <th>Posting Date</th>
                                <th>Posted By </th>
                                <th>Template No </th>
                                <th>Output Qty</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Batch No</th>
                                <th>Posting Date</th>
                                <th>Posted By </th>
                                <th>Template No </th>
                                <th>Output Qty</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            {{-- @foreach($template_lines as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->item_no }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ $data->percentage }}</td>
                                <td>{{ $data->type }}</td>
                                <td>{{ $data->main_product }}</td>
                                <td>{{ $data->shortcode }}</td>
                                <td>{{ $data->unit_measure }}</td>
                                <td>{{ $data->location }}</td>
                            </tr>
                            @endforeach --}}
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
