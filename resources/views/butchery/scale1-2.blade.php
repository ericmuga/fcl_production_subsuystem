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
                <label>Slaughter Date(yyyy-mm-dd):</label>
                <div class="input-group">
                    <input type="text" id="datepk" name="datepk" class="form-control"
                        value="{{ $helpers->getButcheryDate() }}" readonly>
                </div>
            </div>
            <!-- /.form group -->
        </div>
    </div>
    <!-- End Slaughter date show -->
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
                            <input type="number" onClick="this.select();" oninput="adjustTareweight()"
                                class="form-control" id="no_of_carcass" min="1" value="1" name="no_of_carcass" placeholder="">
                        </div>
                        <div class="form-group">
                            <label>Carcass Type</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <label for="baconers">
                                            <input class="form-check-input" type="radio" value="G1030" id="baconers"
                                                name="carcass_type" checked> Baconers
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <label for="sows">
                                            <input class="form-check-input" type="radio" value="G1031" id="sows"
                                                name="carcass_type"> Sows
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-check">
                                <label for="headless_sale">
                                    <input class="form-check-input" type="radio" value="G1033" id="headless_sale"
                                        name="carcass_type"> Porker, Headles-sales
                                </label>
                            </div>
                            <div class="form-check">
                                <label for="headOn_sale">
                                    <input class="form-check-input" type="radio" value="G1032" id="headOn_sale"
                                        name="carcass_type"> Porker, HeadOn-sales
                                </label>
                            </div>
                            <div class="form-check">
                                <label for="carcassSide_sale">
                                    <input class="form-check-input" type="radio" value="G1034" id="carcassSide_sale"
                                        name="carcass_type"> Pig, Carcass-Side without Tail-Sales
                                </label>
                            </div>
                            
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
                                value="{{ number_format($configs[0]->tareweight, 2) }}">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Net</label>
                            <input type="number" class="form-control" id="net" name="net" value="0.00" step=".01"
                                placeholder="" readonly>
                        </div>
                    </div>
                </div>
                <div class="card "> <br>
                    <div class="card-body text-center">
                        <div class="form-group" style="padding-left: %">
                            <button type="button" onclick="getScale1Reading()" class="btn btn-primary btn-lg"><i
                                    class="fas fa-balance-scale"></i> Weigh 1</button> <br>
                            <small>Reading from COM: <input type="text" id="comport_value"
                                    value="{{ $configs[0]->comport }}" style="border:none; text-align: center"
                                    disabled></small>
                        </div><br>
                        <div class="form-group" style="padding-top: 20%">
                            <button type="submit" onclick="return checkNetOnSubmit()" class="btn btn-primary btn-lg"><i
                                    class="fa fa-paper-plane" aria-hidden="true"></i> Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- End scale 1 -->

    <!-- scale 2 -->
    <div class="col-md-6">
        <form id="scale2" action="{{ route('butchery_scale2_save') }}" method="post">
            @csrf
            <div class="card-group">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="exampleInputPassword1">Carcass Type</label>
                            <select class="form-control select2" name="carcass_type" id="carcass_type" required>
                                @if (old('carcass_type') == "G1031")
                                <option value="G1030"> Pig, Carcass</option>
                                <option value="G1031" selected> Sow, Carcass</option>
                                @else
                                <option value="G1030" selected="selected"> Pig, Carcass</option>
                                <option value="G1031"> Sow, Carcass</option>
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            @if ($arr_products)
                            <label for="exampleInputPassword1">Product Part</label>
                            <div class="form-check">
                                <label class="form-check-label" for="radio1">
                                    <input type="radio" class="form-check-input messageCheckbox" id="radio1"
                                        name="item_code" value="{{ $arr_products[0]->code }}"
                                        checked>{{ $arr_products[0]->description }}
                                </label>
                            </div>
                            <div class="form-check" style="margin-left: 15%">
                                <label class="form-check-label" for="radio2">
                                    <input type="radio" class="form-check-input messageCheckbox" id="radio2"
                                        name="item_code"
                                        value="{{ $arr_products[1]->code }}">{{ $arr_products[1]->description }}
                                </label>
                            </div>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input messageCheckbox" id="radio3"
                                        name="item_code"
                                        value="{{ $arr_products[2]->code }}">{{ $arr_products[2]->description }}
                                </label>
                            </div>
                            @else
                            <p><code>You must have products part set in db</code></p>
                            @endif
                        </div>
                        <div class="form-group text-center" style="padding-left: 10%">
                            <button type="button" onclick="getScale2Reading()" class="btn btn-primary btn-lg"><i
                                    class="fas fa-balance-scale"></i> Weigh 2</button> <br>
                            <small>Reading from COM: <input type="text" id="comport_value2"
                                    value="{{ $configs[1]->comport }}" style="border:none; text-align: center"
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
                        <div class="form-group">
                            <label for="exampleInputPassword1">Product Type</label>
                            <select class="form-control select2" name="product_type" id="product_type" required>
                                <option value="1" selected="selected">Main Product</option>
                                <option value="2">By Product</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">No. of pieces </label>
                            <input type="number" class="form-control" id="no_of_items" value="" name="no_of_items"
                                placeholder="">
                        </div>
                    </div>
                    <div class="card-body text-center" style="padding-bottom: 25%">
                        <div class="form-group">
                            <button type="submit" onclick="return checkNet2OnSubmit()" class="btn btn-primary btn-lg"><i
                                    class="fa fa-paper-plane" aria-hidden="true"></i> Save
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
    </button>
</div>
<hr>

<div id="butchery_output_show" class="collapse">
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
                    <div class="table-responsive">
                        <table id="example1" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Code </th>
                                    <th>Carcass </th>
                                    <th>No. of Carcass</th>
                                    <th>Scale Weight(kgs)</th>
                                    <th>Date </th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Code </th>
                                    <th>Carcass </th>
                                    <th>No. of Carcass</th>
                                    <th>Scale Weight(kgs)</th>
                                    <th>Date </th>
                                </tr>
                            </tfoot>
                            <tbody>
                                @foreach($beheading_data as $data)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td id="edit1ModalShow" data-id="{{$data->id}}"
                                        data-product_code="{{$data->item_code}}" data-item="{{$data->description}}"
                                        data-no_carcass="{{ $data->no_of_carcass }}"
                                        data-weight="{{number_format($data->actual_weight, 2)}}"><a
                                            href="#">{{ $data->item_code }}</a> </td>
                                    <td> {{ $data->description }}</td>
                                    <td> {{ $data->no_of_carcass }}</td>
                                    <td> {{ number_format($data->actual_weight, 2) }}</td>
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

        <!-- scale2 ouput data table -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"> Scale 2 output data | <span id="subtext-h1-title"><small> entries ordered by
                                latest</small> </span></h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example2" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product Code</th>
                                    <th>Product</th>
                                    <th>Scale Weight(kgs)</th>
                                    <th>No. of Pieces</th>
                                    <th>Date </th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Product Code</th>
                                    <th>Product</th>
                                    <th>Scale Weight(kgs)</th>
                                    <th>No. of Pieces</th>
                                    <th>Date </th>
                                </tr>
                            </tfoot>
                            <tbody>
                                @foreach($butchery_data as $data)
                                <tr>
                                    <td>{{ $loop->iteration}}</td>
                                    <td id="itemCodeModalShow" data-id="{{$data->id}}"
                                        data-item_code="{{trim($data->item_code)}}" data-item="{{$data->description}}" data-pieces="{{ $data->no_of_items }}"
                                        data-weight="{{number_format($data->actual_weight, 2)}}"><a
                                            href="#">{{ $data->item_code }}</a> </td>
                                    <td> {{ $data->description }}</td>
                                    <td> {{ number_format($data->actual_weight, 2) }}</td>
                                    <td> {{ $data->no_of_items }}</td>
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
<!-- butchery ouput data show -->

<!-- Edit scale1 Modal -->
<div id="edit1Modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!--Start create user modal-->
        <form id="form-edit-role" action="{{route('butchery_scale1_update')}}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Scale1 Item: <strong><input style="border:none"
                                type="text" id="item_name1" name="item_name1" value="" readonly></strong></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="email" class="col-form-label"> Carcass </label>
                        <select class="form-control" name="edit_carcass" id="edit_carcass">
                            <option value="G1030">Baconers</option>
                            <option value="G1031">Sows</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>No. of Carcasses</label>
                        <input type="number" onClick="this.select();" class="form-control" id="edit_no_carcass" value=""
                            name="edit_no_carcass" placeholder="">
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-form-label">Scale Weight(actual_weight)</label>
                        <input type="number" onClick="this.select();" class="form-control" name="edit_weight1"
                            id="edit_weight1" placeholder="" step="0.01" autocomplete="off" required autofocus>
                    </div>
                    <input type="hidden" name="item_id1" id="item_id1" value="">
                </div>
                <div class="modal-footer">
                    <div class="form-group">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button class="btn btn-warning">
                            <i class="fa fa-save"></i> Update
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!--End Edit scale1 modal-->

<!-- Start Edit Scale2 Modal -->
<div id="itemCodeModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <form id="form-edit-scale" action="{{ route('butchery_scale2_update') }}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Scale2 Item: <strong><input style="border:none"
                                type="text" id="item_name" name="item_name" value="" readonly></strong></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="baud" class="col-form-label">Product</label>
                        <select class="form-control" name="edit_product" id="edit_product">
                            @foreach($products as $data)
                            <option value="{{trim($data->code)}}">{{$data->description}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>No. of Pieces</label>
                        <input type="number" onClick="this.select();" class="form-control" id="edit_no_pieces" value=""
                            name="edit_no_pieces" placeholder="">
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-form-label">Scale Weight(actual_weight)</label>
                        <input type="number" onClick="this.select();" class="form-control" name="edit_weight"
                            id="edit_weight" placeholder="" step="0.01" autocomplete="off" required autofocus>
                    </div>
                    <input type="hidden" name="item_id" id="item_id" value="">
                </div>
                <div class="modal-footer">
                    <div class="form-group">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button class="btn btn-warning">
                            <i class="fa fa-save"></i> Update
                        </button>
                    </div>
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

        $('.messageCheckbox').change(function () {
            var checkedValue = document.querySelector('.messageCheckbox:checked').value;
        });

        $('#no_of_carcass').change(function () {
            adjustTareweight();
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

        // edit scale 1
        $("body").on("click", "#edit1ModalShow", function (a) {
            a.preventDefault();

            var product = $(this).data('product_code');
            var item = $(this).data('item');
            var no_carcass = $(this).data('no_carcass');
            var weight = $(this).data('weight');
            var id = $(this).data('id');


            $('#edit_carcass').val(product);
            $('#item_name1').val(item);
            $('#edit_no_carcass').val(no_carcass);
            $('#edit_weight1').val(weight);
            $('#item_id1').val(id);

            $('#edit1Modal').modal('show');
        });

        // edit scale 2
        $("body").on("click", "#itemCodeModalShow", function (e) {
            e.preventDefault();

            var product = $(this).data('item_code');
            var item = $(this).data('item');
            var pieces = $(this).data('pieces');
            var weight = $(this).data('weight');
            var id = $(this).data('id');

            $('#edit_product').val(product);
            $('#item_name').val(item);
            $('#edit_no_pieces').val(pieces);
            $('#edit_weight').val(weight);
            $('#item_id').val(id);

            $('#itemCodeModal').modal('show');
        });

    });

    // adjust tareweight
    function adjustTareweight() {
        var number_of_carcass = $('#no_of_carcass').val();
        var default_tareweight = $('#default_tareweight').val();

        var new_tareweight = (number_of_carcass) * (default_tareweight);
        $("#tareweight").val(Math.round((new_tareweight + Number.EPSILON) * 100) / 100);

        getNet();
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

    function checkNetOnSubmit() {
        var net = $('#net').val();
        $valid = true;
        if (net == null || net <= 0.00) {
            $valid = false;
            alert("Please ensure you have valid netweight.");
        };
        return $valid;
    }

    function checkNet2OnSubmit() {
        var net2 = $('#net2').val();
        $valid = true;
        if (net2 == null || net2 <= 0.00) {
            $valid = false;
            alert("Please ensure you have valid netweight." + net2);

        };
        return $valid;
    }

    //read scale 1
    function getScale1Reading() {
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

    //read scale 2
    function getScale2Reading() {
        var comport = $('#comport_value2').val();

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
                        reading2.value = obj.response;
                        getNet2();

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
