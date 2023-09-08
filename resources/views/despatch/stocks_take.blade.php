@extends('layouts.despatch_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"> {{ $title }} |<small> Create Stock Take Entries </small></h1>
        </div><!-- /.col -->
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#exampleModal"><i
                class="fa fa-excel"></i>
            Import Stocks
        </button>
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <form action="{{ route('import_stocks_excel') }}" method="post" enctype="multipart/form-data">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @csrf
                    <input type="file" name="file">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-lg btn-prevent-multiple-submits"> Import Stocks</button>
                </div>
            </div>
        </div>
    </form>
</div>

<form id="form-save-scale3" class="form-prevent-multiple-submits" action="{{ route('save_stocks') }}"
    method="post">
    @csrf
    <div class="card-group">
        <div class="card">
            <div class="card-body " style="">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="exampleInputPassword1"> Product</label>
                            <select class="form-control select2" name="product" id="product" required>
                                <option value="">Select product</option>
                                @foreach($items as $i)
                                    <option
                                        value="{{ $i->code.'-'.$i->unit_of_measure.'-'.$i->qty_per_unit_of_measure }}">
                                        {{ $i->code. '-'.$i->description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-6">
                        <div class="form-group" id="product_type_select">
                            <label for="exampleInputPassword1">Unit measure</label>
                            <input type="text" class="form-control" id="unit_measure" value="" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" id="product_type_select">
                            <label for="exampleInputPassword1">Qty Unit Measure</label>
                            <input type="text" class="form-control" id="qty_unit" value="" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <label for="exampleInputEmail1">Weight</label>
                    <input type="number" step="0.01" class="form-control" id="reading" name="reading" value=""
                        placeholder="" required>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Pieces</label>
                    <input type="number" class="form-control" id="pieces" name="pieces" value="" step="" placeholder=""
                        required>
                </div>
            </div>
        </div>
        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <label for="exampleInputPassword1">Stock Take Date</label>
                    <div class="input-group date" id="reservationdate" data-target-input="nearest">
                        <input type="text" class="form-control datetimepicker-input" id="prod_date" name="prod_date"
                            required data-target="#reservationdate" />
                        <div class="input-group-append" data-target="#reservationdate" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Chiller </label>
                    <select class="form-control select2" name="chiller_code" id="chiller_code" required>
                        <option value="">Select chiller</option>
                        @foreach($chillers as $c)
                            <option value="{{ $c->chiller_code }}">{{ $c->description }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" class="input_checks" id="location_code" value="3535">
                <div class="form-group" style="padding-top: 5%">
                    <button type="submit" class="btn btn-primary btn-lg btn-prevent-multiple-submits"><i
                            class="fa fa-paper-plane single-click" aria-hidden="true"></i> Save</button>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" class="input_checks" id="loading_value" value="0">
</form><br>

<div class="div">
    <button class="btn btn-primary " data-toggle="collapse" data-target="#slicing_output_show"><i
            class="fa fa-plus"></i>
        Output
    </button>
</div>

<div id="slicing_output_show" class="collapse">
    <hr>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"> Stock Entries Data | <span id="subtext-h1-title"><small> entries for
                                last 10
                                days</small> </span></h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example1" class="table display nowrap table-striped table-bordered table-hover"
                            width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Code </th>
                                    <th>product </th>
                                    <th>Weight(kgs)</th>
                                    <th>No. of Pieces</th>
                                    <th>Location Code</th>
                                    <th>Chiller</th>
                                    <th>Date </th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Code </th>
                                    <th>product </th>
                                    <th>Weight(kgs)</th>
                                    <th>No. of Pieces</th>
                                    <th>Location Code</th>
                                    <th>Chiller</th>
                                    <th>Date </th>
                                </tr>
                            </tfoot>
                            <tbody>
                                @foreach($data as $d)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $d->product_code }}</td>
                                        <td>{{ $d->description }}</td>
                                        <td>{{ $d->weight }}</td>
                                        <td>{{ $d->pieces }}</td>
                                        <td>{{ $d->location_code }}</td>
                                        <td>{{ $d->chiller_code }}</td>
                                        <td>{{ $d->created_at }}</td>
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
</div>
<!-- slicing ouput data show -->

<!-- Edit Modal -->
<div id="itemCodeModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!--Start create user modal-->
        <form class="form-prevent-multiple-submits" id="form-edit-role"
            action="{{ route('butchery_scale3_update') }}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Scale3 Item: <strong><input style="border:none"
                                type="text" id="item_name" name="item_name" value="" readonly></strong></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="exampleInputPassword1">Product</label>
                        <select class="form-control select2" name="edit_product" id="edit_product" required>
                            @foreach($items as $i)
                                <option value="{{ trim($i->code) }}" selected="selected">
                                    {{ ucwords($i->description) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row form-group">
                        <div class="col-md-6">
                            <label for="exampleInputPassword1">No. of Crates</label>
                            <select class="form-control" name="edit_crates" id="edit_crates" required>
                                <option>1</option>
                                <option>2</option>
                                <option>3</option>
                                <option selected>4</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="exampleInputPassword1">Product Type</label>
                            <select class="form-control" name="edit_product_type2" id="edit_product_type2"
                                selected="selected" required>
                                <option value="1">
                                    Main Product
                                </option>
                                <option value="2">
                                    By Product
                                </option>
                                <option value="3">
                                    Intake
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-form-label">Scale Weight(actual_weight)</label>
                        <input type="number" onClick="this.select();" class="form-control" name="edit_weight"
                            id="edit_weight" placeholder="" step="0.01" autocomplete="off" required autofocus>
                    </div>
                    <div class="form-group">
                        <label>No. of Pieces</label>
                        <input type="number" onClick="this.select();" onfocus="this.value=''" class="form-control"
                            id="edit_no_pieces" value="" name="edit_no_pieces" placeholder="">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Production Process</label>
                        <select selected="selected" class="form-control" name="edit_production_process"
                            id="edit_production_process">

                        </select>
                    </div>
                    <input type="hidden" name="item_id" id="item_id" value="">
                    <input type="hidden" id="loading_val_edit" value="0">
                </div>
                <div class="modal-footer">
                    <div class="form-group">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button class="btn btn-warning btn-lg btn-prevent-multiple-submits" type="submit">
                            <i class="fa fa-save"></i> Update
                        </button>
                    </div>
                </div>

                <div id="loading2" class="collapse">
                    <div class="row d-flex justify-content-center">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!--End Edit scale1 modal-->

@endsection

@section('scripts')
<script>
    $(document).ready(function () {

        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        $('#product').on('input', () => {
            let itm = $('#product').val()
            if ($(this).has('option').length > 0) {
                // var invoiced_qty = itm.substr(0, itm.indexOf(' '))
                const parts = itm.split('-');
                // console.log(parts[0])
                $('#unit_measure').val(parts[1])

                let formatedQty = parseFloat(parts[2])
                $('#qty_unit').val(formatedQty.toFixed(2))

                $('#product').select2('destroy').select2();
                $('#reading').focus();
            }
        })

        $("body").on("click", "#itemCodeModalShow", function (e) {
            e.preventDefault();

            var code = $(this).data('code');
            var item = $(this).data('item');
            var weight = $(this).data('weight');
            var no_of_pieces = $(this).data('no_of_pieces');
            var id = $(this).data('id');
            var process_code = $(this).data('production_process');
            var type_id = $(this).data('type_id');

            $('#edit_product').val(code);
            $('#item_name').val(item);
            $('#edit_weight').val(weight);
            $('#edit_no_pieces').val(no_of_pieces)
            $('#item_id').val(id);
            $('#edit_production_process').val(process_code);
            $('#edit_product_type2').val(type_id);

            $('#edit_product').select2('destroy').select2();

            loadEditProductionProcesses(code);

            $('#itemCodeModal').modal('show');
        });
    });

</script>
@endsection
