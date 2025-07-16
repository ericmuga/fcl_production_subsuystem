@extends('layouts.highcare_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"> {{ $title }} |<small> Bacon Slicing </small></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<form id="form-save-scale3" class="form-prevent-multiple-submits"
    action="{{ route('bacon_slicing_save') }}" method="post">
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
                                        value="{{ $product->shortcode.':'.$product->code.':'.$product->description.':'.$product->process_code.':'.$product->product_type_code.':'.$product->product_type_name.':'.$product->process }}">
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
                            <input type="text" class="form-control" id="product_type" value="" readonly>
                            <input type="hidden" class="form-control" id="product_type_code" value=""
                                name="product_type_code">
                        </div>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-6">
                        <div class="form-group" id="product_type_select">
                            <label for="exampleInputPassword1">Product Name</label>
                            <input type="text" class="form-control" id="product_name" readonly value="">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" id="product_type_select">
                            <label for="exampleInputPassword1">Production Process</label>
                            <input type="text" class="form-control" id="production_process" readonly value="">
                            <input type="hidden" class="form-control" id="production_process_code"
                                name="production_process_code" value="">
                        </div>
                    </div>
                </div>
                <div class="form-group" style="padding-left: 30%;">
                    <button type="button" onclick="getScaleReading()" id="weigh" value=""
                        class="btn btn-primary btn-lg"><i class="fas fa-balance-scale"></i> Weigh</button> <br><br>
                    <small>Reading from <input type="text" id="comport_value" value="{{ $configs[0]->comport?? '' }}"
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
                <div class="row form-group">
                    <div class="crates col-md-4">
                        <label for="exampleInputPassword1">Total Crates </label>
                        <input type="number" class="form-control" id="total_crates" value="" name="total_crates" min="2"
                            placeholder="" required>
                    </div>
                    <div class="crates col-md-4">
                        <label for="exampleInputPassword1">Black Crates </label>
                        <input type="number" class="form-control" id="black_crates" value="" name="black_crates" min="1"
                            placeholder="" required>
                    </div>
                    <div class="col-md-4">
                        <label for="exampleInputPassword1">Total Tare</label>
                        <input type="number" class="form-control" id="tareweight" name="tareweight" value="0.0"
                            readonly>
                    </div>
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
                    <label for="inputEmail3" class="col-form-label">Production Date (dd/mm/yyyy)</label>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="input-group date" id="reservationdate" data-target-input="nearest">
                                <input type="text" class="form-control datetimepicker-input" id="prod_date"
                                    name="prod_date" required data-target="#reservationdate" />
                                <div class="input-group-append" data-target="#reservationdate"
                                    data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row form-group justify-content-center">
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">No. of pieces(Optional) </label>
                        <input type="number" class="form-control" value="" id="no_of_pieces" name="no_of_pieces"
                            >
                    </div>
                </div>
                <div class="row">

                </div>
                <div class="form-group" style="padding-top: 5%">
                    <button type="submit" onclick="return validateOnSubmit()"
                        class="btn btn-primary btn-lg btn-prevent-multiple-submits"><i
                            class="fa fa-paper-plane single-click" aria-hidden="true"></i> Save</button>
                </div>
            </div>
        </div>
    </div>
    <div id="loading" class="collapse">
        <div class="row d-flex justify-content-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>
</form>
<br>

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
                    <h3 class="card-title"> Beef Deboned output data | <span id="subtext-h1-title"><small> entries
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
                                    <th>Total Crates</th>
                                    <th>Scale Weight(kgs)</th>
                                    <th>Total Tare</th>
                                    <th>Net Weight(kgs)</th>
                                    <th>Total Pieces</th>
                                    <th>Created Date </th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Code </th>
                                    <th>product </th>
                                    <th>Product Type</th>
                                    <th>Production Process</th>
                                    <th>Total Crates</th>
                                    <th>Scale Weight(kgs)</th>
                                    <th>Total Tare</th>
                                    <th>Net Weight(kgs)</th>
                                    <th>Total Pieces</th>
                                    <th>Created Date </th>
                                </tr>
                            </tfoot>
                            <tbody>
                                @foreach($bacon_data as $data)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $data->item_code }}</td>
                                        <td>{{ $data->description }}</td>
                                        @if ($data->product_type == 1)
                                            <td>Main</td>
                                        @elseif ($data->product_type == 2)
                                            <td>By-Product</td>
                                        @else
                                            <td>Intake</td>
                                        @endif
                                        <td>{{ $data->process }}</td>
                                        <td>{{ $data->no_of_crates }}</td>
                                        <td>{{ $data->actual_weight }}</td>
                                        <td>{{ number_format($data->actual_weight - $data->net_weight, 2) }}</td>
                                        <td>{{ number_format($data->net_weight, 2) }}</td>
                                        <td>{{ $data->no_of_pieces }}</td>
                                        <td>{{ \Carbon\Carbon::parse($data->created_at)->format('d/m/Y H:i') }}
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

        setProductionDate() //set production date default

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

        $('#manual_weight').change(function () {
            var manual_weight = document.getElementById('manual_weight');
            var reading = document.getElementById('reading');
            if (manual_weight.checked == true) {
                reading.readOnly = false;
                reading.focus();
                $('#reading').val("");
                getNet()

            } else {
                reading.readOnly = true;
                reading.focus();
                $('#reading').val("");
                getNet()
            }
        });

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

        $('#product').change(function () {
            var data = $(this).val();

            var desc = data.split(':')[2];
            var process_code = data.split(':')[3];
            var process = data.split(':')[6];
            var product_type_code = data.split(':')[4];
            var product_type = data.split(':')[5];

            $('#product_type').val(product_type);
            $('#product_type_code').val(product_type_code);
            $('#product_name').val(desc);
            $('#production_process').val(process);
            $('#production_process_code').val(process_code);
        });

        $(".crates").on("input", function () {
            getTareweight()
            getNet()
        });

        $('#reading').on("input", function () {
            getNet()
        });
    });

    const getTareweight = () => {
        let total_crates = $('#total_crates').val()
        let black_crates = $('#black_crates').val()
        let tareweight = 0

        if (parseInt(total_crates) > 0 && parseInt(black_crates)) {
            tareweight = (parseInt(total_crates) * 1.8) + (parseInt(black_crates) * 0.2)
            let formatted = Math.round((tareweight + Number.EPSILON) * 100) / 100;
            $('#tareweight').val(formatted);
        }
    }

    function validateOnSubmit() {
        $valid = true;

        var net = $('#net').val();
        var product_type = $('#product_type').val();
        var no_of_pieces = $('#no_of_pieces').val();
        var process = $('#production_process').val();
        var process_substring = process.substr(0, process.indexOf(' '));

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

    const getNet = () => {
        var reading = document.getElementById('reading').value;
        var tareweight = document.getElementById('tareweight').value;
        var net = document.getElementById('net');

        new_net_value = parseFloat(reading) - parseFloat(tareweight);
        if (tareweight > 0 && reading != '') {
            net.value = Math.round((new_net_value + Number.EPSILON) * 100) / 100;
        } else {
            net.value = 0.0;
        }
    }

    const setProductionDate = () => {
        let dateToday = new Date()

        // Format the date as "DD/MM/YYYY"
        var formattedDateToday =
            `${padZero(dateToday.getDate())}/${padZero(dateToday.getMonth() + 1)}/${dateToday.getFullYear()}`
        $('#prod_date').val(formattedDateToday)

        // Split the date by slashes to get day, month, and year parts
        let dateParts = formattedDateToday.split('/');

        // Get the day part (the second element after splitting)
        let day = dateParts[0]
    }

    const padZero = (num) => {
        return num < 10 ? `0${num}` : num;
    }

    //Date picker
    $('#reservationdate').datetimepicker({
        format : "DD/MM/YYYY"
    });

</script>
@endsection
