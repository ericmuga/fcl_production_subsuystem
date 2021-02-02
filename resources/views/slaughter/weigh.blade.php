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
                            <code><input type="text" id="comport_value"
                                    value="Reading from COM: {{ $configs[0]->comport?? "" }}"
                                    style="border:none" disabled></code>
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
                        value="{{ number_format($configs[0]->tareweight, 2)?? "" }}"
                        readonly>
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
                                <option value="" selected disabled>select</option>
                                @foreach($receipts as $receipt)
                                    <option value="{{ $receipt->vendor_tag }}">
                                        {{ ucwords($receipt->vendor_tag) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4" style="padding-top: 8%">
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
                            <option value="{{ $type->code }}" @if($type->code == "G0110") selected="selected" @endif>
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
                <div class="form-group">
                    <label for="exampleInputPassword1">Total Received from vendor </label>
                    <input type="text" class="form-control" value="" name="total_by_vendor" id="total_by_vendor"
                        placeholder="" readonly required>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Total weighed </label>
                    <input type="text" class="form-control" value="" name="total_per_slap" id="total_per_slap"
                        placeholder="" readonly required>
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
                    <button type="submit" onclick="return validateOnSubmit() && checkQtyCount()"
                        class="btn btn-primary btn-lg"><i class="fa fa-paper-plane" aria-hidden="true"></i>
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
                        <div class="col-md-6">
                            <label for="exampleInputPassword1">Net</label>
                            <input type="number" class="form-control readonly" id="ms_net" name="ms_net" value=""
                                step="0.01" placeholder="" required />
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="exampleInputPassword1">Settlement Weight</label>
                                <input type="number" class="form-control" id="ms_settlement_weight"
                                    name="ms_settlement_weight" value="0.00" step="0.01" placeholder="" readonly required>
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
                            <option value="" selected disabled>select</option>
                            @foreach($carcass_types as $type)
                                <option value="{{ $type->code }}">
                                    {{ ucwords($type->description) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="baud">Meat %:</label>
                        <input type="number" class="form-control" id="ms_meat_pc" name="ms_meat_pc" value=""
                            oninput="getMissingSlapClassification()" placeholder="" required>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Classification Code</label>
                        <input type="text" class="form-control" id="ms_classification" name="ms_classification"
                            placeholder="" readonly required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">
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

<div id="slaughter_entries" class="collapse"><hr>
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
                                <th>Product Code </th>
                                <th>Net weight(kgs)</th>
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
                                <th>Product Code </th>
                                <th>Net weight (kgs)</th>
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
                                    <td>{{ number_format($data->net_weight, 2) }}</td>
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
                                <th>Item Code</th>
                                <th>Net Weight(kgs)</th>
                                <th>Meat % </th>
                                <th>Classification </th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Slapmark</th>
                                <th>Item Code</th>
                                <th>Net Weight(kgs)</th>
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
                                    <td>{{ $data->net_weight }}</td>
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

        /* Start weigh data ajax */
        $('#slapmark').change(function () {
            var slapmark = $(this).val();
            // alert(slapmark);
            if (slapmark != null) {
                $.ajax({
                    type: "GET",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{ url('scale-ajax') }}",
                    data: {
                        'slapmark': slapmark,

                    },
                    dataType: 'JSON',
                    success: function (res) {
                        if (res) {
                            // console.log(res);
                            var str = JSON.stringify(res);
                            var obj = JSON.parse(str);

                            $('#receipt_no').val(obj.receipt_no);
                            $('#vendor_no').val(obj.vendor_no);
                            $('#vendor_name').val(obj.vendor_name);

                            var meat_percent = document.getElementById('meat_percent');
                            meat_percent.readOnly = false;

                            // loadMoreDetailsAjax();
                            $.ajax({
                                type: "GET",
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                                        .attr('content')
                                },
                                url: "{{ url('scale-ajax-2') }}",
                                data: {
                                    'slapmark': slapmark,

                                },
                                dataType: 'JSON',
                                success: function (data) {
                                    var str2 = JSON.stringify(data);
                                    var obj2 = JSON.parse(str2);
                                    $('#total_by_vendor').val(obj2
                                        .total_per_vendor);
                                    $('#total_per_slap').val(obj2
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
        });
        /* End weigh data ajax */

    });


    function validateOnSubmit() {
        var net = $('#net').val();
        var slapmark = $('#slapmark').val();
        var total_by_vendor = $('#total_by_vendor').val();
        var total_per_slap = $('#total_per_slap').val();

        if (net == "" || net <= 0.00) {
            alert("Please ensure you have valid netweight.");
            return false;
        }
    }

    function checkQtyCount() {
        if (slapmark == null) {
            alert("please select slapmark.");
            return false;
        } else if (slapmark != null && total_by_vendor == total_per_slap) {
            alert("You have exhausted vendor received Qty.");
            return false;
        }
    }

    //classification code logic on input
    function getClassificationCode() {

        var special_vendor_no = ["PF99901", "PF99902", "PF99903", "PF99904", "PF99905"];
        var meat_percent = $('#meat_percent').val();
        var vendor_number = $('#vendor_no').val();
        var carcass_type = $('#carcass_type').val();

        //transcoding from from carcass code to livestock code to look up in the receipt ledger
        if (carcass_type == "G0101") {
            carcass_type = "G0110";
        }
        if (carcass_type == "G0102") {
            carcass_type = "G0111";
        }
        if (carcass_type == "G0104") {
            carcass_type = "G0113";
        }

        var s_weight = $('#settlement_weight').val();
        var classification_code = document.getElementById('classification_code');

        if (meat_percent != null) {

            if (vendor_number != null) {
                // check if vendor number exists in special vendor list
                if (special_vendor_no.includes(vendor_number)) {

                    if (meat_percent >= 0 && meat_percent <= 20 && carcass_type == "G0110" && s_weight < 40) {
                        $('#classification_code').val("RMPK-SUB40");
                    }

                    if (meat_percent >= 0 && meat_percent <= 100 && carcass_type == "G0110" && s_weight >= 40 &&
                        s_weight <= 49) {
                        classification_code.value = "RM-CLS05";
                    }

                    if (meat_percent >= 8 && meat_percent <= 10 && carcass_type == "G0110" && s_weight >= 56 &&
                        s_weight <= 59) {
                        classification_code.value = "RM-CLS02";
                    }

                    if (meat_percent >= 0 && meat_percent <= 7 && carcass_type == "G0110" && s_weight >= 56 &&
                        s_weight <= 75) {
                        classification_code.value = "RM-CLS02";
                    }

                    if (meat_percent >= 11 && meat_percent <= 100 && carcass_type == "G0110" && s_weight >= 56 &&
                        s_weight <= 75) {
                        classification_code.value = "RM-CLS02";
                    }

                    if (meat_percent >= 8 && meat_percent <= 10 && carcass_type == "G0110" && s_weight >= 60 &&
                        s_weight <= 75) {
                        classification_code.value = "RM-CLS01";
                    }

                    if (meat_percent >= 0 && meat_percent <= 100 && carcass_type == "G0110" && s_weight >= 76 &&
                        s_weight <= 85) {
                        classification_code.value = "RM-CLS03";
                    }

                    if (meat_percent >= 0 && meat_percent <= 100 && carcass_type == "G0110" && s_weight >= 50 &&
                        s_weight <= 55) {
                        classification_code.value = "RM-CLS04";
                    }

                    if (meat_percent >= 0 && meat_percent <= 100 && carcass_type == "G0110" && s_weight >= 86 &&
                        s_weight <= 100) {
                        classification_code.value = "RM-CLS06";
                    }

                    if (meat_percent >= 0 && meat_percent <= 100 && carcass_type == "G0110" && s_weight >= 101 &&
                        s_weight <= 120) {
                        classification_code.value = "RM-CLS07";
                    }

                    if (meat_percent >= 0 && meat_percent <= 100 && carcass_type == "G0110" && s_weight > 120) {
                        classification_code.value = "RM-CLS08";
                    }

                    if (carcass_type == "G0111") {
                        classification_code.value = "SOW-RM";
                    }

                    if (carcass_type == "G0113" && s_weight >= 5 && s_weight <= 7) {
                        classification_code.value = "RM-SK1";
                    }

                    if (carcass_type == "G0113" && s_weight >= 7 && s_weight < 9) {
                        classification_code.value = "RM-SK2";
                    }

                    if (carcass_type == "G0113" && s_weight >= 9 && s_weight < 16) {
                        classification_code.value = "RM-SK3";
                    }

                    if (carcass_type == "G0113" && s_weight >= 17 && s_weight < 20) {
                        classification_code.value = "RM-SK4";
                    }

                    if (carcass_type == "G0113" && s_weight >= 9 && s_weight < 20) {
                        classification_code.value = "RM-SK5";
                    }

                } else
                // vendor number not in special vendor list
                {
                    if (meat_percent >= 0 && meat_percent <= 20 && carcass_type == "G0110" && s_weight < 40) {
                        classification_code.value = "PK-SUB40";
                    }
                    if (meat_percent >= 0 && meat_percent <= 100 && carcass_type == "G0110" && s_weight >= 40 &&
                        s_weight <= 49.99) {
                        classification_code.value = "CLS05";
                    }
                    if (meat_percent >= 8 && meat_percent <= 10 && carcass_type == "G0110" && s_weight >= 50 &&
                        s_weight <= 59) {
                        classification_code.value = "CLS02";
                    }
                    if (meat_percent >= 0 && meat_percent <= 7 && carcass_type == "G0110" && s_weight >= 56 &&
                        s_weight <= 75) {
                        classification_code.value = "CLS02";
                    }
                    if (meat_percent >= 11 && meat_percent <= 100 && carcass_type == "G0110" && s_weight >= 56 &&
                        s_weight <= 75) {
                        classification_code.value = "CLS02";
                    }
                    if (meat_percent >= 8 && meat_percent <= 10 && carcass_type == "G0110" && s_weight >= 60 &&
                        s_weight <= 75) {
                        classification_code.value = "CLS01";
                    }
                    if (meat_percent >= 0 && meat_percent <= 100 && carcass_type == "G0110" && s_weight >= 76 &&
                        s_weight <= 85) {
                        classification_code.value = "CLS03";
                    }
                    if (meat_percent >= 0 && meat_percent <= 100 && carcass_type == "G0110" && s_weight >= 50 &&
                        s_weight <= 55) {
                        classification_code.value = "CLS04";
                    }
                    if (meat_percent >= 0 && meat_percent <= 100 && carcass_type == "G0110" && s_weight >= 86 &&
                        s_weight <= 100) {
                        classification_code.value = "CLS06";
                    }
                    if (meat_percent >= 0 && meat_percent <= 100 && carcass_type == "G0110" && s_weight >= 101 &&
                        s_weight <= 120) {
                        classification_code.value = "CLS07";
                    }

                    if (meat_percent >= 0 && meat_percent <= 100 && carcass_type == "G0110" && s_weight > 120) {
                        classification_code.value = "CLS08";
                    }
                    if (carcass_type == "G0111") {
                        classification_code.value = "SOW-3P";
                    }
                    if (carcass_type == "G0113" && s_weight >= 5 && s_weight < 8) {
                        classification_code.value = "3P-SK4";
                    }
                    if (carcass_type == "G0113" && s_weight >= 9 && s_weight < 20) {
                        classification_code.value = "3P-SK5";
                    }

                }

            } else {
                //vendor number is null
                alert("vendor number is not available");
            }

        }
    }

    function getMissingSlapClassification() {
        var meat_percent = $('#ms_meat_pc').val();
        var carcass_type = $('#ms_carcass_type').val();
        var s_weight = $('#settlement_weight').val();
        var classification_code = document.getElementById('ms_classification');

        if (meat_percent >= 0 && meat_percent <= 20 && carcass_type == "G0110" && s_weight < 40) {
            classification_code.value = "PK-SUB40";
        }
        if (meat_percent >= 0 && meat_percent <= 100 && carcass_type == "G0110" && s_weight >= 40 &&
            s_weight <= 49) {
            classification_code.value = "CLS05";
        }
        if (meat_percent >= 8 && meat_percent <= 10 && carcass_type == "G0110" && s_weight >= 50 &&
            s_weight <= 59) {
            classification_code.value = "CLS02";
        }
        if (meat_percent >= 0 && meat_percent <= 7 && carcass_type == "G0110" && s_weight >= 56 &&
            s_weight <= 75) {
            classification_code.value = "CLS02";
        }
        if (meat_percent >= 11 && meat_percent <= 100 && carcass_type == "G0110" && s_weight >= 56 &&
            s_weight <= 75) {
            classification_code.value = "CLS02";
        }
        if (meat_percent >= 8 && meat_percent <= 10 && carcass_type == "G0110" && s_weight >= 60 &&
            s_weight <= 75) {
            classification_code.value = "CLS01";
        }
        if (meat_percent >= 0 && meat_percent <= 100 && carcass_type == "G0110" && s_weight >= 76 &&
            s_weight <= 85) {
            classification_code.value = "CLS03";
        }
        if (meat_percent >= 0 && meat_percent <= 100 && carcass_type == "G0110" && s_weight >= 50 &&
            s_weight <= 55) {
            classification_code.value = "CLS04";
        }
        if (meat_percent >= 0 && meat_percent <= 100 && carcass_type == "G0110" && s_weight >= 86 &&
            s_weight <= 100) {
            classification_code.value = "CLS06";
        }
        if (meat_percent >= 0 && meat_percent <= 100 && carcass_type == "G0110" && s_weight >= 101 &&
            s_weight <= 120) {
            classification_code.value = "CLS07";
        }

        if (meat_percent >= 0 && meat_percent <= 100 && carcass_type == "G0110" && s_weight > 120) {
            classification_code.value = "CLS08";
        }
        if (carcass_type == "G0111") {
            classification_code.value = "SOW-3P";
        }
        if (carcass_type == "G0113" && s_weight >= 5 && s_weight < 8) {
            classification_code.value = "3P-SK4";
        }
        if (carcass_type == "G0113" && s_weight >= 9 && s_weight < 20) {
            classification_code.value = "3P-SK5";
        }


    }

    function getNet() {
        var reading = document.getElementById('reading').value;
        var tareweight = document.getElementById('tareweight').value;
        var net = document.getElementById('net');
        var missing_slap_net = document.getElementById('ms_net');
        var new_net_value = parseFloat(reading) - parseFloat(tareweight);

        net.value = Math.round((new_net_value + Number.EPSILON) * 100) / 100;
        missing_slap_net.value = Math.round((new_net_value + Number.EPSILON) * 100) / 100;

        getSettlementWeight(net.value);
    }

    function getSettlementWeight(net) {
        var settlement_weight = document.getElementById('settlement_weight');
        var missing_slap_settlement = document.getElementById('ms_settlement_weight');

        var settlement_int = Math.floor(0.975*net);
        var settlement_float = (0.975*net) % 1;

        if (settlement_float > 0.54) {
            settlement_weight.value = settlement_int + 1;
            missing_slap_settlement.value = settlement_int + 1;

        } else {
            settlement_weight.value = settlement_int;
            missing_slap_settlement.value = settlement_int;

        }
        // alert(settlement_float);

    }

    function getScaleReading() {
        alert('get scale');
    }

</script>
@endsection
