@extends('layouts.spices_master')

@section('content')

<div class="div">
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#import_template"><i
            class="fas fa-file-excel"></i> Import
        Template List</button>
</div><br>

<!-- Import modal -->
<div class="modal fade" id="import_template" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <form id="form-import-template" class="form-prevent-multiple-submits" action="{{route('template_upload')}}"
            method="post" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Import Template</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="farmer_import">Choose file</label>
                        <br>
                        <input type="file" name="file" id="file" required>
                    </div>
                    <input type="hidden" name="item_created_date" id="item_created_date" value="">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-flat " type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit"
                        class="btn btn-outline-info btn-lg btn-prevent-multiple-submits float-right"><i
                            class="fa fa-spinner" aria-hidden="true"></i> Upload</button>
                </div>
            </div>
    </div>
</div>
<!-- end Return sales -->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"></h3>
                <h3 class="card-title"> Imported Template List | <span id="subtext-h1-title"><small> view, filter,
                            print/download</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Template No</th>
                                <th>Template Name</th>
                                <th> Blocked </th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Template No</th>
                                <th>Template Name</th>
                                <th> Blocked </th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($templates as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><a href="{{ route('template_lines', $data->template_no) }}">{{ $data->template_no }}</a></td>
                                <td>{{ $data->template_name }}</td>
                                @if ($data->blocked == 0)
                                <td><span class="badge badge-success">No</span></td>

                                @else
                                <td><span class="badge badge-danger">Yes</span></td>

                                @endif
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

@section('scripts')
<script>
    $(document).ready(function () {
        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });
    });

</script>

@endsection
