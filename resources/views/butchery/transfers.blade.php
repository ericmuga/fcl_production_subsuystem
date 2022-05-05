@extends('layouts.butchery_master')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"> Sales Entries | <span id="subtext-h1-title"><small> view butchery sale for
                            <strong>last 7 days</strong></small>
                    </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Code</th>
                                <th>Product Name</th>
                                <th> weight(kgs) </th>
                                <th>Net Weight(kgs)</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Product Code</th>
                                <th>Product Name</th>
                                <th> weight(kgs) </th>
                                <th>Net Weight(kgs)</th>
                                <th>Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($transfers_data as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td id="editModalShow" data-id="{{$data->id}}" data-product_code="{{$data->item_code}}"
                                    data-item="{{$data->description}}"
                                    data-weight="{{number_format($data->actual_weight, 2)}}"><a
                                        href="#">{{ $data->item_code }}</a> </td>
                                <td>{{ $data->description }}</td>
                                <td>{{ number_format($data->actual_weight, 2) }}</td>
                                <td>{{ number_format($data->net_weight, 2) }}</td>
                                <td>{{ $data->created_at }}</td>
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

<!-- Edit transfers Modal -->
<div id="editModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!--Start create user modal-->
        <form id="form-edit-role" class="form-prevent-multiple-submits" action="{{route('butchery_transfers_update')}}"
            method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Transfer Item: <strong><input
                                style="border:none" type="text" id="item_name" name="item_name" value=""
                                readonly></strong></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="email" class="col-form-label"> Product </label>
                        <select class="form-control" name="edit_carcass" id="edit_carcass">
                            @foreach($products_list as $data)
                            <option value="{{trim($data->code)}}">{{$data->description}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">No. of Crates</label>
                        <select class="form-control select2" name="edit_crates" id="edit_crates" required>
                            <option value="" selected disabled>Please select</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-form-label">Scale Weight(actual_weight)</label>
                        <input type="number" onClick="this.select();" class="form-control" name="edit_weight"
                            id="edit_weight" placeholder="" step="0.01" autocomplete="off" required autofocus>
                    </div>
                    <input type="hidden" name="item_id" id="item_id" value="">
                </div>
                <div class="modal-footer">
                    <div class="form-group">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button class="btn btn-warning btn-prevent-multiple-submits">
                            <i class="fa fa-save"></i> Update
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!--End Edit transfers modal-->

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        $("body").on("click", "#editModalShow", function (a) {
            a.preventDefault();

            var product = $(this).data('product_code');
            var item = $(this).data('item');
            var no_carcass = $(this).data('no_carcass');
            var weight = $(this).data('weight');
            var id = $(this).data('id');


            $('#edit_carcass').val(product);
            $('#item_name').val(item);
            $('#edit_no_carcass').val(no_carcass);
            $('#edit_weight').val(weight);
            $('#item_id').val(id);

            $('#editModal').modal('show');
        });

        // returns modal
        $("body").on("click", "#returnSaleModalShow", function (a) {
            a.preventDefault();

            var id = $(this).data('id');
            var product = $(this).data('product_code');
            var item = $(this).data('item');
            var no_carcass = $(this).data('no_carcass');
            var created_date = $(this).data('timestamp');
            var weight = $(this).data('weight');


            $('#return_item_id').val(id);
            $('#return_item_code').val(product);
            $('#item_name2').val(item);
            $('#return_no_carcass').val(no_carcass);
            $('#return_weight').val(weight);
            $('#item_created_date').val(created_date);
            $('#weight').val(weight);

            $('#returnSaleModal').modal('show');
        });
    });

</script>

@endsection
