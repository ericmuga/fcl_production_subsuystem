@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"> Scale-1 |<small> Beheading pig & sow</small></h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
            <h1 class="m-0"> Scale-2 |<small> Pork Breaking</small></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<div class="row">
    <!-- Slaughter date show -->
    <div class="col-md-3">
        <form class="">
            <div class="form-group row">
                <!-- Date -->
                <div class="form-group">
                    <label>Slaughter Date(mm/dd/yyyy):</label>
                    <div class="input-group date" id="reservationdate" data-target-input="nearest">
                        <input type="text" id="datepk" class="form-control datetimepicker-input" data-target="#reservationdate" />
                        <div class="input-group-append" data-target="#reservationdate" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
                <!-- /.form group -->
            </div>
        </form>
    </div>
    <!-- End Slaughter date show -->

    <!-- slaughter data show -->
    <div class="col-md-4">
        <form>
            <div class="row">
                <!-- baconers, sows, sides -->
                <div class=" col-md-6">
                    <div class="form-group">
                        <label>Baconers:</label>
                        <input type="number" id="baconers_number" value="0" oninput="getSidesNumber()" >
                    </div>
                    <div class="div">
                        <label>Sows:</label>
                        <input type="number" id="sows_number" value="0" oninput="getSidesNumber()" >
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>sides:</label>
                        <input type="number" id="baconers_sides" value="0" disabled>
                    </div>
                    <div class="div">
                        <label>sides:</label>
                        <input type="number" id="sows_sides" value="0" disabled>
                    </div>
                </div>
                <!-- /.form group -->
            </div>
        </form>
    </div>
    <!-- baconers, sows, side -->
</div> <hr>
<div class="row">
    <!-- scale 1 -->
    <div class="col-md-6">
        <form>
            <div class="card-group">
                <div class="card">
                    <div class="card-body " style="padding-top: ">
                        <div class="form-group">
                            <label>No. of Carcasses</label>
                            <select class="form-control" id="no_of_carcass" name="no_of_carcass" oninput="getNet()">
                                <option value="1.0"> 1</option>
                                <option value="2.0"> 2</option>
                                <option value="3.0"> 3</option>
                                <option value="4.0"> 4</option>
                                <option value="5.0"> 5</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" value="G1030" id="baconers"
                                    name="carcass_type" checked>
                                <label for="form-check-label">Baconers</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" value="G1031" id="sows"
                                    name="carcass_type">
                                <label for="form-check-label">Sows </label>
                            </div>
                        </div>
                        <div class="form-group" style="padding-left: 20%">
                            <button type="button" onclick="getWeightAjaxApi()" id="weigh" value="COM4"
                                class="btn btn-primary btn-lg">Weigh</button> <br>
                            <small>Reading from <input type="text" id="comport_value" value="COM4" style="border:none"
                                    disabled></small>
                        </div>
                    </div>
                </div>
                <div class="card ">
                    <div class="card-body text-center">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Reading</label>
                            <input type="number" step="0.01" class="form-control" id="reading" name="reading"
                                value="0.00" placeholder="" readonly oninput="getNet()">
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="manual_weight">
                            <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Tare-Weight</label>
                            <input type="number" class="form-control" id="tareweight" step="0.00" name="tareweight"
                                value="2.4" readonly>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Net</label>
                            <input type="number" class="form-control" id="net" value="0.00" step=".01" placeholder=""
                                readonly>
                        </div>
                    </div>
                </div>
                <div class="card ">
                    <div class="card-body text-center">

                        <div class="form-group" style="padding-top: 40%">
                            <button type="submit" class="btn btn-primary btn-lg"><i class="fa fa-paper-plane"
                                    aria-hidden="true"></i> Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- End scale 1 -->

    <!-- scale 2 -->
    <div class="col-md-6">
        <form>
            <div class="card-group">
                <div class="card">
                    <div class="card-body " style="">
                        <div class="form-group">
                            <label for="exampleInputPassword1">Carcass Type</label>
                            <select class="form-control select2" name="carcass_type" id="carcass_type" required>
                                <option value="" selected>Baconer</option>
                                <option value="">Sow</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <label class="form-check-label" for="radio1">
                                    <input type="radio" class="form-check-input" id="radio1" name="carcass_part"
                                        value="option1" checked>Legs
                                </label>
                            </div>
                            <div class="form-check">
                                <label class="form-check-label" for="radio2">
                                    <input type="radio" class="form-check-input" id="radio2" name="carcass_part"
                                        value="option2">Middles
                                </label>
                            </div>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" id="radio3" name="carcass_part"
                                        value="option2">Shoulders
                                </label>
                            </div>
                        </div>
                        <div class="form-group" style="padding-left: 30%">
                            <button type="button" onclick="getWeight2AjaxApi()" id="weigh2" value="COM4"
                                class="btn btn-primary btn-lg">Weigh</button> <br>
                            <small>Reading from <input type="text" id="comport_value" value="COM6" style="border:none"
                                    disabled></small>
                        </div>
                    </div>
                </div>
                <div class="card ">
                    <div class="card-body text-center">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Reading</label>
                            <input type="number" step="0.01" class="form-control" id="reading2" name="reading2"
                                value="0.00" oninput="getNet2()" placeholder="" readonly>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="manual_weight2">
                            <label class="form-check-label" for="manual_weight2">Enter Manual weight</label>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Tare-Weight</label>
                            <input type="number" class="form-control" id="tareweight2" name="tareweight2" value="2.4"
                                readonly>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Net</label>
                            <input type="number" class="form-control" id="net2" name="net2" value="0.00" step=".01"
                                placeholder="" readonly>
                        </div>
                    </div>
                </div>
                <div class="card ">
                    <div class="card-body text-center">
                        <div class="form-group" style="padding-top: 50%">
                            <button type="submit" class="btn btn-primary btn-lg"><i class="fa fa-paper-plane"
                                    aria-hidden="true"></i> Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- End scale 2 -->
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function () {

        var date = new Date();
        $("#datepk").val(formatDate(date));

        $('#no_of_carcass').change(function () {
            var number_of_carcass = $(this).val();
            var tareweight = $('#tareweight').val();

            var new_tareweight = (number_of_carcass) * (tareweight);
            $("#tareweight").val(new_tareweight);

        });

        $('#manual_weight').change(function () {
            var manual_weight = document.getElementById('manual_weight');
            var reading = document.getElementById('reading');
            if (manual_weight.checked == true) {
                reading.readOnly = false;

            } else {
                reading.readOnly = true;

            }

        });

        $('#manual_weight2').change(function () {
            var manual_weight2 = document.getElementById('manual_weight2');
            var reading2 = document.getElementById('reading2');
            if (manual_weight2.checked == true) {
                reading2.readOnly = false;

            } else {
                reading2.readOnly = true;

            }

        });

    });

    //getSidesNumber
    function getSidesNumber(){
        var baconers_number = $('#baconers_number').val();
        if (baconers_number > 0) {
            $('#baconers_sides').val(baconers_number * 2);

        } else {
            $('#baconers_sides').val(baconers_number);
        }

        var sows_number = $('#sows_number').val();
        if (sows_number > 0) {
            $('#sows_sides').val(sows_number * 2);

        } else {
            $('#sows_sides').val(sows_number);
        }
    }

    // getNetWeight1
    function getNet() {
        var reading = $('#reading').val();
        var tareweight = $('#tareweight').val();
        var net = document.getElementById('net');
        net.value = parseFloat(reading) - parseFloat(tareweight);
    }

    // getNetWeight2
    function getNet2() {
        var reading2 = document.getElementById('reading2').value;
        var tareweight2 = document.getElementById('tareweight2').value;
        var net2 = document.getElementById('net2');
        net2.value = parseFloat(reading2) - parseFloat(tareweight2);
    }

    function formatDate(date) {
        month = '' + (date.getMonth() + 1),
        day = '' + date.getDate(),
        year = date.getFullYear();

        if (month.length < 2)
            month = '0' + month;
        if (day.length < 2)
            day = '0' + day;

        return [month, day, year].join('/');
    }

    function getWeightAjaxApi() {
        var ComPortID = document.getElementById('weigh').value;
        if (ComPortID) {
            alert('comport ' + ComPortID + 'is available');
            $.ajax({
                type: "GET",
                url: "{{ url('api/get-centres') }}?route_id=" + routeID,
                success: function (res) {
                    if (res) {
                        $("#centre").empty();
                        // $("#centre").append('<option>Select</option>');
                        $.each(res, function (key, value) {
                            $("#centre").append($("<option></option>").attr("value", value.id)
                                .text(value.centre_name));
                        });

                    } else {
                        $("#reading").empty();
                    }
                }
            });

        } else {
            // $("#reading").value = 0.00;
            alert('comport ' + ComPortID + 'is not available');
        }
    }

    //Date range picker
    $('#reservationdate').datetimepicker({
        format: 'L',
    });

</script>
@endsection
