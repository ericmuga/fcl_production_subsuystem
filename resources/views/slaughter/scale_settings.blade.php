@extends('layouts.slaughter_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="card-title"> Scale configs | <span id="subtext-h1-title"><small> view and edit scale
                        configs</small> </span></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<div class="row">
    <div class="col-md-8" style="margin: 0 auto; float: none;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Showing all Entries </h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Scale Name</th>
                            <th>ComPort</th>
                            <th>BaudRate</th>
                            <th>Tareweight</th>
                            <Th>Date Created</Th>
                            <th style="width: 30px">Config</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <th style="width: 10px">#</th>
                        <th>Scale Name</th>
                        <th>ComPort</th>
                        <th>BaudRate</th>
                        <th>Tareweight</th>
                        <Th>Date Created</Th>
                        <th style="width: 30px">Config</th>
                    </tfoot>
                    <tbody>
                        @foreach($scale_settings as $data)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $data->scale }}</td>
                                <td>{{ $data->comport }}</td>
                                <td>{{ $data->baudrate }}</td>
                                <td>{{ number_format($data->tareweight, 2) }}</td>
                                <td>{{ $helpers->dateToHumanFormat($data->created_at) }}</td>
                                <td>
                                    <button type="button" data-id="{{ $data->id  }}" data-item="{{ $data->scale }}" data-comport="{{ $data->comport }}" data-baudrate="{{ $data->baudrate }}" data-tareweight="{{ number_format($data->tareweight, 2) }}"
                                        class="btn btn-primary btn-sm " id="editScaleModalShow"><i
                                            class="nav-icon fas fa-edit"></i>
                                        Edit</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
    </div>
</div>

<!-- Start Edit Scale Modal -->
<div id="editScaleModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <form id="form-edit-scale" action="" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Scale: <code><strong><input style="border:none"
                                type="text" id="item_name" name="item_name" value="" readonly></strong></code></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="baud">ComPort:</label>
                        <select class="form-control" name="edit_comport" id="edit_comport">
                            {{-- @if ($comport)
                                @foreach($comport as $com)
                                    <option value="{{$com->id}}" selected="selected">{{$com->role_name}}</option>
                                @endforeach
                            @endif --}}

                        </select>
                    </div>
                    <div class="form-group">
                        <label for="baud">BaudRate:</label>
                        <input type="number" class="form-control" id="edit_baud" name="edit_baud" value="" placeholder="" required>
                    </div>
                    <div class="form-group">
                        <label for="baud">Tareweight:</label>
                        <input type="number" class="form-control" id="edit_tareweight" step="0.01" value="" name="edit_tareweight" placeholder="" required>
                    </div>
                    <input type="hidden" name="item_id" id="item_id" value="">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">
                        <i class="fa fa-save"></i> Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- End Edit Scale modal-->

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        // edit
        $("body").on("click", "#editScaleModalShow", function (a) {
            a.preventDefault();

            var scale = $(this).data('item');
            var comport = $(this).data('comport');
            var tareweight = $(this).data('tareweight');
            var baud = $(this).data('baudrate');
            var id = $(this).data('id');

            $('#item_name').val(scale);
            $('#edit_comport').val(comport);
            $('#edit_baud').val(baud);
            $('#edit_tareweight').val(tareweight);
            $('#item_id').val(id);


            $('#editScaleModal').modal('show');
        });

        /* Start comport data ajax */

        /* End comport data ajax */
    });

</script>
@endsection
