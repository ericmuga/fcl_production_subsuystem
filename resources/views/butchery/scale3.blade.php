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
<form id="form-save-scale3" action="{{ route('butchery_scale3_save') }}" method="post">
    @csrf
    <div class="card-group">
        <div class="card">
            <div class="card-body " style="">
                <div class="form-group">
                    <label for="exampleInputPassword1"> Product Name</label>
                    <select class="form-control select2" name="product" id="product" required>
                        <option value="">Select product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->code }}">
                                {{ ucwords($product->description) }} - {{ $product->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="row form-group">
                    <div class="col-md-8">
                        <div class="form-group" id="product_type_select">
                            <label for="exampleInputPassword1">Product Type</label>
                            <input type="text" class="form-control" id="product_type" value="" name="product_type">
                        </div>
                    </div>
                    <div class="col-md-4" style="padding-top: 7.5%">
                        <button class="btn btn-outline-info btn-sm form-control" id="btn_product_type" type="button"
                            data-toggle="modal" disabled>
                            <strong>Edit?</strong>
                        </button>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-8">
                        <div class="form-group" id="product_type_select">
                            <label for="exampleInputPassword1">Production Process</label>
                            <input type="text" class="form-control" id="process_type" value="" name="process_type">
                        </div>
                    </div>
                    <div class="col-md-4" style="padding-top: 7.5%">
                        <button class="btn btn-outline-info btn-sm form-control" id="btn_process_type" type="button"
                            data-toggle="modal" disabled>
                            <strong>Edit?</strong>
                        </button>
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
                    <label for="exampleInputPassword1">No. of Crates</label>
                    <select class="form-control" name="no_of_crates" id="no_of_crates" required>
                        <option value="" selected disabled>select no. of crates</option>
                        <option>2</option>
                        <option>3</option>
                        <option>4</option>
                        <option>5</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Reading</label>
                    <input type="number" step="0.01" class="form-control" id="reading" name="reading" value="0.00"
                        oninput="getNet()" placeholder="" readonly>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="manual_weight">
                    <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                </div>
            </div>
        </div>

        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <label for="exampleInputPassword1">Scale Tare-Weight</label>
                    <input type="number" class="form-control" id="tareweight" name="tareweight"
                        value="{{ number_format($configs[0]->tareweight, 2) }}" readonly>
                    <input type="hidden" class="form-control " id="default_tareweight"
                        value="{{ number_format($configs[0]->tareweight, 2) }}">
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Net</label>
                    <input type="number" class="form-control" id="net" name="net" value="0.00" step=".01" placeholder=""
                        readonly>
                </div>
                <div class="form-group" style="padding-top: 10%">
                    <button type="submit" onclick="return checkNetOnSubmit()" class="btn btn-primary btn-lg"><i
                            class="fa fa-paper-plane" aria-hidden="true"></i> Save</button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- product type modal -->
<div class="modal fade" id="productTypesModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-product-type" action="#" method="post">
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
                        <select name="edit_product_type" id="edit_product_type" class="form-control select2" required
                            autofocus>
                            @foreach($product_types as $type)
                                <option value="{{ $type->code }}" selected="selected">
                                    {{ ucwords($type->description) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="button" onclick="setProductCode()">
                        <i class="fa fa-save"></i> Edit
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- end product code modal -->

<!-- production process modal -->
<div class="modal fade" id="productionProcessModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-production-process" action="#" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Production Process</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class=" form-group">
                        <select name="edit_production_process" id="edit_production_process"
                            value="{{ old('edit_production_process') }}" class="form-control select2"
                            required autofocus>
                            <option value="" selected disabled>Select process</option>
                            @foreach($processes as $type)
                                <option value="{{ $type->process_code }}">
                                    {{ ucwords($type->process) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="button" onclick="setProductionProcess()">
                        <i class="fa fa-save"></i> Edit
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- end production process modal -->
<hr>

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
                    <table id="example1" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code </th>
                                <th>product </th>
                                <th>Product Type</th>
                                <th>Production Process</th>
                                <th>Weight(kgs)</th>
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
                                <th>Weight(kgs)</th>
                                <th>Date </th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($deboning_data as $data)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td> {{ $data->item_code }}</td>
                                    <td>{{ $helpers->getProductName($data->item_code) }}</td>
                                    <td> {{ $data->product_type }}</td>
                                    <td> {{ $data->process }}</td>
                                    <td> {{ number_format($data->actual_weight, 2) }}</td>
                                    <td> {{ $data->created_at }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
            <!-- /.col -->
        </div>
    </div>
</div>
<!-- slicing ouput data show -->

@endsection

@section('scripts')
<script>
    $(document).ready(function () {

        $("body").on("click", "#btn_product_type", function (a) {
            a.preventDefault();

            var product = $('#product_type').val();
            var code = 1;
            if (product == "By Product") {
                code = 2;
            }
            $('#edit_product_type').val(code);

            $('#productTypesModal').modal('show');
        });

        $("body").on("click", "#btn_process_type", function (a) {
            a.preventDefault();

            $('#productionProcessModal').modal('show');
        });

        $('#product').change(function () {
            var product_code = $('#product').val();
            var product_type = document.getElementById('product_type');
            if (product_code != '') {
                $.ajax({
                    type: "GET",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{ url('product_type_ajax') }}",
                    data: {
                        'product_code': product_code,

                    },
                    dataType: 'JSON',
                    success: function (res) {
                        if (res) {
                            // console.log(res);
                            $('#btn_product_type').prop('disabled', false);
                            $('#btn_process_type').prop('disabled', false);

                            //product type
                            if (res.product_type == 1) {
                                $('#product_type').val("Main Product");
                            } else {
                                $('#product_type').val("By Product");
                            }

                            //process type
                            if (res.process_type == 4) {
                                $('#process_type').val("Debone Pork Leg");
                            } else if (res.process_type == 5) {
                                $('#process_type').val("Debone Pork Middle");
                            } else if (res.process_type == 6) {
                                $('#process_type').val("Debone Pork Shoulder");
                            } else if (res.process_type == 7) {
                                $('#process_type').val("Debone Sow");
                            } else if (res.process_type == 8) {
                                $('#process_type').val(
                                    "Slicing parts for slices, portions");
                            } else if (res.process_type == 9) {
                                $('#process_type').val("Trim & Roll");
                            } else if (res.process_type == 10) {
                                $('#process_type').val("Fat Stripping Rinds");
                            } else if (res.process_type == 11) {
                                $('#process_type').val("Rolling Pork Legs");
                            } else if (res.process_type == 12) {
                                $('#process_type').val("Rolling Pork Shoulders");
                            } else if (res.process_type == 13) {
                                $('#process_type').val("Bones");
                            } else {
                                $('#process_type').val("");
                            }

                        }
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
    });

    function setProductCode() {
        var edit_product_type = $('#edit_product_type').val();
        if (edit_product_type == null) {
            alert('please select item')
        } else if (edit_product_type == 1) {
            $('#product_type').val("Main Product");
        } else if (edit_product_type == 2) {
            $('#product_type').val("By Product");
        }
        $('#productTypesModal').modal('hide');

    }

    function setProductionProcess() {
        var edit_production_process = $('#edit_production_process').val();
        if (edit_production_process == null) {
            alert('please select item')
        } else if (edit_production_process == 4) {
            $('#process_type').val("Debone Pork Leg");
        } else if (edit_production_process == 5) {
            $('#process_type').val("Debone Pork Middle");
        } else if (edit_production_process == 6) {
            $('#process_type').val("Debone Pork Shoulder");
        } else if (edit_production_process == 7) {
            $('#process_type').val("Debone Sow");
        } else if (edit_production_process == 8) {
            $('#process_type').val("Slicing parts for slices, portions");
        } else if (edit_production_process == 9) {
            $('#process_type').val("Trim & Roll");
        } else if (edit_production_process == 10) {
            $('#process_type').val("Fat Stripping Rinds");
        } else if (edit_production_process == 11) {
            $('#process_type').val("Rolling Pork Legs");
        } else if (edit_production_process == 12) {
            $('#process_type').val("Rolling Pork Shoulders");
        } else if (edit_production_process == 13) {
            $('#process_type').val("Bones");
        }
        $('#productionProcessModal').modal('hide');

    }

    function getNet() {
        var reading = document.getElementById('reading').value;
        var tareweight = document.getElementById('tareweight').value;
        var net = document.getElementById('net');
        new_net_value = parseFloat(reading) - parseFloat(tareweight);
        net.value = Math.round((new_net_value + Number.EPSILON) * 100) / 100;
    }

    function checkNetOnSubmit() {
        var net = $('#net').val();
        $valid = true;
        if (net == "" || net <= 0.00) {
            $valid = false;
            alert("Please ensure you have valid netweight.");
        };
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
                    console.log(data);

                    var obj = JSON.parse(data);
                    console.log(obj.success);

                    if (obj.success == true) {
                        var reading = document.getElementById('reading');
                        reading.value = obj.response;

                    } else if (obj.success == false) {
                        alert('error occured in response: ' + obj.response);

                    } else {
                        alert('No response from service');

                    }

                },
                error: function (data) {
                    var errors = data.responseJSON;
                    console.log(errors);
                    alert('error occured when sending request');
                }
            });
        } else {
            alert("Please set comport value first");
        }
    }

</script>
@endsection
