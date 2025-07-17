@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-9">
            <h1 class="m-0"> {{ $title }} |<small> Beef/Lamb Products Receiving </small></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<form id="form-save-scale3" class="form-prevent-multiple-submits"
    action="{{ route('save_idt_receiving') }}" method="post">
    @csrf
    <div class="card-group">
        <div class="card">
            <div class="card-body text-center">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="exampleInputPassword1"> Product ShortCode</label>
                            <select class="form-control select2" name="product" id="product" required>
                                <option value="">Select product</option>
                                @foreach($products as $product)
                                    <option
                                        value="{{ $product->code }}">
                                        {{ $product->code. ' '.$product->description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group text-center">
                            <label for="exampleInputPassword1">Batch No</label>
                            <input type="text" class="form-control" id="batch_no" name="batch_no" value="" placeholder="">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group text-center">
                            <label for="exampleInputPassword1">Vehicle No</label>
                            <select class="form-control select2" name="description" id="description" required>
                                <option value="">Select Vehicle</option>
                                <option value="KAQ 714R">KAQ 714R</option>
                                <option value="KAS 004G">KAS 004G</option>
                                <option value="KCE 015W">KCE 015W</option>
                                <option value="KAX 004C">KAX 004C</option>
                            </select>
                            <input type="hidden" id="session_vehicle" name="session_vehicle" value="{{ old('description') }}">
                        </div>
                    </div>
                </div>
                <div class="form-group" style="padding-top: 5%;">
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
                    <div class="crates col-md-6">
                        <label for="exampleInputPassword1">Total Crates </label>
                        <input type="number" class="form-control" id="total_crates" value="1" name="total_crates"
                            placeholder="" required>
                    </div>
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Total Tare</label>
                        <input type="number" class="form-control" id="tareweight" name="tareweight" value="0.0" readonly>
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
                    <h3 class="card-title"> transfers data | <span id="subtext-h1-title"><small> today's entries
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
                                    <th>Vehicle No</th>
                                    <th>Code </th>
                                    <th>product </th>
                                    <th>Total Crates</th>
                                    <th>Scale Weight(kgs)</th>
                                    <th>Total Tare</th>
                                    <th>Net Weight(kgs)</th>
                                    <th>Total Pieces</th>
                                    <th>Prod Date</th>
                                    <th>Received By</th>
                                    <th>Created Date </th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Vehicle No</th>
                                    <th>Code </th>
                                    <th>product </th>
                                    <th>Total Crates</th>
                                    <th>Scale Weight(kgs)</th>
                                    <th>Total Tare</th>
                                    <th>Net Weight(kgs)</th>
                                    <th>Total Pieces</th>
                                    <th>Prod Date</th>
                                    <th>Received By</th>
                                    <th>Created Date </th>
                                </tr>
                            </tfoot>
                            <tbody>
                                @foreach($entries as $data)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $data->vehicle_no }}</td>
                                        <td>{{ $data->product_code }}</td>
                                        <td>{{ $data->description }}</td>
                                        <td>{{ $data->receiver_total_crates }}</td>
                                        <td>{{ $data->receiver_total_weight + ($data->receiver_total_crates * 1.8 ) }}</td>
                                        <td>{{ $data->receiver_total_crates * 1.8 }}</td> 
                                        <td>{{ $data->receiver_total_weight }}</td> 
                                        <td>{{ $data->receiver_total_pieces }}</td> 
                                        <td>{{ $data->production_date }}</td> 
                                        <td>{{ $data->received_by }}</td> 
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


@endsection

@section('scripts')
<script>
    $(document).ready(function () {

        getTareweight()
        getNet()
        setProductionDate() //set production date default

        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        getSessionVehicle()

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

        $('#product').change(function () {
            $(this).select2('destroy').select2();
            //$("#batch_no").focus();
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
        let tareweight = 0

        if (parseInt(total_crates) > 0 ) {
            tareweight = parseInt(total_crates) * 1.8
            let formatted = Math.round((tareweight + Number.EPSILON) * 100) / 100;
            $('#tareweight').val(formatted);
        }
    }

    function validateOnSubmit() {
        $valid = true;

        var net = $('#net').val();

        if (net == "" || net <= 0.00) {
            $valid = false;
            alert("Please ensure you have valid netweight.");
        }

        return $valid;
    }

    const getSessionVehicle = () => {
        let old_val = $('#session_vehicle').val()

        if (old_val) {
            // Select the option with the specified value
            $('#description').select2('destroy').select2();
            $("#description").val(old_val).trigger('change');
        }
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
