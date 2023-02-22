@extends('layouts.freshcuts-bulk_master')

@section('content')
<form id="form-save-freshcuts" class="form-prevent-multiple-submits" action="{{ route('freshcuts_create_idt') }}"
    method="post">
    @csrf
    <div class="card-group">
        <div class="card">
            <div class="card-body " style="">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="exampleInputPassword1"> Product</label>
                            <select class="form-control select2" name="product" id="product" required>
                                <option value="">Select product</option>
                                @foreach($items as $t)
                                <option value="{{ $t->code }}">{{ $t->code.'-'.$t->description.'-'.$t->barcode }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Transfer To</label>
                        <select class="form-control select2" name="transfer_to" id="transfer_to" required>
                            <option disabled selected value> -- select an option -- </option>
                            <option value="2055">Sausage</option>
                            <option value="2500">High Care</option>
                            <option value="3535">Despatch</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Transfer Type</label>
                        <select class="form-control select2" name="transfer_type" id="transfer_type" required>
                            <option disabled selected value> -- select an option -- </option>
                            <option value="1">Local</option>
                            <option value="2">Export</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <label for="exampleInputPassword1">Carriage Type</label>
                        <select class="form-control select2" name="carriage_type" id="carriage_type" required>
                            <option disabled selected value> -- select an option -- </option>
                            <option value="1.8">Crate</option>
                            <option value="40">Van</option>
                        </select>
                    </div>
                    <div hidden id="crates_div" class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputPassword1">No. of Crates </label>
                            <input type="number" class="form-control" onClick="this.select();" id="no_of_crates"
                                value="" name="no_of_crates" min="1" placeholder="" required>
                        </div>
                    </div>
                </div>
                <div class="form-group" style="padding-left: 30%; padding-top: 5%">
                    <button type="button" onclick="getScaleReading()" id="weigh" value=""
                        class="btn btn-primary btn-lg"><i class="fas fa-balance-scale"></i> Weigh</button> <br>
                    <small>Reading from : <input style="font-weight: bold; border: none" type="text" id="comport_value"
                            value="{{ $configs[0]->comport }}" style="border:none" disabled></small>
                </div>

            </div>
        </div>
        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <label for="exampleInputEmail1">Scale Reading</label>
                    <input type="number" step="0.01" class="form-control" id="reading" name="reading" value="0.00"
                        oninput="getNet()" placeholder="" readonly>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="manual_weight" name="manual_weight">
                    <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                </div> <br>
                <input type="hidden" id="old_manual" value="{{ old('manual_weight') }}">
                <div class="form-group">
                    <label for="exampleInputPassword1">Tare-Weight</label>
                    <input type="number" class="form-control" id="tareweight" name="tareweight" value="" readonly>
                </div>
                <div class="row form-group">
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Total Tare-Weight</label>
                        <input type="number" class="form-control" id="total_tare" name="total_tare" value="0.00"
                            step=".01" placeholder="" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Net Weight</label>
                        <input type="number" class="form-control" id="net" name="net" value="0.00" step=".01"
                            placeholder="" readonly>
                    </div>
                </div>
            </div>
        </div>
        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <label for="exampleInputPassword1">No. of pieces </label>
                    <input type="number" class="form-control" onClick="this.select();" id="no_of_pieces" value=""
                        name="no_of_pieces" placeholder="" required>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Batch No </label>
                    <input type="text" class="form-control" onClick="this.select();" id="batch_no" value=""
                        name="batch_no" placeholder="" required>
                </div>
                <div class="form-group" style="padding-top: 5%">
                    <button type="submit" onclick="return validateOnSubmit()"
                        class="btn btn-primary btn-lg btn-prevent-multiple-submits"><i
                            class="fa fa-paper-plane single-click" aria-hidden="true"></i> Save</button>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" class="input_checks" id="loading_value" value="0">

    <div id="loading" class="collapse">
        <div class="row d-flex justify-content-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>
</form><br>

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
                <div class="table-responsive">
                    <table id="example1" class="table display nowrap table-striped table-bordered table-hover"
                        width="100%">
                        <thead>
                            <tr>
                                <th>IDT No</th>
                                <th>Product Code</th>
                                <th>Product</th>
                                <th>Std Unit Measure</th>
                                <th>Location </th>
                                <th>Chiller</th>
                                <th>Total Crates</th>
                                <th>Full Crates</th>
                                <th>Incomplete Crate Pieces</th>
                                <th>Total Pieces</th>
                                <th>Total Weight</th>
                                <th>Export No</th>
                                <th>Batch No</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>IDT No</th>
                                <th>Product Code</th>
                                <th>Product</th>
                                <th>Std Unit Measure</th>
                                <th>Location </th>
                                <th>Chiller</th>
                                <th>Total Crates</th>
                                <th>Full Crates</th>
                                <th>Incomplete Crate Pieces</th>
                                <th>Total Pieces</th>
                                <th>Total Weight</th>
                                <th>Export No</th>
                                <th>Batch No</th>
                                <th>Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($transfer_lines as $data)
                            <tr>
                                <td id="editIdtModalShow" data-id="{{$data->id}}" data-product="{{ $data->product}}"
                                    data-unit_measure="{{ $data->qty_per_unit_of_measure }}"
                                    data-total_pieces="{{ $data->total_pieces }}"
                                    data-total_weight="{{ $data->total_weight }}"
                                    data-transfer_type="{{ $data->transfer_type }}"
                                    data-description="{{ $data->description }}" data-batch_no="{{ $data->batch_no }}"><a
                                        href="#">{{ $data->id }}</a>
                                </td>
                                <td>{{ $data->product_code }}</td>
                                <td>{{ $data->product }}</td>
                                <td>{{ number_format($data->qty_per_unit_of_measure, 2) }}</td>
                                <td>{{ $data->location_code }}</td>
                                <td>{{ $data->chiller_code }}</td>
                                <td>{{ $data->total_crates }}</td>
                                <td>{{ $data->full_crates }}</td>
                                <td>{{ $data->incomplete_crate_pieces }}</td>
                                <td>{{ $data->total_pieces }}</td>
                                <td>{{ $data->total_weight }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ $data->batch_no }}</td>
                                <td>{{ $helpers->amPmDate($data->created_at) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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

            } else {
                reading.readOnly = true;
            }

        });

        $('#carriage_type').on('select2:select', function (e) {
            let carriage = $(this).val()
            let element = document.getElementById("crates_div")
                        
            if (carriage == '1.8') {
                element.removeAttribute("hidden")
                $('#carriage_type').select2('destroy').select2();
                $('#no_of_crates').focus()
            } else {
                element.setAttribute("hidden", "hidden");
                getNet()
            }
            $('#tareweight').val(carriage)
        });

        $('#no_of_crates').on("input",function () {
            getNet()
        });

        $('#reading').on("input", function () {
            getNet()
        });
    });

    const getTotalTareweight = () => {
        let total_tare = 0

        let tare = $('#carriage_type').val()
        let no_of_crates = $('#no_of_crates').val()

        if(tare == 1.8 && no_of_crates == '') {
            alert('please enter no of crates')

        } else {
            if(parseFloat(tare) == 40) {
                // meat van
                $('#total_tare').val(tare)
                total_tare += parseFloat(tare)
            } else {
                let total = 0;
                total = parseFloat(tare) * parseFloat(no_of_crates)
                $('#total_tare').val(total)
                total_tare += total
            }
        }

        return total_tare
    }

    const getNet = () => {
        let total_tare = 0
        let tare = $('#carriage_type').val()
        let reading = $('#reading').val()

        if(tare == '') {
            alert('Please select carriage type first')
        } else {
            // proceed
            total_tare = getTotalTareweight()
        }

        net = parseFloat(reading) - parseFloat(total_tare)
        $('#net').val(net)
    }

    const validateOnSubmit = () => {
        $valid = true;

        var net = $('#net').val();
        var product_type = $('#product_type').val();
        var no_of_pieces = $('#no_of_pieces').val();
        var process = $('#production_process').val();
        var process_substring = process.substr(0, process.indexOf(' '));
        let loading_val = $('#loading_value').val()

        if (loading_val != 1) {
            alert('please wait for loading process code to complete')
            $valid = false;
        }

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

    //read scale
    const getScaleReading = () => {
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
