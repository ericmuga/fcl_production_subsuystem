@extends('layouts.slaughter_master')

@section('content')

<!-- weigh -->
<form id="form-slaughter-weigh" class="form-prevent-multiple-submits" action="{{ route('save_weigh_data') }}"
    method="post">
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
                @php
                    $allowedUsernames = explode(',', config('app.manual_weights_usernames'));
                    $allowedUsernames = array_map('strtolower', $allowedUsernames);  
                    $sessionUsername = strtolower(Session::get('session_username'));
                @endphp

                @if (in_array($sessionUsername, $allowedUsernames))
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="manual_weight">
                        <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                    </div>
                @else
                    {{-- Debugging message for testing --}}
                    <script>console.log("Session username not allowed:", "{{ $sessionUsername }}");</script>
                @endif 
                <br>
                <div class="form-group"
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
                            <input type="hidden" class="input_checks" id="loading_value" value="0">
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
                    <input type="checkbox" class="form-check-input" id="disease_investigation" name="disease_investigation" style="transform: scale(1.5);">
                    <label for="disease_investigation">Held for further investigation?</label>
                </div>
                <div class="form-group" style="padding-top: 5%">
                    <button type="submit" id="btn_save" onclick="return validateOnSubmit()"
                        class="btn btn-primary btn-lg btn-prevent-multiple-submits"><i class="fa fa-paper-plane"
                            aria-hidden="true"></i>
                        Save</button>
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
<!--End weigh -->

<!-- missing slap modal -->
<div class="modal fade" id="slapModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-edit-scale" class="form-prevent-multiple-submits" action="{{ route('save_missing_data') }}"
            method="post">
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
                        <input type="text" class="form-control" id="ms_classification" name="ms_classification"
                            placeholder="" readonly required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary btn-prevent-multiple-submits" onclick="return validateOnSubmit2()"
                        type="submit">
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
                    <div class="table-responsive">
                        <table id="example1" class="table table-striped table-bordered table-hover table-responsive">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Receipt No.</th>
                                    <th>Slapmark </th>
                                    <th> Code</th>
                                    <th> Type</th>
                                    <th> Scale Reading(kgs)</th>
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
                                    <th> Scale Reading (kgs)</th>
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
                    <div class="table-responsive">
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

        // $("#form-slaughter-weigh").submit(function () {
        //     let loading_val = $('#loading_value').val()
        //     if (loading_val != 1) {
        //         alert('please wait for loading data to complete')
        //         return false;
        //     }
        // });

        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        $(".readonly").keydown(function (e) {
            e.preventDefault();
            alert('please ensure you have net reading from scale');
        });

        $("#reading").keyup(function (e) {
            e.preventDefault();
            var net = $('#net').val();
            var meat_pc = $('#meat_percent').val();
            if (net > 0 && meat_pc > 0) {
                getClassificationCode();
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
        $('#loading').collapse('show');
        $('#loading_value').val(0)
        $('#btn_save').prop('disabled', true)
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
                        $('#btn_save').prop('disabled', false)
                        $('#loading').collapse('hide');
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

                                //update loading_val
                                $('#loading_value').val(1)
                                $(".btn-prevent-multiple-submits").attr('disabled', false);

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
        var carcass_type = $('#carcass_type').val();
        var total_by_vendor = $('#total_by_vendor').val();
        var total_per_slap = $('#total_per_slap').val();
        var class_code = $('#classification_code').val();

        if (carcass_type != 'G0113' && !(net > 10 && net <= 300)) {
            alert("Please ensure you have valid netweight of range 10-300kgs .");
            return false;
        }

        if (class_code == '') {
            alert("Please ensure classification code has picked before saving.");
            return false;
        }

        if (slapmark != null && total_by_vendor == total_per_slap) {
            alert("You have exhausted vendor received Qty.");
            return false;
        }
    }

    function validateOnSubmit2() {
        var net = $('#net').val();
        var carcass_type = $('#ms_carcass_type').val();

        if (carcass_type != 'G0113' && !(net > 10 && net <= 300)) {
            alert("Please ensure you have valid netweight of range 10-300kgs .");
            return false;
        }
    }

    //classification code logic on input
    function getClassificationCode() {
        var s_weight = parseFloat($('#settlement_weight').val());

        if (!s_weight) {
            alert('please take the reading first. reading is: ' + s_weight);
            return;
        }

        var rosemark_vendor_list = ["PF99901", "PF99902", "PF99903", "PF99904", "PF99905"];
        var meat_percent = parseFloat($('#meat_percent').val());
        var vendor_number = $('#vendor_no').val();
        var carcass_type = $('#carcass_type').val();
        var classification_code = document.getElementById('classification_code');

        // suckling pigs classification
        if (carcass_type === "G0113") {
            classification_code.value = "*";
            return;
        }

        if (isNaN(meat_percent)) {
            classification_code.value = "";
            console.log("Meat % is not set; cannot determine classification code");
            return;
        }

        if (!vendor_number) {
            alert("vendor number is not available");
            return;
        }

        var isRosemark = rosemark_vendor_list.includes(vendor_number);

        // Helper to prefix Rosemark classes
        var prefix = isRosemark ? 'RM-' : '';

        if (carcass_type === "G0110") {
            // Baconers (standard pigs)

            // Sub-40
            if (s_weight < 40) {
                classification_code.value = isRosemark ? "RMPK SUB-40" : "PK SUB-40";
                return;
            }

            // CLS01 – Export Large Eye: 60–75kg, fat 8–10mm
            if (s_weight >= 60 && s_weight <= 75 && meat_percent >= 8 && meat_percent <= 10) {
                classification_code.value = prefix + "CLS01";
                return;
            }

            // CLS02 – Standard Pig (Heavy Hogs): 60–90kg, fat 11–13mm
            if (s_weight >= 60 && s_weight <= 90 && meat_percent >= 11 && meat_percent <= 13) {
                classification_code.value = prefix + "CLS02";
                return;
            }

            // CLS03 – Standard Pig: 56–59kg, all fat levels
            if (s_weight >= 56 && s_weight <= 59) {
                classification_code.value = prefix + "CLS03";
                return;
            }

            // CLS03 – Standard Pig: 60–75kg, fat 1–7mm & 14–100mm
            if (s_weight >= 60 && s_weight <= 75 && (meat_percent <= 7 || meat_percent >= 14)) {
                classification_code.value = prefix + "CLS03";
                return;
            }

            // CLS04 – Production (Manufacturing): 76–90kg, fat 1–10mm & 14–100mm
            if (s_weight >= 76 && s_weight <= 90 && (meat_percent <= 10 || meat_percent >= 14)) {
                classification_code.value = prefix + "CLS04";
                return;
            }

            // CLS04 – Production (Manufacturing): 91–100kg, all fat levels
            if (s_weight >= 91 && s_weight <= 100) {
                classification_code.value = prefix + "CLS04";
                return;
            }

            // CLS05 – Production (Porkers): 50–55kg, all fat levels
            if (s_weight >= 50 && s_weight <= 55) {
                classification_code.value = prefix + "CLS05";
                return;
            }

            // CLS06 – Production (Porkers): 40–49kg, all fat levels
            if (s_weight >= 40 && s_weight <= 49) {
                classification_code.value = prefix + "CLS06";
                return;
            }

            // CLS07 – Production (Heavy Hogs): 101–120kg, all fat levels
            if (s_weight >= 101 && s_weight <= 120) {
                classification_code.value = prefix + "CLS07";
                return;
            }

            // CLS08 – Production (Heavy Hogs): Above 120kg, all fat levels
            if (s_weight > 120) {
                classification_code.value = prefix + "CLS08";
                return;
            }

            // Fallback for any unmapped G0110
            classification_code.value = prefix + "CLS09";
            return;
        }

        // Sows and suckling pigs (Rosemark/non-Rosemark specific labels)
        if (carcass_type === "G0111") {
            classification_code.value = isRosemark ? "RMSOW-3P" : "SOW-3P";
            return;
        }

        if (carcass_type === "G0113") {
            if (s_weight >= 5 && s_weight < 8) {
                classification_code.value = isRosemark ? "RM3P-SK4" : "3P-SK4";
            } else if (s_weight >= 9 && s_weight < 20) {
                classification_code.value = "3P-SK5";
            } else {
                classification_code.value = "";
            }
            return;
        }

        // Default if nothing matches
        classification_code.value = "";
        console.log("Unable to determine classification code, please check meat % and settlement weight");
    }

    function getClassificationCode2() {
        var s_weight = parseFloat($('#ms_settlement_weight').val());
        var meat_percent = parseFloat($('#ms_meat_pc').val());
        var carcass_type = $('#ms_carcass_type').val();
        var $classification = $('#ms_classification');

        if (!s_weight) {
            alert('Please get scale reading. Current settlement is: ' + s_weight);
            $classification.val("");
            return;
        }

        // Baconers (standard pigs)
        if (carcass_type === "G0110") {
            if (isNaN(meat_percent)) {
                $classification.val("");
                console.log("Meat % is not set; cannot determine classification code");
                return;
            }

            // Sub-40
            if (s_weight < 40) {
                $classification.val("PK SUB-40");
                return;
            }

            // CLS01 – Export Large Eye: 60–75kg, fat 8–10mm
            if (s_weight >= 60 && s_weight <= 75 && meat_percent >= 8 && meat_percent <= 10) {
                $classification.val("CLS01");
                return;
            }

            // CLS02 – Standard Pig (Heavy Hogs): 60–90kg, fat 11–13mm
            if (s_weight >= 60 && s_weight <= 90 && meat_percent >= 11 && meat_percent <= 13) {
                $classification.val("CLS02");
                return;
            }

            // CLS03 – Standard Pig: 56–59kg, all fat levels
            if (s_weight >= 56 && s_weight <= 59) {
                $classification.val("CLS03");
                return;
            }

            // CLS03 – Standard Pig: 60–75kg, fat 1–7mm & 14–100mm
            if (s_weight >= 60 && s_weight <= 75 && (meat_percent <= 7 || meat_percent >= 14)) {
                $classification.val("CLS03");
                return;
            }

            // CLS04 – Production (Manufacturing): 76–90kg, fat 1–10mm & 14–100mm
            if (s_weight >= 76 && s_weight <= 90 && (meat_percent <= 10 || meat_percent >= 14)) {
                $classification.val("CLS04");
                return;
            }

            // CLS04 – Production (Manufacturing): 91–100kg, all fat levels
            if (s_weight >= 91 && s_weight <= 100) {
                $classification.val("CLS04");
                return;
            }

            // CLS05 – Production (Porkers): 50–55kg, all fat levels
            if (s_weight >= 50 && s_weight <= 55) {
                $classification.val("CLS05");
                return;
            }

            // CLS06 – Production (Porkers): 40–49kg, all fat levels
            if (s_weight >= 40 && s_weight <= 49) {
                $classification.val("CLS06");
                return;
            }

            // CLS07 – Production (Heavy Hogs): 101–120kg, all fat levels
            if (s_weight >= 101 && s_weight <= 120) {
                $classification.val("CLS07");
                return;
            }

            // CLS08 – Production (Heavy Hogs): Above 120kg, all fat levels
            if (s_weight > 120) {
                $classification.val("CLS08");
                return;
            }

            // Fallback for any unmapped G0110
            $classification.val("CLS09");
            return;
        }

        // Sows
        if (carcass_type === "G0111") {
            $classification.val("SOW-3P");
            return;
        }

        // Suckling pigs
        if (carcass_type === "G0113") {
            if (s_weight >= 5 && s_weight < 8) {
                $classification.val("3P-SK4");
            } else if (s_weight >= 9 && s_weight < 20) {
                $classification.val("3P-SK5");
            } else {
                $classification.val("");
            }
            return;
        }

        // Default if nothing matches
        $classification.val("");
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

            var cold_weight = 0.975 * Math.round(net);
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

            var cold_weight = 0.975 * Math.round(net);
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

            var cold_weight = 0.975 * Math.round(net);
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

            var cold_weight = 0.975 * Math.round(net);
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
