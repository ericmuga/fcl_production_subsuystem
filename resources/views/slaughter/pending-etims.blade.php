@extends('layouts.slaughter_master')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Settlements Pending Etims Update| <span id="subtext-h1-title"><small> Update Per settlement</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Vendor No.</th>
                                <th>Vendor Name </th>
                                <th>Settlement No </th>
                                <th>Total Weight </th>
                                <th>Unit Price</th>
                                <th>Net Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Vendor No.</th>
                                <th>Vendor Name </th>
                                <th>Settlement No </th>
                                <th>Total Weight </th>
                                <th>Unit Price</th>
                                <th>Net Amount</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($results as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->vendor_no }}</td>
                                <td>{{ $data->vendor_name }}</td>
                                <td>{{ $data->settlement_no }}</td>
                                <td>{{ $data->totalWeight }}</td>
                                <td>{{ $data->unitPrice }}</td>
                                <td>{{ $data->netAmount }}</td>
                                <td>
                                    <button type="button" data-settlement_ref="{{ $data->settlement_no }}" 
                                        class="btn btn-primary btn-sm " id="editScaleModalShow"><i
                                            class="nav-icon fas fa-edit"></i>
                                        Edit</button>
                                </td>
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

<!-- Start Edit Scale Modal -->
<div id="editScaleModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <form id="form-edit-scale" class="form-prevent-multiple-submits" action="{{ route('update_pending_etims') }}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Update eTims for Settlement: <code><strong><input style="border:none"
                                    type="text" id="item_name" name="item_name" readonly></strong></code></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <label for="cu_inv_no">Cu Invoice No:</label>
                        <input type="text" class="form-control" id="cu_inv_no" name="cu_inv_no" value=""
                            required>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-group">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button class="btn btn-warning btn-lg btn-prevent-multiple-submits"
                            onclick="return validateOnEditSubmit()" type="submit">
                            <i class="fa fa-save"></i> Update
                        </button>
                    </div>
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
        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        // edit
        $("body").on("click", "#editScaleModalShow", function (a) {
            a.preventDefault();

            var settlement = $(this).data('settlement_ref');

            $('#item_name').val(settlement);
            $('#item_id').val(settlement);

            // Set focus on the desired field
            $('#editScaleModal').on('shown.bs.modal', function () {
                $('#cu_inv_no').focus();
            });

            $('#editScaleModal').modal('show');
        });

    });

</script>
@endsection
