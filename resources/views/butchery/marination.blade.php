@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"> {{ $title }} |<small> Marination </small></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<form id="form-save-marination" class="form-prevent-multiple-submits" action="{{ route('save_marination') }}"
    method="post">
    @csrf
    <div class="card-group">
        <div class="card">
            <div class="card-body " style="">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="exampleInputPassword1"> Product Code</label>
                            <select class="form-control select2" name="product" id="product" required>
                                <option value="">Select product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->code.'-'.$product->product_type_code }}">
                                    {{ $product->code.' '.$product->description.'-'.$product->product_type_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <label for="exampleInputEmail1">Reading</label>
                    <input type="number" step="0.01" class="form-control" id="reading" name="reading" value="0.00"
                        oninput="getNet()" placeholder="" onfocus="this.select();">
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="manual_weight" name="manual_weight">
                    <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                </div> <br>
                <input type="hidden" id="old_manual" value="{{ old('manual_weight') }}">
                <div class="row">
                    <div class="col-4 form-group">
                        <label for="crate_weight">Crate Weight</label>
                        <select class="form-control" id="crate_weight" name="crate_weight" onchange="updateTotalTare()">
                            <option selected value="1.8">1.8</option>
                            <option value="1.5">1.5</option>
                        </select>
                    </div>
                    <div class="col-4 form-group">
                        <label for="black_crates">Black Crates</label>
                        <input type="number" class="form-control" id="black_crates" name="black_crates" min="0" oninput="updateTotalTare()" value="1">
                    </div>
                    <div class="col-4 form-group">
                        <label for="tareweight">Crates Tare-Weight</label>
                        <input type="number" class="form-control" id="tareweight" name="tareweight" value="" readonly>
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
                    <label for="exampleInputPassword1">Total Crates</label>
                    <input type="number" class="form-control" onClick="this.select();" id="no_of_crates" min="0" value="4" oninput="updateTotalTare()"
                        name="no_of_crates" placeholder="" required>
                </div>
                <div class="form-group">
                    <span class="tinyLabel">Marination Date</span>
                    <input type="datetime-local" class="form-control" id="marination_date" name="marination_date"
                        required>
                </div>
                <div class="form-group" style="padding-top: 10%">
                    <button type="submit" onclick="return checkNetOnSubmit()"
                        class="btn btn-primary btn-lg btn-prevent-multiple-submits"><i
                            class="fa fa-paper-plane single-click" aria-hidden="true"></i> Save</button>
                </div>
            </div>
        </div>
    </div>
</form>
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
                    <h3 class="card-title"> Marination output data | <span id="subtext-h1-title"><small> showing last
                                <strong>7 days</strong> entries
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
                                    <th>Process</th>
                                    <th>Scale Weight(kgs)</th>
                                    <th>Net Weight(kgs)</th>
                                    <th>Date </th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Code </th>
                                    <th>product </th>
                                    <th>Product Type</th>
                                    <th>Process</th>
                                    <th>Scale Weight(kgs)</th>
                                    <th>Net Weight(kgs)</th>
                                    <th>Date </th>

                                </tr>
                            </tfoot>
                            <tbody>
                                @foreach($marination_data as $data)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td id="itemCodeModalShow" data-id="{{$data->id}}"
                                        data-weight="{{ number_format($data->actual_weight, 2) }}"
                                        data-code="{{ $data->item_code }}" data-item="{{ $data->description }}"><a
                                            href="#">{{ $data->item_code }}</a>
                                    </td>
                                    <td>{{ $data->description }}</td>
                                    <td> {{ $data->product_type }}</td>
                                    <td> {{ $data->process }}</td>
                                    <td> {{ number_format($data->actual_weight, 2) }}</td>
                                    <td> {{ number_format($data->net_weight, 2) }}</td>
                                    <td>{{ $helpers->amPmDate($data->created_at)}}</td>
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
        <form class="form-prevent-multiple-submits" id="form-edit-role" action="{{route('update_marination')}}"
            method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit marination Item: <strong><input
                                style="border:none" type="text" id="item_name" name="item_name" value=""
                                readonly></strong></h5>
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
                            <label for="email" class="col-form-label">Scale Weight(actual_weight)</label>
                            <input type="number" onClick="this.select();" class="form-control" name="edit_weight"
                                id="edit_weight" placeholder="" step="0.01" autocomplete="off" required autofocus>
                        </div>
                    </div>
                    <input type="hidden" name="item_id" id="item_id" value="">
                </div>
                <div class="modal-footer">
                    <div class="form-group">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button class="btn btn-warning btn-lg btn-prevent-multiple-submits" type="submit">
                            <i class="fa fa-save"></i> Update
                        </button>
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
    function updateTotalTare() {
        crate_weight = document.getElementById('crate_weight').value;
        no_of_crates = document.getElementById('no_of_crates').value;
        black_crates = document.getElementById('black_crates').value;
        tareweight_input = document.getElementById('tareweight');
        tareweight_input.value = (no_of_crates * crate_weight + (black_crates * (2-crate_weight))).toFixed(2);
        getNet();
    };

    $(document).ready(function () {
        updateTotalTare();

        $('#product').on("change", function (e) {
            e.preventDefault();
            var selectedCode = $(this).children("option:selected").val();
            var reading = document.getElementById('reading');

            if (selectedCode !== '') {
                $('#product').select2('destroy').select2();
                reading.focus();
            }
        });

        $('#manual_weight').prop('checked', true);

        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
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

            $('#itemCodeModal').modal('show');
        });


        $('#edit_product').change(function () {
            var code = $('#edit_product').val();

            var crates_no = $('#edit_crates').val();
            var net_weight = $('#edit_weight').val();

            var net = net_weight - (1.8 * crates_no);
        });

        $('#edit_weight').keyup(function () {
            var code = $('#edit_product').val();

            var crates_no = $('#edit_crates').val();
            var net_weight = $('#edit_weight').val();

            var net = net_weight - (1.8 * crates_no);
        });

        $('#edit_crates').change(function () {
            var code = $('#edit_product').val();

            var crates_no = $('#edit_crates').val();
            var net_weight = $('#edit_weight').val();

            var net = net_weight - (1.8 * crates_no);
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

</script>
@endsection
