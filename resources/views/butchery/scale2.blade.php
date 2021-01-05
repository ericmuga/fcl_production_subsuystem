@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"> {{ $title }} |<small> Breaking pig & sow</small></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<form>
    <div class="card-group">
        <div class="card">
            <div class="card-body " style="">
                <div class="form-group">
                    <label for="exampleInputPassword1">Carcass Type</label>
                    <select class="form-control select2" name="receipt_no" id="receipt_no" required>
                        <option value="" selected disabled>select</option>
                        <option>Baconer</option>
                        <option>Sow</option>
                    </select>
                </div> <br>
                <div class="form-group">
                    <div class="form-check">
                        <label class="form-check-label" for="radio1">
                            <input type="radio" class="form-check-input" id="radio1" name="optradio"
                                value="option1">Legs
                        </label>
                    </div> <br>
                    <div class="form-check">
                        <label class="form-check-label" for="radio2">
                            <input type="radio" class="form-check-input" id="radio2" name="optradio"
                                value="option2">Middles
                        </label>
                    </div> <br>
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="radio" class="form-check-input" id="radio3" name="optradio"
                                value="option2">Shoulders
                        </label>
                    </div>
                </div> <br>
                <div class="form-group" style="padding-left: 30%">
                    <button type="button" onclick="getWeightAjaxApi()" id="weigh" value="COM4"
                        class="btn btn-primary btn-lg">Weigh</button> <br><br>
                    <small>Reading from <input type="text" id="comport_value" value="COM4" style="border:none"
                            disabled></small>
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
                    <input type="checkbox" class="form-check-input" id="manual_weight">
                    <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                </div> <br>
                <div class="form-group">
                    <label for="exampleInputPassword1">Tare-Weight</label>
                    <input type="number" class="form-control" id="tareweight" name="tareweight" value="2.4" readonly>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Net</label>
                    <input type="number" class="form-control" id="net" value="0.00" step=".01" placeholder="" readonly>
                </div>
            </div>
        </div>

        <div class="card ">
            <div class="card-body text-center">

                <div class="form-group" style="padding-top: 50%">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fa fa-paper-plane"
                            aria-hidden="true"></i> Save</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {

        $('#manual_weight').change(function () {
            var manual_weight = document.getElementById('manual_weight');
            var reading = document.getElementById('reading');
            if (manual_weight.checked == true) {
                reading.readOnly = false;

            } else {
                reading.readOnly = true;

            }

        });
    });

    function getNet() {
        var reading = document.getElementById('reading').value;
        var tareweight = document.getElementById('tareweight').value;
        var net = document.getElementById('net');
        net.value = parseFloat(reading) - parseFloat(tareweight);
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

</script>
@endsection
