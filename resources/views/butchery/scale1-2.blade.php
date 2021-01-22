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

@php
    $arr_products = $products->toArray();
@endphp

@section('content')
<div class="row">
    <!-- Slaughter date show -->
    <div class="col-md-2">
        <div class="form-group row">
            <!-- Date -->
            <div class="form-group">
                <label>Slaughter Date(mm/dd/yyyy):</label>
                <div class="input-group date" id="reservationdate" data-target-input="nearest">
                    <input type="text" id="datepk" class="form-control datetimepicker-input" value=""
                        oninput="getCounts()" data-target="#reservationdate" />
                    <div class="input-group-append" data-target="#reservationdate" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
            </div>
            <!-- /.form group -->
        </div>
    </div>
    <!-- End Slaughter date show -->

    <!-- slaughter data show -->
    <div class="col-md-2 ">
        <label>Baconers:</label>
        <input type="number" id="baconers_number" value="" readonly>
    </div>
    <div class="col-md-2 ">
        <label>Baconers sides:</label>
        <input type="number" id="baconers_sides" value="" readonly>
    </div>
    <div class="col-md-2 ">
        <label>Sows:</label>
        <input type="number" id="sows_number" value="" readonly>
    </div>
    <div class="col-md-2 ">
        <label>sows sides:</label>
        <input type="number" id="sows_sides" value="" readonly>
    </div>
    <!-- /.form group -->
</div>

<div class="row">
    <!-- scale 1 -->
    <div class="col-md-6">
        <form id="form-butchery-scale1" action="{{ route('butchery_scale1_save') }}" method="post">
            @csrf
            <div class="card-group">
                <div class="card">
                    <div class="card-body " style="padding-top: ">
                        <div class="form-group">
                            <label>No. of Carcasses</label>
                            <select class="form-control" id="no_of_carcass" name="no_of_carcass" oninput="getNet()">
                                <option value="1"> 1</option>
                                <option value="2"> 2</option>
                                <option value="3"> 3</option>
                                <option value="4"> 4</option>
                                <option value="5"> 5</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" value="G0110" id="baconers"
                                            name="carcass_type" checked>
                                        <label for="form-check-label">Baconers</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" value="G0111" id="sows"
                                            name="carcass_type">
                                        <label for="form-check-label">Sows </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" value="G0110A" id="headless_sale"
                                    name="carcass_type" >
                                <label for="form-check-label">Porker, Headles-sales</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" value="G0110B" id="headOn_sale"
                                    name="carcass_type" >
                                <label for="form-check-label">Porker, HeadOn-sales</label>
                            </div>
                        </div>
                        <div class="form-group" style="padding-left: 20%">
                            <button type="button" onclick="getWeightAjaxApi()" id="weigh" value="COM4"
                                class="btn btn-primary btn-lg"><i class="fas fa-balance-scale"></i> Weigh 1</button> <br>
                            <small>Reading from <input type="text" id="comport_value"
                                    value="{{ $configs[0]->comport }}" style="border:none" disabled></small>
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
                            <input type="number" class="form-control" id="tareweight" step="0.01" name="tareweight"
                                value="{{ number_format($configs[0]->tareweight, 2) }}" readonly>
                            <input type="hidden" class="form-control " id="default_tareweight"
                                value="{{ number_format($configs[0]->tareweight, 2) }}" >
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

                        <div class="form-group" style="padding-top: 40%">
                            <button type="submit" onclick="return checkNetOnSubmit()" class="btn btn-primary btn-lg"><i class="fa fa-paper-plane"
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
        <form id="form-butchery-scale2" action="{{ route('butchery_scale2_save') }}" method="post">
            @csrf
            <div class="card-group">
                <div class="card">
                    <div class="card-body">
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
                            <label for="exampleInputPassword1">Carcass Part</label>
                            <div class="form-check">
                                <label class="form-check-label" for="radio1">
                                    <input type="radio" class="form-check-input" id="radio1" name="item_code"
                                        value="{{ $arr_products[0]->code }}" checked>{{ $arr_products[0]->description }}
                                </label>
                            </div>
                            <div class="form-check">
                                <label class="form-check-label" for="radio2">
                                    <input type="radio" class="form-check-input" id="radio2" name="item_code"
                                        value="{{ $arr_products[1]->code }}">{{ $arr_products[1]->description }}
                                </label>
                            </div>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" id="radio3" name="item_code"
                                        value="{{ $arr_products[2]->code }}">{{ $arr_products[2]->description }}
                                </label>
                            </div>
                        </div>
                        <div class="form-group" style="padding-left: 20%">
                            <button type="button" onclick="getWeight2AjaxApi()" id="weigh2" value="COM4"
                                class="btn btn-primary btn-lg"><i class="fas fa-balance-scale"></i> Weigh 2</button> <br>
                            <small>Reading from <input type="text" id="comport_value2"
                                    value="{{ $configs[1]->comport }}" style="border:none" disabled></small>
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
                            <input type="number" class="form-control" id="tareweight2" name="tareweight2"
                                value="{{ number_format($configs[1]->tareweight, 2) }}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Net</label>
                            <input type="number" class="form-control" id="net2" name="net2" value="0.00" step="0.01"
                                placeholder="" readonly>
                        </div>
                    </div>
                </div>
                <div class="card ">
                    <div class="card-body text-center">
                        <div class="form-group" style="padding-top: 50%">
                            <button type="submit" onclick="return checkNet2OnSubmit()" class="btn btn-primary btn-lg"><i class="fa fa-paper-plane"
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
<hr>

<div class="div">
    <button class="btn btn-primary " data-toggle="collapse" data-target="#butchery_output_show"><i
            class="fa fa-plus"></i>
        Output
    </button> <hr>
</div>

<div id="butchery_output_show" class="collapse">
    {{-- <div class="row">
        <!-- baconers, sows, sides -->
        <div class="form-group col-md-2">
            <label>Legs:</label>
            <input type="number" id="baconers_number" value="0" oninput="getSidesNumber()" readonly>
        </div>
        <div class="form-group col-md-2">
            <label>Middles:</label>
            <input type="number" id="baconers_sides" value="0" readonly>
        </div>
        <div class="form-group col-md-2">
            <label>Shoulders:</label>
            <input type="number" id="baconers_sides" value="0" readonly>
        </div>
        <!-- /.form group -->
    </div> --}}
    <!-- legs, middles, shoulders -->

    <div class="row">
        <!-- scale1 ouput data table -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"> Scale 1 output data | <span id="subtext-h1-title"><small> entries ordered by
                                latest</small> </span></h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="hidden" hidden>{{ $i = 1 }}</div>
                    <table id="example1" class="table table-striped table-bordered table-hover table-responsive">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code </th>
                                <th>Carcass </th>
                                <th>No. of Carcass</th>
                                <th>Weight(kgs)</th>
                                <th>Date </th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Code </th>
                                <th>Carcass </th>
                                <th>No. of Carcass</th>
                                <th>Weight(kgs)</th>
                                <th>Date </th>
                            </tr>
                        </tfoot>
                        <tbody>
                        @foreach($beheading_data as $data)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td> {{ $data->item_code }}</td>
                                <td> {{ $data->description }}</td>
                                <td> {{ $data->no_of_carcass }}</td>
                                <td> {{ number_format($data->net_weight, 2) }}</td>
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

        <!-- scale2 ouput data table -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"> Scale 2 output data | <span id="subtext-h1-title"><small> entries ordered by
                                latest</small> </span></h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <table id="example2" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Code</th>
                                <th>Product</th>
                                <th>Weight (kgs)</th>
                                <th>Date </th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Product Code</th>
                                <th>Product</th>
                                <th>Weight (kgs)</th>
                                <th>Date </th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($butchery_data as $data)
                            <tr>
                                <td>{{ $loop->iteration}}</td>
                                <td id="itemCodeModalShow" data-id="{{$data->id}}" data-code="{{$data->item_code}}" data-item="{{$data->description}}"><a href="#">{{ $data->item_code }}</a> </td>
                                <td> {{ $data->description }}</td>
                                <td> {{ $data->net_weight }}</td>
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
<!-- butchery ouput data show -->

<!-- Start Edit Scale Modal -->
<div id="itemCodeModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <form id="form-edit-scale" action="{{ route('butchery_scale2_update') }}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Product Code: <strong><input style="border:none"
                                type="text" id="item_name" name="item_name" value="" readonly></strong></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="baud">Product</label>
                        <select class="form-control" name="editproduct" id="editproduct" required>
                            @foreach($products as $data)
                                <option value="{{$data->code}}" selected="selected">{{$data->description}}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="item_id" id="item_id" value="">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-warning">
                        <i class="fa fa-save"></i> Update
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- End Edit Scale modal-->
@endsection

@section('scripts')
<script>
    $(document).ready(function () {

        var date = new Date();
        date.setDate(date.getDate() - 1); //date yesterday

        // $("#datepk").val(formatDate(date));

        $('#no_of_carcass').change(function () {
            var number_of_carcass = $(this).val();
            var default_tareweight = $('#default_tareweight').val();

            var new_tareweight = (number_of_carcass) * (default_tareweight);
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

        $('#manual_weight2').change(function () {
            var manual_weight2 = document.getElementById('manual_weight2');
            var reading2 = document.getElementById('reading2');
            if (manual_weight2.checked == true) {
                reading2.readOnly = false;
                reading2.focus();
                $('#reading2').val("");

            } else {
                reading2.readOnly = true;

            }

        });

        // edit
        $("body").on("click", "#itemCodeModalShow", function (a) {
            a.preventDefault();

            var product = $(this).data('code');
            var item = $(this).data('item');
            var id = $(this).data('id');

            $('#editproduct').val(product);
            $('#item_name').val(item);
            $('#item_id').val(id);

            $('#itemCodeModal').modal('show');
        });

    });

    //get slaughter data count
    function getCounts() {
        var date = $('#datepk').val();
        if (date != null) {
            $.ajax({
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ url('slaughter-data-ajax') }}",
                data: {
                    'date': date,

                },
                dataType: 'JSON',
                success: function (res) {
                    if (res) {

                        var str = JSON.stringify(res);
                        var obj = JSON.parse(str);

                        $("#baconers_number").val(obj.baconers);
                        $("#sows_number").val(obj.sows);

                        var baconers_sides = document.getElementById('baconers_sides');
                        var sows_sides = document.getElementById('sows_sides');
                        if (obj.baconers > 0) {

                            baconers_sides.value = obj.baconers * 2;

                        } else {
                            baconers_sides.value = 0;
                        }

                        if (obj.sows > 0) {
                            sows_sides.value = obj.sows * 2;

                        } else {
                            sows_sides.value = 0;
                        }

                    }
                }
            });

        }

    }

    // getNetWeight1
    function getNet() {
        var reading = $('#reading').val();
        var tareweight = $('#tareweight').val();
        var net = document.getElementById('net');
        new_net_value = parseFloat(reading) - parseFloat(tareweight);
        net.value = Math.round((new_net_value + Number.EPSILON) * 100) / 100;
    }

    // getNetWeight2
    function getNet2() {
        var reading2 = document.getElementById('reading2').value;
        var tareweight2 = document.getElementById('tareweight2').value;
        var net2 = document.getElementById('net2');
        new_net_value = parseFloat(reading2) - parseFloat(tareweight2);
        net2.value = Math.round((new_net_value + Number.EPSILON) * 100) / 100;
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

    function checkNetOnSubmit(){
        var net = $('#net').val();
        $valid = true;
        if (net == "" || net <= 0.00 ) {
            $valid = false;
            alert("Please ensure you have valid netweight.");
        };
        return $valid;
    }

    function checkNet2OnSubmit(){
        var net2 = $('#net2').val();
        $valid = true;
        if (net2 == "" || net2 <= 0.00 ) {
            $valid = false;
            alert("Please ensure you have valid netweight."+ net2);

        };
        return $valid;
    }

</script>
@endsection
