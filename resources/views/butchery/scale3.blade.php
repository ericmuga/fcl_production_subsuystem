@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"> {{ $title }} |<small> Deboning </small></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<form id="form-save-scale3" class="form-prevent-multiple-submits"
    action="{{ route('butchery_scale3_save') }}" method="post">
    @csrf
    <div class="card-group">
        <div class="card">
            <div class="card-body " style="">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="exampleInputPassword1"> Product ShortCode</label>
                            <select class="form-control select2" name="product" id="product" required>
                                <option value="">Select product</option>
                                @foreach($products as $product)
                                    <option
                                        value="{{ $product->shortcode.'-'.$product->code.'-'.$product->product_type_code }}">
                                        {{ $product->shortcode . substr($product->code, strpos($product->code, "G") + 1).' '.$product->description.'-'.$product->product_type_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-8">
                        <div class="form-group" id="product_type_select">
                            <label for="exampleInputPassword1">Product Type</label>
                            <input type="text" class="form-control" id="product_type" value="" name="product_type">
                        </div>
                    </div>
                    {{-- <div class="col-md-4" style="padding-top: 7%">
                        <button class="btn btn-outline-info btn-sm form-control" id="btn_product_type" type="button"
                            data-toggle="modal" disabled>
                            <strong>Edit?</strong>
                        </button>
                    </div> --}}
                </div>
                <div class="row form-group">
                    <div class="col-md-6">
                        <div class="form-group" id="product_type_select">
                            <label for="exampleInputPassword1">Product Name</label>
                            <input type="text" class="form-control" id="product_name" value="">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" id="product_type_select">
                            <label for="exampleInputPassword1">Production Process</label>
                            <input type="text" class="form-control" id="production_process" name="production_process"
                                value="">
                        </div>
                        <input type="hidden" class="form-control" id="production_process_code"
                            name="production_process_code" value="">
                    </div>
                </div>
                <div class="form-group" style="padding-left: 30%;">
                    <button type="button" onclick="getScaleReading()" id="weigh" value=""
                        class="btn btn-primary btn-lg"><i class="fas fa-balance-scale"></i> Weigh</button> <br><br>
                    <small>Reading from <input type="text" id="comport_value" value="{{ $configs[0]->comport }}"
                            style="border:none" disabled></small>
                </div>

            </div>
        </div>
        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <label for="exampleInputEmail1">Reading</label>
                    <input type="number" step="0.01" class="form-control" id="reading" name="reading" value="0.00"
                        oninput="getNet()" placeholder="" readonly>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="manual_weight" name="manual_weight">
                    <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                </div> <br>
                <input type="hidden" id="old_manual" value="{{ old('manual_weight') }}">
                <div class="form-group">
                    <label for="exampleInputPassword1">Crates Tare-Weight</label>
                    <input type="number" class="form-control" id="tareweight" name="tareweight"
                        value="{{ number_format($configs[0]->tareweight * 4, 2) }}" readonly>
                    <input type="hidden" class="form-control " id="default_tareweight"
                        value="{{ number_format($configs[0]->tareweight, 2) }}">
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Net</label>
                    <input type="number" class="form-control" id="net" name="net" value="0.00" step=".01" placeholder=""
                        readonly>
                </div>
            </div>
        </div>

        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <label for="exampleInputPassword1">Production Date</label>
                    <select class="form-control" name="prod_date" id="prod_date" required>
                        <option selected value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                    </select>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="exampleInputPassword1">No. of Crates</label>
                            <input type="number" class="form-control" onClick="this.select();" id="no_of_crates" value="4"
                                name="no_of_crates" placeholder="" required>
                        </div>
                        <div class="col-md-6">
                            <label for="exampleInputPassword1">No. of pieces </label>
                            <input type="number" class="form-control" onClick="this.select();" id="no_of_pieces" value="0"
                                name="no_of_pieces" placeholder="" required>
                        </div>
                    </div>                    
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Narration </label>
                    <input type="text" class="form-control" onClick="this.select();" id="desc" value=""
                        name="desc" placeholder="any further narration eg. java, jowl..">
                </div>
                <!-- <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="for_transfer" name="for_transfer">
                    <label class="form-check-label" for="for_transfer"><strong> For Transfer?</strong></label>
                </div><br> -->
                <div id="transfer_div" class="form-group collapse">
                    <label for="exampleInputPassword1">Transfer To</label>
                    <select class="form-control select2" name="transfer_to" id="transfer_to" required>
                        <option selected value="1">Sausage</option>
                        <option value="2">High Care</option>
                        <option value="3">Despatch</option>
                    </select>
                </div>
                <div class="form-group" style="padding-top: 5%">
                    <button type="submit" onclick="return validateOnSubmit()"
                        class="btn btn-primary btn-lg btn-prevent-multiple-submits"><i
                            class="fa fa-paper-plane single-click" aria-hidden="true"></i> Save</button>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" class="input_checks" id="loading_value" value="0">

    <div id="loading" class="collapse">
        <div class="row d-flex justify-content-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>
</form>


<!-- product type modal -->
<div class="modal fade" id="productTypesModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-product-type" class="form-prevent-multiple-submits" action="#" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Product Type</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class=" form-group">
                        <select name="edit_product_type" id="edit_product_type" class="form-control" required>
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
                <div class="modal-footer">
                    <button class="btn btn-primary btn-prevent-multiple-submits" type="submit"
                        onclick="setProductCode()">
                        <i class="fa fa-save"></i> Edit
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<hr>
<!-- end product code modal -->

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
                    <h3 class="card-title"> Scale 3 Deboned output data | <span id="subtext-h1-title"><small> entries
                                ordered by
                                latest</small> </span></h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="hidden" hidden>{{ $i = 1 }}</div>
                    <div class="table-responsive">
                        <table id="example1" class="table table-striped table-bordered table-hover" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Code </th>
                                    <th>product </th>
                                    <th>Product Type</th>
                                    <th>Production Process</th>
                                    <th>Scale Weight(kgs)</th>
                                    <th>Net Weight(kgs)</th>
                                    <th>No. of Crates</th>
                                    <th>No. of Pieces</th>
                                    <th>Narration</th>
                                    <th>Edited</th>
                                    <th>Date </th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Code </th>
                                    <th>product </th>
                                    <th>Product Type</th>
                                    <th>Production Process</th>
                                    <th>Scale Weight(kgs)</th>
                                    <th>Net Weight(kgs)</th>
                                    <th>No. of Crates</th>
                                    <th>No. of Pieces</th>
                                    <th>Narration</th>
                                    <th>Edited</th>
                                    <th>Date </th>
                                </tr>
                            </tfoot>
                            <tbody>
                                @foreach($deboning_data as $data)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        {{-- Allow edits for only today --}}
                                        @php
                                            $createdAtDate = \Carbon\Carbon::parse($data->created_at)->format('Y-m-d');
                                            $todayDate = \Carbon\Carbon::today()->format('Y-m-d');
                                        @endphp
                                        
                                        @if($createdAtDate === $todayDate)
                                            <td id="itemCodeModalShow" data-id="{{ $data->id }}"
                                                data-weight="{{ number_format($data->actual_weight, 2) }}"
                                                data-no_of_pieces="{{ $data->no_of_pieces }}"
                                                data-code="{{ $data->item_code }}"
                                                data-type_id="{{ $data->type_id }}"
                                                data-production_process="{{ $data->process_code }}"
                                                data-item="{{ $data->description }}"><a
                                                    href="#">{{ $data->item_code }}</a>
                                            </td>
                                        @else
                                            <td><span class="badge badge-warning">Edit closed</span></td>
                                        @endif

                                        <td>{{ $data->description }}</td>
                                        <td> {{ $data->product_type }}</td>
                                        <td> {{ $data->process }}</td>
                                        <td> {{ number_format($data->actual_weight, 2) }}</td>
                                        <td> {{ number_format($data->net_weight, 2) }}</td>
                                        <td> {{ $data->no_of_crates }}</td>
                                        <td> {{ $data->no_of_pieces }}</td>
                                        <td> {{ $data->narration }}</td>
                                        @if($data->edited == 1)
                                            <td>
                                                <span class="badge badge-warning">Yes</span>
                                            </td>
                                        @else
                                            <td>
                                                <span class="badge badge-success">No</span>
                                            </td>
                                        @endif
                                        <td> {{ $data->created_at }}</td>
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
                            @foreach($products as $product)
                                <option value="{{ trim($product->code) }}" selected="selected">
                                    {{ ucwords($product->description) }} - {{ $product->code }}
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
                        <button class="btn btn-warning btn-lg btn-prevent-multiple-submits"
                            onclick="return validateOnEditSubmit()" type="submit">
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

        let reading = document.getElementById('reading');
        if (($('#old_manual').val()) == "on") {
            $('#manual_weight').prop('checked', true);
            reading.readOnly = false;
            reading.focus();
            $('#reading').val("");

        } else {
            reading.readOnly = true;

        }

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


        $('#edit_product').change(function () {
            $('#loading_val_edit').val(0)
            var code = $('#edit_product').val();

            var crates_no = $('#edit_crates').val();
            var net_weight = $('#edit_weight').val();

            var net = net_weight - (1.8 * crates_no);

            getNumberOfPiecesEdit(code.trim(), net);
            loadEditProductionProcesses(code);
        });

        $('#edit_weight').keyup(function () {
            var code = $('#edit_product').val();

            var crates_no = $('#edit_crates').val();
            var net_weight = $('#edit_weight').val();

            var net = net_weight - (1.8 * crates_no);

            getNumberOfPiecesEdit(code.trim(), net);
        });

        $('#edit_crates').change(function () {
            var code = $('#edit_product').val();

            var crates_no = $('#edit_crates').val();
            var net_weight = $('#edit_weight').val();

            var net = net_weight - (1.8 * crates_no);

            getNumberOfPiecesEdit(code.trim(), net);
        });

        $("body").on("click", "#btn_product_type", function (a) {
            a.preventDefault();

            var product = $('#product_type').val();
            var code = 1;
            if (product == "By Product") {
                code = 2;
            } else if (product == "Intake") {
                code = 3;
            }
            $('#edit_product_type').val(code);

            $('#productTypesModal').modal('show');
        });

        $('#product').change(function () {
            $('#loading').collapse('show');
            $('#loading_value').val(0)
            var code = $('#product').val();
            var shortcode = code.split('-')[0];
            var product_code = code.split('-')[1];
            var product_type_code = code.split('-')[2];

            if (product_code != '') {
                $.ajax({
                    type: "GET",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{ url('product_details_ajax') }}",
                    data: {
                        'product_code': product_code,
                        'shortcode': shortcode,
                        'product_type_code': product_type_code,

                    },
                    dataType: 'JSON',
                    success: function (res) {
                        if (res) {
                            // console.log(res);
                            $('#btn_product_type').prop('disabled', false);

                            // product type
                            if (res[0].product_type == '1') {
                                $('#product_type').val("Main Product");

                            } else if (res[0].product_type == '2') {
                                $('#product_type').val("By Product");

                            } else {
                                $('#product_type').val("Intake");
                            }

                            // product name and process
                            $('#product_name').val(res[0].description);
                            $('#production_process').val(res[0].process);
                            $('#production_process_code').val(res[0].process_code);

                            // get number of pieces
                            if (product_code == 'G1169' || product_code == 'G1119' ||
                                product_code == 'G1121' || product_code == 'G1189' ||
                                product_code == 'G1164' || product_code == 'G1126') {
                                var net = $('#net').val();
                                getNumberOfPieces(product_code, net);

                            } else {
                                // focus on number of pieces                                       
                                $('#no_of_pieces').val(0);
                                $('#no_of_pieces').select();
                            }

                            //update loading value
                            $('#loading_value').val(1)
                            $('#loading').collapse('hide');
                        }
                    },
                    error: function (data) {
                        var errors = data.responseJSON;
                        console.log(errors);
                        alert('error occured when pulling production types');
                    }
                });

            }

        });

        $('#no_of_crates').change(function () {
            var number_of_crates = $(this).val();
            var default_tareweight = $('#default_tareweight').val();

            var new_tareweight = (number_of_crates) * (default_tareweight);
            $("#tareweight").val(Math.round((new_tareweight + Number.EPSILON) * 100) / 100);

            getNet();

        });

        $('#production_process').change(function () {
            var production_process = $('#production_process').val();
            var val_of_pieces = $('#no_of_pieces').val();

            if (production_process !== "" && val_of_pieces == 0) {
                $('#no_of_pieces').select();
            }
        });

        $('#manual_weight').change(function () {
            var manual_weight = document.getElementById('manual_weight');
            var reading = document.getElementById('reading');
            if (manual_weight.checked == true) {
                reading.readOnly = false;
                reading.focus();
                $('#reading').val("");

            } else {
                reading.readOnly = true;

            }

        });

        $('#for_transfer').change(function () {
            var for_transfer = document.getElementById('for_transfer');
            if (for_transfer.checked == true) {
                $('#transfer_div').collapse('show')

            } else {
                $('#transfer_div').collapse('hide')
            }

        });
    });

    function loadEditProductionProcesses(code) {
        $('#loading2').collapse('show');
        $('#loading_val_edit').val(0)

        $.ajax({
            type: "GET",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                    .attr('content')
            },
            url: "{{ url('product_process_ajax') }}",
            data: {
                'product_code': code,

            },
            dataType: 'JSON',
            success: function (data) {
                // console.log(data);
                var formOptions = "";
                for (var key in data) {
                    // console.log(data[key].process_code)
                    var process_code = data[key]
                        .process_code;

                    var process_name =
                        getProductionProcessName(
                            process_code);

                    formOptions += "<option value='" +
                        process_code + "'>" + process_name +
                        "</option>";
                }

                $('#edit_production_process').html(formOptions);

                // get number of pieces
                if (code == 'G1169' ||
                    code == 'G1119' ||
                    code == 'G1121' ||
                    code == 'G1189') {
                    var net = $('#net').val();

                    getNumberOfPieces(code, net);

                } else {
                    // focus on number of pieces                                       
                    $('#no_of_pieces').val(0);
                    $('#no_of_pieces').select();
                }

                // completed loading process code
                $('#loading_val_edit').val(1)
                $('#loading2').collapse('hide');

            },
            error: function (data) {
                var errors = data.responseJSON;
                // console.log(errors);
                alert(
                    'error occured when pulling production processes'
                );
            }

        });
    }

    function getNumberOfPieces(product_code, net) {

        if (product_code == 'G1169' && net > 0) {
            var pieces = Math.round(net) / 3;
            $('#no_of_pieces').val(Math.round(pieces));
        }

        if ((product_code == 'G1119') && net > 0) {
            var pieces = Math.round(net) / 0.53;
            $('#no_of_pieces').val(Math.round(pieces));
        }

        if ((product_code == 'G1121') && net > 0) {
            var pieces = Math.round(net) / 1.7;
            $('#no_of_pieces').val(Math.round(pieces));
        }

        if (product_code == 'G1189' && net > 0) {
            var pieces = Math.round(net) / 2.30;
            $('#no_of_pieces').val(Math.round(pieces));
        }

        if (product_code == 'G1164' && net > 0) {
            var pieces = Math.round(net) / 4.66;
            $('#no_of_pieces').val(Math.round(pieces));
        }

        if (product_code == 'G1126' && net > 0) {
            var pieces = Math.round(net) / 6.49;
            $('#no_of_pieces').val(Math.round(pieces));
        }

    }

    function getNumberOfPiecesEdit(product_code, net) {

        if (product_code == 'G1169' && net > 0) {
            var pieces = Math.round(net) / 3;
            $('#edit_no_pieces').val(Math.round(pieces));

        } else if (product_code == 'G1126' && net > 0) {
            var pieces = Math.round(net) / 6.49;
            $('#edit_no_pieces').val(Math.round(pieces));

        } else if (product_code == 'G1119' && net > 0) {
            var pieces = Math.round(net) / 0.53;
            $('#edit_no_pieces').val(Math.round(pieces));

        } else if (product_code == 'G1189' && net > 0) {
            var pieces = Math.round(net) / 2.30;
            $('#edit_no_pieces').val(Math.round(pieces));

        } else if (product_code == 'G1164' && net > 0) {
            var pieces = Math.round(net) / 4.66;
            $('#edit_no_pieces').val(Math.round(pieces));

        } else if (product_code == 'G1121' && net > 0) {
            var pieces = Math.round(net) / 1.7;
            $('#edit_no_pieces').val(Math.round(pieces));
        }

    }

    function setProductCode() {
        var edit_product_type = $('#edit_product_type').val();
        if (edit_product_type == null) {
            alert('please select item')
        } else if (edit_product_type == 1) {
            $('#product_type').val("Main Product");
        } else if (edit_product_type == 2) {
            $('#product_type').val("By Product");
        } else if (edit_product_type == 3) {
            $('#product_type').val("Intake");
        }
        $('#productTypesModal').modal('hide');

    }

    function getProductionProcessName(process_code) {
        if (process_code == 0) {
            return "Behead Pig";

        }
        if (process_code == 1) {
            return "Behead Sow";

        }
        if (process_code == 2) {
            return "Breaking Pig, (Leg, Mdl, Shld)";

        }
        if (process_code == 3) {
            return "Breaking Sow into Leg,Mid,&Shd";

        }
        if (process_code == 4) {
            return "Debone Pork Leg";

        }
        if (process_code == 5) {
            return "Debone Pork Middle";

        }
        if (process_code == 6) {
            return "Debone Pork Shoulder";

        }
        if (process_code == 0) {
            return "Debone Pork Leg";

        }
        if (process_code == 7) {
            return "Debone Sow";

        }
        if (process_code == 8) {
            return "Slicing parts for slices, portions";

        }
        if (process_code == 9) {
            return "Trim & Roll";

        }
        if (process_code == 10) {
            return "Fat Stripping Rinds";

        }
        if (process_code == 11) {
            return "Rolling Pork Legs";

        }
        if (process_code == 12) {
            return "Rolling Pork Shoulders";

        }
        if (process_code == 13) {
            return "Bones";

        }
    }

    function getNet() {
        var reading = document.getElementById('reading').value;
        var tareweight = document.getElementById('tareweight').value;
        var net = document.getElementById('net');
        new_net_value = parseFloat(reading) - parseFloat(tareweight);
        net.value = Math.round((new_net_value + Number.EPSILON) * 100) / 100;

        //get number of pieces
        var code = $('#product').val();

        if (code != "" && net.value > 0) {
            var product_code = code.split('-')[1];
            getNumberOfPieces(product_code, net.value);

        } else {

            $('#no_of_pieces').val(0);
        }

    }

    function validateOnSubmit() {
        $valid = true;

        var net = $('#net').val();
        var product_type = $('#product_type').val();
        var no_of_pieces = $('#no_of_pieces').val();
        var process = $('#production_process').val();
        var process_substring = process.substr(0, process.indexOf(' '));
        let loading_val = $('#loading_value').val()

        if (loading_val != 1) {
            alert('please wait for loading process code to complete')
            $valid = false;
        }

        if (net == "" || net <= 0.00) {
            $valid = false;
            alert("Please ensure you have valid netweight.");
        }

        //check main product pieces
        if (product_type == 'Main Product' && no_of_pieces < 1 && process_substring == 'Debone') {
            $valid = false;
            alert("Please ensure you have inputed no_of_pieces,\nThe item is a main product in deboning process");
        }
        return $valid;
    }

    function validateOnEditSubmit() {
        $valid = true;

        let loading_val = $('#loading_val_edit').val()

        if (loading_val != 1) {
            alert('please wait for loading process code to complete')
            $valid = false;
        }
        return $valid;
    }

    //read scale
    function getScaleReading() {
        var comport = $('#comport_value').val();

        if (comport != null) {
            $.ajax({
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                        .attr('content')
                },
                url: "{{ url('butchery/read-scale-api-service') }}",

                data: {
                    'comport': comport,

                },
                dataType: 'JSON',
                success: function (data) {
                    // console.log(data);

                    var obj = JSON.parse(data);
                    // console.log(obj.success);

                    if (obj.success == true) {
                        var reading = document.getElementById('reading');
                        console.log('weight: ' + obj.response);
                        reading.value = obj.response;
                        getNet();

                    } else if (obj.success == false) {
                        alert('error occured in response: ' + obj.response);

                    } else {
                        alert('No response from service');

                    }

                },
                error: function (data) {
                    var errors = data.responseJSON;
                    // console.log(errors);
                    alert('error occured when sending request');
                }
            });
        } else {
            alert("Please set comport value first");
        }
    }

</script>
@endsection
