@extends('layouts.slaughter_master')

@section('content')

<!-- weigh -->
<form id="form-slaughter-weigh" action="{{ route('save_weigh_data') }}" method="post">
    @csrf
    <div class="card-group">
        <div class="card ">
            <div class="card-body text-center">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" onclick="getScaleReading()" class="btn btn-primary btn-lg"><i
                                    class="fas fa-balance-scale"></i> Weigh</button>
                        </div>
                        <div class="col-md-6">
                            <small><label>Reading from ComPort:</label><strong><input type="text"
                                        style="text-align: center; border:none" id="comport_value"
                                        value="{{ $configs[0]->comport?? "" }}" disabled></strong></small>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Reading</label>
                    <input type="number" step="0.01" class="form-control" id="reading" name="reading" value="0.00"
                        oninput="getNet()" placeholder="" readonly required>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="manual_weight">
                    <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                </div> <br>
                <div class="form-group">
                    <label for="exampleInputPassword1">Tare-Weight</label>
                    <input type="number" class="form-control" id="tareweight" name="tareweight"
                        value="{{ number_format($configs[0]->tareweight, 2)?? "" }}" readonly>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="exampleInputPassword1">Net</label>
                            <input type="number" class="form-control" id="net" name="net" value="0.00" step="0.01"
                                placeholder="" readonly required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="exampleInputPassword1">Settlement Weight</label>
                            <input type="number" class="form-control" id="settlement_weight" name="settlement_weight"
                                value="0.00" step="0.01" placeholder="" readonly required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-8">
                            <label for="exampleInputPassword1">Slapmark</label>
                            <select class="form-control select2" name="slapmark" id="slapmark" required>
                                @foreach($receipts as $receipt)
                                @if (old('slapmark') == $receipt->vendor_tag)
                                <option value="{{ $receipt->vendor_tag }}" selected>{{ ucwords($receipt->vendor_tag) }}
                                </option>
                                @else
                                <option value="{{ $receipt->vendor_tag }}">{{ ucwords($receipt->vendor_tag) }}</option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4" style="padding-top: 7%">
                            <button class="btn btn-outline-info btn-sm form-control" type="button" data-toggle="modal"
                                data-target="#slapModal">
                                <strong>slapmark?</strong>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Receipt No.</label>
                    <input type="text" class="form-control" value="" name="receipt_no" id="receipt_no" placeholder=""
                        readonly required>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Carcass Type</label>
                    <select class="form-control select2" name="carcass_type" id="carcass_type" required>
                        @foreach($carcass_types as $type)
                        <option value="{{ $type->code }}" @if($loop->first) selected="selected" @endif>
                            {{ ucwords($type->description) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Vendor Number</label>
                    <input type="text" class="form-control" value="" name="vendor_no" id="vendor_no" placeholder=""
                        readonly required>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Vendor Name</label>
                    <input type="text" class="form-control" name="vendor_name" id="vendor_name" placeholder="" readonly
                        required>
                </div>
            </div>
        </div>
        <div class="card ">
            <div class="card-body text-center">
                <div class="row form-group">
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Total Received From Vendor </label>
                        <input type="text" class="form-control" value="" name="delivered_per_vendor"
                            id="delivered_per_vendor" placeholder="" readonly required>
                    </div>
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Total Received per slapmark </label>
                        <input type="text" class="form-control" value="" name="total_by_vendor" id="total_by_vendor"
                            placeholder="" readonly required>
                    </div>
                </div>
                <div class=" row form-group">
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Total weighed </label>
                        <input type="text" class="form-control" value="" name="total_per_slap" id="total_per_slap"
                            placeholder="" readonly required>
                    </div>
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Total remaining </label>
                        <input type="text" class="form-control" value="" name="total_remaining" id="total_remaining"
                            placeholder="" readonly required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Meat %</label>
                    <input type="number" class="form-control" id="meat_percent" value="" name="meat_percent"
                        oninput="getClassificationCode()" placeholder="" readonly required>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Classification Code</label>
                    <input type="text" class="form-control" id="classification_code" name="classification_code"
                        placeholder="" readonly required>
                </div>
                <div class="form-group">
                    <button type="submit" onclick="return validateOnSubmit()" class="btn btn-primary btn-lg"><i
                            class="fa fa-paper-plane" aria-hidden="true"></i>
                        Save</button>
                </div>
            </div>
        </div>
    </div>
</form>
<!--End weigh -->

<!-- missing slap modal -->
<div class="modal fade" id="slapModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-edit-scale" action="{{ route('save_missing_data') }}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Record missing slaps</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class=" row form-group">
                        <div class="col-md-4">
                            <label for="exampleInputPassword1">Reading</label>
                            <input type="number" class="form-control readonly" id="ms_reading" name="ms_reading"
                                value="" step="0.01" placeholder="" required readonly />
                        </div>
                        <div class="col-md-4">
                            <label for="exampleInputPassword1">Net</label>
                            <input type="number" class="form-control readonly" id="ms_net" name="ms_net" value=""
                                step="0.01" placeholder="" required readonly />
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputPassword1">Settlement Weight</label>
                                <input type="number" class="form-control" id="ms_settlement_weight"
                                    name="ms_settlement_weight" value="0.00" step="0.01" placeholder="" readonly
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="baud">Slapmark:</label>
                        <input type="text" class="form-control" id="ms_slap" name="ms_slap" value="" placeholder=""
                            required>
                    </div>
                    <div class="form-group">
                        <label for="baud">Carcass-Type:</label>
                        <select class="form-control select2" name="ms_carcass_type" id="ms_carcass_type" required>
                            @foreach($carcass_types as $type)
                            <option value="{{ $type->code }}" @if($loop->first) selected="selected" @endif>
                                {{ ucwords($type->description) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="baud">Meat %:</label>
                        <input type="number" class="form-control" id="ms_meat_pc" name="ms_meat_pc" value=""
                            placeholder="" oninput="getClassificationCode2()" required>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Classification Code</label>
                        <input type="text" class="form-control" id="ms_classification"
                            name="ms_classification" placeholder="" readonly
                            required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" onclick="return validateOnSubmit2()" type="submit">
                        <i class="fa fa-save"></i> Post
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- end missing slap -->
<hr>

<div class="div">
    <button class="btn btn-primary " data-toggle="collapse" data-target="#slaughter_entries"><i class="fa fa-plus"></i>
        Entries
    </button>
</div>

<div id="slaughter_entries" class="collapse">
    <hr>
    <div class="row">
        <!-- slaughter data Table-->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"></h3>
                    <h3 class="card-title"> Weighed Entries | <span id="subtext-h1-title"><small> view weighed ordered
                                by latest</small> </span></h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="hidden" hidden>{{ $i = 1 }}</div>
                    <table id="example1" class="table table-striped table-bordered table-hover table-responsive">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Receipt No.</th>
                                <th>Slapmark </th>
                                <th> Code</th>
                                <th> Type</th>
                                <th> weight(kgs)</th>
                                <th>Meat %</th>
                                <th>Classification</th>
                                <th>Slaughter Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Receipt No.</th>
                                <th>Slapmark </th>
                                <th> Code</th>
                                <th> Type</th>
                                <th> weight (kgs)</th>
                                <th>Meat %</th>
                                <th>Classification</th>
                                <th>Slaughter Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($slaughter_data as $data)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $data->receipt_no }}</td>
                                <td>{{ $data->slapmark }}</td>
                                <td>{{ $data->item_code }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ number_format($data->actual_weight, 2) }}</td>
                                <td>{{ $data->meat_percent }}</td>
                                <td>{{ $data->classification_code }}</td>
                                <td>{{ $data->created_at }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>

        <!-- missing slaps Table-->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"></h3>
                    <h3 class="card-title"> Missing Slapmarks Entries | <span id="subtext-h1-title"><small> view weighed
                                ordered
                                by latest</small> </span></h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="hidden" hidden>{{ $i = 1 }}</div>
                    <table id="example2" class="table table-striped table-bordered table-hover table-responsive">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Slapmark</th>
                                <th> Code</th>
                                <th> Type</th>
                                <th> Weight(kgs)</th>
                                <th>Meat % </th>
                                <th>Classification </th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Slapmark</th>
                                <th> Code</th>
                                <th> Type</th>
                                <th> Weight(kgs)</th>
                                <th>Meat % </th>
                                <th>Classification </th>
                                <th>Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($slaps as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->slapmark }}</td>
                                <td>{{ $data->item_code }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ number_format($data->actual_weight, 2) }}</td>
                                <td>{{ $data->meat_percent }}</td>
                                <td>{{ $data->classification_code }}</td>
                                <td>{{ $data->created_at }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
        <!-- /.col -->
    </div>
</div>
<!--End users Table-->

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        loadWeighData();

        $(".readonly").keydown(function (e) {
            e.preventDefault();
            alert('please ensure you have net reading from scale');
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

        $('#carcass_type').change(function () {
            var net = $('#net').val();

            loadWeighData();
            getSettlementWeight(net);
            getClassificationCode();

        });

        $('#ms_carcass_type').change(function () {
            var net = $('#net').val();

            getSettlementWeight2(net);
            getClassificationCode2();

        });

        /* Start weigh data ajax */
        $('#slapmark').change(function () {
            loadWeighData();
        });
        /* End weigh data ajax */

    });

    function loadWeighData() {
        /* Start weigh data ajax */
        var slapmark = $('#slapmark').val();
        var carcass_type = $('#carcass_type').val();

        // transcoding from carcass code to livestock code to look up in the receipt ledger
        if (carcass_type == "G0110") {
            carcass_type = "G0101"; // pig livestock
        }
        if (carcass_type == "G0111") {
            carcass_type = "G0102"; // sow livestock
        }
        if (carcass_type == "G0113") {
            carcass_type = "G0104"; // suckling livestock
        }

        if (slapmark != null) {
            $.ajax({
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ url('scale-ajax') }}",
                data: {
                    'slapmark': slapmark,
                    'carcass_type': carcass_type,

                },
                dataType: 'JSON',
                success: function (res) {
                    if (res) {
                        //console.log(res);
                        var str = JSON.stringify(res);
                        var obj = JSON.parse(str);

                        $('#receipt_no').val(obj.receipt_no);
                        $('#vendor_no').val(obj.vendor_no);
                        $('#vendor_name').val(obj.vendor_name);

                        var meat_percent = document.getElementById('meat_percent');
                        meat_percent.readOnly = false;

                        // focus on meat percentage
                        $('#meat_percent').focus();

                        // loadMoreDetailsAjax;
                        var vendor_number = $('#vendor_no').val();
                        $.ajax({
                            type: "GET",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                                    .attr('content')
                            },
                            url: "{{ url('scale-ajax-2') }}",
                            data: {
                                'vendor_no': vendor_number,
                                'slapmark': slapmark,
                                'carcass_type': carcass_type,

                            },
                            dataType: 'JSON',
                            success: function (data) {
                                var str2 = JSON.stringify(data);
                                var obj2 = JSON.parse(str2);

                                $('#total_by_vendor').val(obj2
                                    .total_per_slap);

                                $('#delivered_per_vendor').val(obj2
                                    .total_per_vendor);

                                $('#total_per_slap').val(obj2
                                    .total_weighed);

                                $('#total_remaining').val(obj2
                                    .total_per_slap - obj2
                                    .total_weighed);


                            },
                            error: function (data) {
                                var errors = data.responseJSON;
                                console.log(errors);
                            }
                        });

                        //end ajax 2
                    }
                }
            });

        } else {
            $("#receipt_no").empty();
            $("#total_by_vendor").empty();
            $("#total_per_slap").empty();
            $("#vendor_no").empty();
            $("#vendor_name").empty();
        }
        /* End weigh data ajax */
    }


    function validateOnSubmit() {
        var net = $('#net').val();
        var slapmark = $('#slapmark').val();
        var total_by_vendor = $('#total_by_vendor').val();
        var total_per_slap = $('#total_per_slap').val();

        if (net == "" || net <= 0.00) {
            alert("Please ensure you have valid netweight.");
            return false;
        }

        if (slapmark != null && total_by_vendor == total_per_slap) {
            alert("You have exhausted vendor received Qty.");
            return false;
        }
    }

    function validateOnSubmit2(){
        var net = $('#net').val();

        if (net == "" || net <= 0.00) {
            alert("Please ensure you have valid netweight.");
            return false;
        }
    }

    //classification code logic on input
    function getClassificationCode() {
        var s_weight = $('#settlement_weight').val();

        if (s_weight != 0.00) {
            var rosemark_vendor_list = ["PF99901", "PF99902", "PF99903", "PF99904", "PF99905"];
            var meat_percent = $('#meat_percent').val();
            var vendor_number = $('#vendor_no').val();
            var carcass_type = $('#carcass_type').val();
            var total_received = $('#delivered_per_vendor').val();

            var classification_code = document.getElementById('classification_code');

            if (meat_percent != null) {

                if (vendor_number != null) {
                    // check if vendor number exists in special vendor list
                    if (rosemark_vendor_list.includes(vendor_number)) {
                        // Rosemark classification

                        if (carcass_type != "G0113" && s_weight < 40) {
                            $('#classification_code').val("RMPK SUB40");
                        }

                        else if ((meat_percent >= 8 && meat_percent <= 10) && carcass_type == "G0110" && (s_weight >= 60 &&
                                s_weight <= 75)) {
                            classification_code.value = "RM-CLS01";
                        }

                        else if ((meat_percent < 8 || meat_percent > 10) && carcass_type == "G0110" && (s_weight >= 60 &&
                                s_weight <= 75)) {
                            classification_code.value = "RM-CLS02";
                        }

                        else if (carcass_type == "G0110" && (s_weight >= 56 &&
                                s_weight <= 59)) {
                            classification_code.value = "RM-CLS02";
                        }

                        else if (carcass_type == "G0110" && (s_weight >= 76 &&
                                s_weight <= 85)) {
                            classification_code.value = "RM-CLS03";
                        }

                        else if (carcass_type == "G0110" && (s_weight >= 50 &&
                                s_weight <= 55)) {
                            classification_code.value = "RM-CLS04";
                        }

                        else if (carcass_type == "G0110" && (s_weight >= 40 &&
                                s_weight <= 49)) {
                            classification_code.value = "RM-CLS05";
                        }

                        else if (carcass_type == "G0110" && (s_weight >= 86 && s_weight <= 100)) {
                            classification_code.value = "RM-CLS06";
                        }

                        else if (carcass_type == "G0110" && (s_weight >= 101 && s_weight <= 120)) {
                            classification_code.value = "RM-CLS07";
                        }

                        else if (carcass_type == "G0110" && s_weight > 120) {
                            classification_code.value = "RM-CLS08";
                        }

                        else if (carcass_type == "G0111") {
                            classification_code.value = "SOW-RM";
                        }

                        else if (carcass_type == "G0113" && (s_weight >= 5 && s_weight <= 7)) {
                            classification_code.value = "RM-SK1";
                        }

                        else if (carcass_type == "G0113" && (s_weight >= 7 && s_weight < 9)) {
                            classification_code.value = "RM-SK2";
                        }

                        else if (carcass_type == "G0113" && (s_weight >= 9 && s_weight < 16)) {
                            classification_code.value = "RM-SK3";
                        }

                        else if (carcass_type == "G0113" && (s_weight >= 17 && s_weight < 20)) {
                            classification_code.value = "RM-SK4";
                        }
                        else {
                            classification_code.value = "";
                        }

                    } else {
                        // non rosemark vendors classification list
                        if (total_received > 2) {
                            // contract vendors classification

                            if (carcass_type == "G0110" && s_weight < 40) {
                                classification_code.value = "PK SUB-40";
                            }

                            else if ((meat_percent >= 8 && meat_percent <= 10) && carcass_type == "G0110" && (s_weight >=
                                    60 &&
                                    s_weight <= 75)) {
                                classification_code.value = "CLS01";
                            }

                            else if ((meat_percent < 8 || meat_percent > 10) && carcass_type == "G0110" && (s_weight >= 60 &&
                                    s_weight <= 75)) {
                                classification_code.value = "CLS02";
                            }

                            else if (carcass_type == "G0110" && (s_weight >= 56 && s_weight <= 59)) {
                                classification_code.value = "CLS02";
                            }

                            else if (carcass_type == "G0110" && (s_weight >= 76 && s_weight <= 85)) {
                                classification_code.value = "CLS03";
                            }

                            else if (carcass_type == "G0110" && (s_weight >= 50 && s_weight <= 55)) {
                                classification_code.value = "CLS04";
                            }

                            else if (carcass_type == "G0110" && (s_weight >= 40 && s_weight <= 49)) {
                                classification_code.value = "CLS05";
                            }

                            else if (carcass_type == "G0110" && (s_weight >= 86 &&
                                    s_weight <= 100)) {
                                classification_code.value = "CLS06";
                            }

                            else if (carcass_type == "G0110" && (s_weight >= 101 && s_weight <= 120)) {
                                classification_code.value = "CLS07";
                            }

                            else if (carcass_type == "G0110" && s_weight > 120) {
                                classification_code.value = "CLS08";
                            }

                            else if (carcass_type == "G0111") {
                                classification_code.value = "SOW-3P";
                            }

                            else if (carcass_type == "G0113" && (s_weight >= 5 && s_weight < 8)) {
                                classification_code.value = "3P-SK4";
                            }

                            else if (carcass_type == "G0113" && (s_weight >= 9 && s_weight < 20)) {
                                classification_code.value = "3P-SK5";
                            }

                            else {
                                classification_code.value = "";
                            }

                        } else {
                            // non contract vendor classification(less than 2 delivered)

                            if (carcass_type == "G0110" && s_weight < 40) {
                                classification_code.value = "PK SUB-40";
                            }

                            else if ((meat_percent >= 8 && meat_percent <= 10) && carcass_type == "G0110" && (s_weight >=
                                    60 &&
                                    s_weight <= 75)) {
                                classification_code.value = "NC-CLS01";
                            }

                            else if ((meat_percent < 8 || meat_percent > 10) && carcass_type == "G0110" && (s_weight >= 60 &&
                                    s_weight <= 75)) {
                                classification_code.value = "NC-CLS02";
                            }

                            else if (carcass_type == "G0110" && (s_weight >= 56 && s_weight <= 59)) {
                                classification_code.value = "NC-CLS02";
                            }

                            else if (carcass_type == "G0110" && (s_weight >= 76 && s_weight <= 85)) {
                                classification_code.value = "NC-CLS03";
                            }

                            else if (carcass_type == "G0110" && (s_weight >= 50 && s_weight <= 55)) {
                                classification_code.value = "NC-CLS04";
                            }

                            else if (carcass_type == "G0110" && (s_weight >= 40 && s_weight <= 49)) {
                                classification_code.value = "NC-CLS05";
                            }

                            else if (carcass_type == "G0110" && (s_weight >= 86 &&
                                    s_weight <= 100)) {
                                classification_code.value = "NC-CLS06";
                            }

                            else if (carcass_type == "G0110" && (s_weight >= 101 && s_weight <= 120)) {
                                classification_code.value = "NC-CLS07";
                            }

                            else if (carcass_type == "G0110" && s_weight > 120) {
                                classification_code.value = "NC-CLS08";
                            }

                            else if (carcass_type == "G0111") {
                                classification_code.value = "SOW-3P";
                            }

                            else if (carcass_type == "G0113" && (s_weight >= 5 && s_weight < 8)) {
                                classification_code.value = "3P-SK4";
                            }

                            else if (carcass_type == "G0113" && (s_weight >= 9 && s_weight < 20)) {
                                classification_code.value = "3P-SK5";
                            }
                            
                            else {
                                classification_code.value = "";
                            }
                        }
                    }

                } else {
                    //vendor number is null
                    alert("vendor number is not available");
                }

            }
        } else {
            alert('please take the reading first. reading is: ' + s_weight);
        }
    }

    function getClassificationCode2() {
        var s_weight = $('#settlement_weight').val();

        var meat_percent = $('#ms_meat_pc').val();
        var carcass_type = $('#ms_carcass_type').val();

        if (carcass_type == "G0110" && s_weight < 40) {
            $('#ms_classification').val("PK SUB-40");
        }

        else if ((meat_percent >= 8 && meat_percent <= 10) && carcass_type == "G0110" && (s_weight >= 60 &&
                s_weight <= 75)) {
            $('#ms_classification').val("CLS01");
        }

        else if ((meat_percent < 8 || meat_percent > 10) && carcass_type == "G0110" && (s_weight >= 60 &&
                s_weight <= 75)) {
            $('#ms_classification').val("CLS02");
        }

        else if (carcass_type == "G0110" && (s_weight >= 56 && s_weight <= 59)) {
            classification_code.value = "CLS02";
            $('#ms_classification').val("PK SUB-40");
        }

        else if (carcass_type == "G0110" && (s_weight >= 76 && s_weight <= 85)) {
            $('#ms_classification').val("CLS03");
        }
        else if (carcass_type == "G0110" && (s_weight >= 50 && s_weight <= 55)) {
            classification_code.value = "CLS04";
            $('#ms_classification').val("PK SUB-40");
        }

        else if (carcass_type == "G0110" && (s_weight >= 40 && s_weight <= 49)) {
            $('#ms_classification').val("CLS05");
        }

        else if (carcass_type == "G0110" && (s_weight >= 86 &&
                s_weight <= 100)) {
            $('#ms_classification').val("CLS06");
        }

        else if (carcass_type == "G0110" && (s_weight >= 101 && s_weight <= 120)) {
            $('#ms_classification').val("CLS07");
        }

        else if (carcass_type == "G0110" && s_weight > 120) {
            $('#ms_classification').val("CLS08");
        }

        else if (carcass_type == "G0111") {
            $('#ms_classification').val("SOW-3P");
        }

        else if (carcass_type == "G0113" && (s_weight >= 5 && s_weight < 8)) {
            $('#ms_classification').val("3P-SK4");
        }

        else if (carcass_type == "G0113" && (s_weight >= 9 && s_weight < 20)) {
            $('#ms_classification').val("3P-SK5");
        }
        
        else {
            $('#ms_classification').val("");
        }
    }

    function getNet() {
        var reading = document.getElementById('reading').value;
        var ms_reading = document.getElementById('ms_reading');
        var tareweight = document.getElementById('tareweight').value;
        var net = document.getElementById('net');
        var missing_slap_net = document.getElementById('ms_net');
        var new_net_value = parseFloat(reading) - parseFloat(tareweight);

        ms_reading.value = reading;
        net.value = Math.round((new_net_value + Number.EPSILON) * 100) / 100;
        missing_slap_net.value = Math.round((new_net_value + Number.EPSILON) * 100) / 100;

        getSettlementWeight(net.value);
    }

    function getSettlementWeight(net) {
        var carcass_type = $('#carcass_type').val();

        if (carcass_type == 'G0113') {
            //suckling calculation
            var settlement_weight = document.getElementById('settlement_weight');
            var missing_slap_settlement = document.getElementById('ms_settlement_weight');

            var cold_weight = 0.975 * net;
            var settlement_int = Math.floor(cold_weight);
            var settlement_float = (cold_weight) % 1;

            if (settlement_float > 0.54) {
                settlement_weight.value = settlement_int + 1;
                missing_slap_settlement.value = settlement_int + 1;

            } else {
                settlement_weight.value = settlement_int;
                missing_slap_settlement.value = settlement_int;

            }

        } else {
            // baconers, sows calculations
            var settlement_weight = document.getElementById('settlement_weight');
            var missing_slap_settlement = document.getElementById('ms_settlement_weight');

            var cold_weight = 0.975 * net;
            var settlement_int = Math.floor(cold_weight);
            var settlement_float = (cold_weight) % 1;

            if (settlement_float > 0.54) {
                settlement_weight.value = settlement_int + 1;
                missing_slap_settlement.value = settlement_int + 1;

            } else {
                settlement_weight.value = settlement_int;
                missing_slap_settlement.value = settlement_int;

            }
        }

    }

    function getSettlementWeight2(net) {
        var ms_carcass_type = $('#ms_carcass_type').val();

        if (ms_carcass_type == 'G0113') {
            //suckling calculation
            var missing_slap_settlement = document.getElementById('ms_settlement_weight');

            var cold_weight = 0.975 * net;
            var settlement_int = Math.floor(cold_weight);
            var settlement_float = (cold_weight) % 1;

            if (settlement_float > 0.54) {
                missing_slap_settlement.value = settlement_int + 1;

            } else {
                missing_slap_settlement.value = settlement_int;

            }

        } else {
            // baconers, sows calculations
            var missing_slap_settlement = document.getElementById('ms_settlement_weight');

            var cold_weight = 0.975 * net;
            var settlement_int = Math.floor(cold_weight);
            var settlement_float = (cold_weight) % 1;

            if (settlement_float > 0.54) {
                missing_slap_settlement.value = settlement_int + 1;

            } else {
                missing_slap_settlement.value = settlement_int;

            }
        }

    }


    function getScaleReading() {
        var comport = $('#comport_value').val();

        if (comport != null) {
            $.ajax({
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                        .attr('content')
                },
                url: "{{ url('slaughter/read-scale-api-service') }}",

                data: {
                    'comport': comport,

                },
                dataType: 'JSON',
                success: function (data) {
                    //console.log(data);

                    var obj = JSON.parse(data);
                    //console.log(obj.success);

                    if (obj.success == true) {
                        var reading = document.getElementById('reading');
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
