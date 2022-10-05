@extends('layouts.butchery_master')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"> Sales Entries | <span id="subtext-h1-title"><small> view butchery sale for <strong>last 7 days</strong></small>
                    </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Code</th>
                                <th>Product Name</th>
                                <th>No. of Pieces</th>
                                <th>No. of Carcasses</th>
                                <th> weight(kgs) </th>
                                <th>Net Weight(kgs)</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Product Code</th>
                                <th>Product Name</th>
                                <th>No. of Pieces</th>
                                <th>No. of Carcasses</th>
                                <th> weight(kgs) </th>
                                <th>Net Weight(kgs)</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($sales_data as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td id="editModalShow" data-id="{{$data->id}}" data-product_code="{{$data->item_code}}"
                                    data-item="{{$data->description}}" data-no_carcass="{{ $data->no_of_pieces }}"
                                    data-weight="{{number_format($data->actual_weight, 2)}}"><a
                                        href="#">{{ $data->item_code }}</a> </td>
                                <td>{{ $data->description }}</td>
                                <td>{{ number_format($data->no_of_pieces, 0) }}</td>
                                <td>{{ $data->no_of_carcass }}</td>
                                <td>{{ number_format($data->actual_weight, 2) }}</td>
                                <td>{{ number_format($data->net_weight, 2) }}</td>
                                <td>{{ $data->created_at }}</td>
                                @if ( $data->returned == 0 )
                                <td>
                                    <button type="button" data-id="{{$data->id}}"
                                        data-product_code="{{$data->item_code}}" data-item="{{$data->description}}"
                                        data-no_carcass="{{ $data->no_of_pieces }}"
                                        data-weight="{{number_format($data->actual_weight, 2)}}" data-timestamp="{{ $data->created_at }}"
                                        class="btn btn-info btn-xs" title="Return Sale" id="returnSaleModalShow"><i
                                            class="fa fa-undo"></i>
                                    </button>
                                </td>
                                @elseif ($data->returned == 1)
                                <td>
                                    <span class="badge badge-warning">returned item</span>
                                </td>
                                @elseif(($data->returned == 2))
                                <td>
                                    <span class="badge badge-success">returned entry</span>
                                </td>
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

<!-- Edit sales Modal -->
<div id="editModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!--Start create user modal-->
        <form id="form-edit-role" class="form-prevent-multiple-submits" action="{{route('butchery_sales_update')}}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit sales Item: <strong><input style="border:none"
                                type="text" id="item_name" name="item_name" value="" readonly></strong></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="email" class="col-form-label"> Carcass Type </label>
                        <select class="form-control" name="edit_carcass" id="edit_carcass">
                            <option value="G1033">Porker, Headles-sales</option>
                            <option value="G1032">Porker, HeadOn-sales</option>
                            <option value="G1034">Pig, Carcass-Side without Tail-Sales</option>
                            <option value="G1101">Pork Shoulder</option>
                            <option value="G1102">Pork Middle</option>
                            <option value="G1109">Sow Shoulder</option>
                            <option value="G1110">Sow Middle</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>No. of Pieces</label>
                        <input type="number" onClick="this.select();" class="form-control" id="edit_no_carcass" value=""
                            name="edit_no_carcass" placeholder="">
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
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button class="btn btn-warning btn-prevent-multiple-submits">
                            <i class="fa fa-save"></i> Update
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!--End Edit sales modal-->

<!-- Return Sale modal -->
<div class="modal fade" id="returnSaleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-sales-returns"  class="form-prevent-multiple-submits" action="{{route('butchery_sales_returns')}}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Sale Return</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Please confirm if you want to return this product</p>
                    <label for="item_name">Item Name: </label>
                    <p><strong><input style="border:none" type="text" class="form-control" id="item_name2" name="item_name" value=""
                                readonly></strong></p>
                    <label for="item_name">Item Weight: </label>
                    <p><strong><input style="border:none" type="text" class="form-control" id="weight" name="weight" value=""
                                readonly></strong></p>
                    <input type="hidden" id="return_no_carcass" value="" name="return_no_carcass">
                    <input type="hidden" name="return_weight" id="return_weight">
                    <input type="hidden" name="return_item_code" id="return_item_code" value="">
                    <input type="hidden" name="return_item_id" id="return_item_id" value="">
                    <input type="hidden" name="item_created_date" id="item_created_date" value="">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-flat " type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-outline-info btn-lg btn-prevent-multiple-submits float-right"><i class="fa fa-spinner"
                            aria-hidden="true"></i> Confirm</button>
                </div>
            </div>
    </div>
</div>
<!-- end Return sales -->

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $('.form-prevent-multiple-submits').on('submit', function(){
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
