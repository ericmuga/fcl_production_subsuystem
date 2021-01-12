@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            {{-- <h1 class="m-0"> {{ $title }}<small></small></h1> --}}
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
                <h3 class="card-title">Bordered Table</h3>
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
                            <th style="width: 30px">Config</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <th style="width: 10px">#</th>
                        <th>Scale Name</th>
                        <th>ComPort</th>
                        <th>BaudRate</th>
                        <th style="width: 30px">Config</th>
                    </tfoot>
                    <tbody>
                        <tr>

                        </tr>
                        </tr>
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
                    <h5 class="modal-title" id="exampleModalLabel">Edit Scale: <strong><input style="border:none"
                                type="text" id="item_name" name="item_name" value="" readonly></strong></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="baud">ComPort:</label>
                        <select name="toggle_action" id="toggle_action" class="form-control" required>
                            <option value="" selected disabled>Select action</option>
                            <option value="1">ComPort1</option>
                            <option value="2">ComPort2</option>
                            <option value="3">ComPort3</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="baud">BaudRate:</label>
                        <input type="number" class="form-control" id="baud" name="baud" placeholder="" required>
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

            $('#item_name').val(scale);


            $('#editScaleModal').modal('show');
        });
    });

</script>
@endsection
