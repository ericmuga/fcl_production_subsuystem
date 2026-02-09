@extends('layouts.template_master')

@section('navbar')

<!-- Navbar -->
@if(request()->query('to_location') == '1570')
    @include('layouts.headers.butchery_header')
@elseif(request()->query('to_location') == '2500')
    @include('layouts.headers.highcare_header')
@elseif(request()->query('to_location') == '2595')
    @include('layouts.headers.highcare_header')
@elseif(request()->query('to_location') == '2055')
    @include('layouts.headers.sausage_header')
@elseif(request()->query('to_location') == '3035')
    @include('layouts.headers.petfood_header')
@elseif(request()->query('to_location') == '4400')
    @include('layouts.headers.freshcuts-bulk_header')
@elseif(request()->query('to_location') == '4450')
    @include('layouts.headers.qa_header')
@endif

<!-- /.navbar -->

@endsection

@section('content-header')
<h1 class="m-2">
    @php($fromLocation = $locations[request()->get('from_location')] ?? null)
    Receive IDTs {{ $fromLocation ? 'from '.$fromLocation.' ' : '' }}to {{ $locations[request()->get('to_location')] ?? '' }}
</h1>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"> Transfer From  Lines Entries | <span id="subtext-h1-title"><small> showing all
                            <strong></strong> entries
                            ordered by
                            latest</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example1" class="table display table-striped table-bordered table-hover"
                        width="100%">
                        <thead>
                            <tr>
                                <th>IDT No</th>
                                <th>Product Code</th>
                                <th>Product</th>
                                <th>From Location</th>
                                <th>To Location</th>
                                <th>Issued pieces</th>
                                <th>Issued Weight</th>
                                <th>Received Weight</th>
                                <th>Total Crates</th>
                                <th>Black Crates</th>
                                <th>Status</th>
                                <th>Issued By</th>
                                <th>Batch No</th>
                                <th>Issue Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>IDT No</th>
                                <th>Product Code</th>
                                <th>Product</th>
                                <th>From Location</th>
                                <th>To Location</th>
                                <th>Issued pieces</th>
                                <th>Issued Weight</th>
                                <th>Received Weight</th>
                                <th>Total Crates</th>
                                <th>Black Crates</th>
                                <th>Status</th>
                                <th>Issued By</th>
                                <th>Batch No</th>
                                <th>Issue Date</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($transfer_lines as $data)
                                <tr>
                                    <td>{{ $data->id }}</td>
                                    <td>{{ $data->product_code }}</td>
                                    <td>{{ $data->description  }}</td>
                                    <td>{{ $data->transfer_from }}</td>
                                    <td>{{ $data->location_code }}</td>
                                    <td>{{ $data->total_pieces }}</td>
                                    <td>{{ $data->total_weight  }}</td>
                                    <td>{{ $data->receiver_total_weight }}</td>
                                    <td>{{ $data->total_crates }}</td>
                                    <td>{{ $data->black_crates }}</td>
                                    @if($data->received_by == null)
                                        <td><span class="badge badge-secondary">pending</span></td>
                                    @elseif($data->received_by != null)
                                        <td><span class="badge badge-success">received</span></td>
                                    @endif
                                    <td>{{ $data->issued_by }}</td>
                                    <td>{{ $data->batch_no }}</td>
                                    <td>{{ $helpers->amPmDate($data->created_at) }}</td>
                                    @if ($data->received_by == null)
                                    <td>
                                        <button
                                            id="receiveModalShow"
                                            type="button"
                                            data-toggle="modal"
                                            data-target="#receiveModal"
                                            data-id="{{$data->id}}"
                                            data-product-name="{{ $data->description }}"
                                            data-issued-pieces="{{ $data->total_pieces }}"
                                            data-issued-weight="{{ $data->total_weight }}"
                                            data-unit-measure="{{ $data->unit_of_measure }}"
                                            data-unit-measure-value="{{ $data->qty_per_unit_of_measure }}"
                                            class="btn btn-warning btn-xs"
                                            title="Receive transfer"
                                            onclick="updateReceiveModalInputs(event)">
                                            <i class="fa fa-check"></i>
                                        </button>
                                    </td>
                                    @else
                                    <td><span class="badge badge-warning">no action</span></td>
                                    @endif
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

<div class="modal fade" id="receiveModal" tabindex="-1" role="dialog" aria-labelledby="receiveModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form id="form-save-batch" class="form-prevent-multiple-submits"
            action="{{ route('idt_receive') }}" method="post"> 
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="receiveModalLabel">Receive IDT</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card-body form-group">
                                <div class="form-group form-row">
                                    <label for="inputEmail3" class="col-sm-4 col-form-label">Product Name </label>
                                    <div class="col-sm-8">
                                        <input type="text" readonly class="form-control" value="" id="f_item"
                                            placeholder="" name="item">
                                        <input type="hidden" name="transfer_id" id="f_transfer_id" value="">
                                    </div>
                                </div>
                                <div class="form-group form-row">
                                    <label for="inputEmail3" class="col-sm-4 col-form-label">No of Pieces
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="number" class="form-control input_params" value=""
                                            id="receiver_total_pieces" name="receiver_total_pieces" placeholder="" required>
                                    </div>
                                </div>
                                <div class="form-group form-row" id="carriage_type_group">
                                    <label for="carriage_type" class="col-sm-4 col-form-label">Carriage Type</label>
                                    <select class="form-control col-sm-8" id="carriage_type" name="carriage_type" onchange="toggleCrateInputs(event)">
                                        <option disabled selected value="">Select Carriage Type</option>
                                        <option value="van">Van</option>
                                        <option value="crate">Crates</option>
                                    </select>
                                </div>
                                <div id="crate_inputs" class="form-group row" hidden>
                                    <div class="col-sm-6">
                                        <div class="row">
                                            <label for="inputEmail3" class="col-sm-5 col-form-label">Total Crates(incl.black)
                                            </label>
                                            <div class="col-sm-7">
                                                <input type="number" class="form-control tareweight" value="2" min="1" oninput="updateTare()" onchange="updateBlackMax()"
                                                    id="f_no_of_crates" placeholder="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="row">
                                            <label for="inputEmail3" class="col-sm-5 col-form-label">Black Crates
                                            </label>
                                            <div class="col-sm-7">
                                                <input type="number" class="form-control tareweight" value="1" min="0" oninput="updateTare()"
                                                    id="f_black_crates" placeholder="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="f_issued_pieces" name="issued_pieces" value="0">
                                <input type="hidden" id="f_issued_weight" name="issued_weight" value="0">
                                <input type="hidden" id="f_valid_match" name="valid_match" value="1">
                                <input type="" id="unit_measure" name="unit_measure" value="">
                                <input type="" id="unit_measure_value" name="unit_measure_value" value="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-row">
                                <label for="inputEmail3" class="col-sm-5 col-form-label"> Scale Reading(Kgs)</label>
                                <div class="col-sm-7">
                                    <input type="number" step=".01" class="form-control" value="0" id="f_weight"
                                        name="weight" placeholder="" readonly required>
                                </div>
                            </div>
                            @if(count($configs) === 0)
                            <div class="form-check" style="text-align: center">
                                <input type="checkbox" class="form-check-input" id="manual_weight" name="manual_weight">
                                <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                            </div>
                            @endif
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="inputEmail3" class="form-label">Tareweight(kgs)
                                        </label>
                                        <input type="number" readonly class="form-control input_params" value="0"
                                            id="f_tareweight" name="f_tareweight" placeholder="">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="inputEmail3" class="form-label"> Net Weight(Kgs)</label>
                                        <input type="number" step=".01" class="form-control" value="0" id="net" name="net"
                                            placeholder="" readonly required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" style="padding-left: 30%; padding-top: 5%">
                                <button type="button" onclick="getScaleReading()" id="weigh" value=""
                                    class="btn btn-primary btn-lg"><i class="fas fa-balance-scale"></i> Weigh</button>
                                <br>
                                @if(count($configs) === 0)
                                <small class="d-block">No comport configured</small>
                                @else
                                <small>Reading from : <input style="font-weight: bold; border: none" type="text"
                                    id="comport_value" value="{{ $configs[0]->comport }}" style="border:none"
                                    disabled></small>
                                @endif
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="location_code" id="location_code" value="3535">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                    <button class="btn btn-primary btn-lg btn-prevent-multiple-submits" type="submit" onclick="return validateOnSubmit()">Save</button>
                </div>
                <div id="loading_f" class="collapse">
                    <div class="row d-flex justify-content-center">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const tareInput = document.getElementById('f_tareweight');
    const weightInput = document.getElementById('f_weight');
    const netInput = document.getElementById('net');
    const unitMeasureInput = document.getElementById('unit_measure');
    const unitMeasureValueInput = document.getElementById('unit_measure_value');

    function toggleCrateInputs(event) {
        let selected = event.target.value;
        const crate_inputs = document.getElementById('crate_inputs');
        if (selected == "crate") {
            crate_inputs.removeAttribute('hidden');
        } else {
            crate_inputs.setAttribute('hidden', true);
        }
        updateTare();
    }

    function updateTare() {
        let carriage_type = document.getElementById('carriage_type').value;
        let tare = 0;
        if (carriage_type == 'van') {
            tare = 40;
        } else if (carriage_type == 'crate') {
            let no_of_crates = document.getElementById('f_no_of_crates').value;
            let black_crates = document.getElementById('f_black_crates').value;
            tare = ((parseInt(no_of_crates) * 1.8) + (parseInt(black_crates) * 0.2)).toFixed(2);
        }
        tareInput.value = tare;
        getNet();
    }

    function getNet() {
        let tareweight = tareInput.value
        let scale_reading = weightInput.value

        let net = parseFloat(scale_reading) - parseFloat(tareweight)
       netInput.value = net.toFixed(2);
    }

    function updateBlackMax() {
        const totalCrates = document.getElementById('f_no_of_crates').value;
        const blackCratesInput = document.getElementById('f_black_crates');
        blackCratesInput.max = totalCrates;
    }

    function isPieceBased() {
        const unit = (unitMeasureInput.value || '').toUpperCase();
        return unit === 'PC';
    }

    function calculateWeightFromPieces() {
        if (!isPieceBased()) {
            return;
        }

        const pieces = parseFloat(document.getElementById('receiver_total_pieces').value || 0);
        const unitVal = parseFloat(unitMeasureValueInput.value || 0);

        if (!pieces || !unitVal) {
            weightInput.value = 0;
            tareInput.value = 0;
            getNet();
            return;
        }

        const weight = pieces * unitVal;
        weightInput.value = weight.toFixed(2);
        tareInput.value = 0; // no carriage/tare for piece-based items
        getNet();
    }
    
    function updateReceiveModalInputs(event) {
        let button = event.currentTarget;
        let id = button.getAttribute('data-id');
        let product_name = button.getAttribute('data-product-name');
        let issued_pieces = button.getAttribute('data-issued-pieces');
        let issued_weight = button.getAttribute('data-issued-weight');
        let unit_measure = button.getAttribute('data-unit-measure');
        let unit_measure_value = button.getAttribute('data-unit-measure-value');

        console.log('updating issued pieces: ' + issued_pieces + ' issued weight: ' + issued_weight);

        document.getElementById('f_transfer_id').value = id;
        document.getElementById('f_item').value = product_name;
        document.getElementById('f_issued_pieces').value = issued_pieces;
        document.getElementById('f_issued_weight').value = issued_weight;
        unitMeasureInput.value = unit_measure;
        unitMeasureValueInput.value = unit_measure_value;

        const carriageGroup = document.getElementById('carriage_type_group');
        const carriageSelect = document.getElementById('carriage_type');
        const crateInputs = document.getElementById('crate_inputs');

        if (isPieceBased()) {
            if (carriageGroup) {
                carriageGroup.style.display = 'none';
            }
            if (carriageSelect) {
                carriageSelect.value = '';
            }
            if (crateInputs) {
                crateInputs.setAttribute('hidden', true);
            }

            calculateWeightFromPieces();
        } else {
            if (carriageGroup) {
                carriageGroup.style.display = '';
            }
        }
    }

    $(document).ready(function () {

        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        $('#f_weight').on("input", function () {
            getNet()
        });

        $('#manual_weight').change(function () {
            let manual_weight = document.getElementById('manual_weight');
            let reading = document.getElementById('f_weight');
            $('#f_weight').val("");
            if (manual_weight.checked == true) {
                reading.readOnly = false;
                reading.focus();
            } else {
                reading.readOnly = true;
            }           
        }); 

        $('#receiver_total_pieces').on('input', function () {
            calculateWeightFromPieces();
        });

    });

    const propCratesProperty = (no_of_crates) => {
        const isReadOnly = no_of_crates === 0;
        $('#f_no_of_crates, #f_black_crates').prop('readonly', isReadOnly);
    }

    const validateOnSubmit = () => {
        let status = true

        let total_crates = $("#f_no_of_crates").val();
        let black_crates = $("#f_black_crates").val();

        let recieving_pieces = $('#receiver_total_pieces').val();
        let receiving_weight = $('#net').val();

        let issued_pieces = $('#f_issued_pieces').val();
        let issued_weight = $('#f_issued_weight').val();


        if (black_crates > total_crates) {
            status = false
            alert("please ensure you have valid crates before submitting")

        } else if (parseInt(issued_pieces) != parseInt(recieving_pieces)) {
            console.log("issued pieces: " + issued_pieces + " recieving pieces: " + recieving_pieces)
            const response = confirm("Issued does not match Receiving Qty. Are you sure you want to continue?");

            if (response) {
                setMatchValidity(0)
                alert("Thanks for confirming");
            } else {
                status = false
                alert("You have cancelled this process");
            }
        } else if (parseInt(issued_weight) != parseInt(receiving_weight)) {
            console.log("issued weight: " + issued_weight + " recieving weight: " + receiving_weight)
            const response = confirm("Issued does not match Receiving Weight. Are you sure you want to continue?");

            if (response) {
                setMatchValidity(0)
                alert("Thanks for confirming");
            } else {
                status = false
                alert("You have cancelled this process");
            }
        }

        return status
    }

    const handleChange = () => {
        let total_crates = $("#total_crates").val();
        let full_crates = $("#full_crates").val();

        if (total_crates != '' && full_crates != '') {
            if (parseInt(total_crates) > parseInt(full_crates)) {
                $('.incomplete_pieces').show();
            } else {
                $('.incomplete_pieces').hide();
                $('#incomplete_pieces').val(0)
            }
        }
    }

    const setUserMessage = (field_succ, field_err, message_succ, message_err) => {
        document.getElementById(field_succ).innerHTML = message_succ
        document.getElementById(field_err).innerHTML = message_err
    }

    const setMatchValidity = (status) => {
        $("#valid_match").val(status)
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
                        var reading = document.getElementById('f_weight');
                        // console.log('weight: ' + obj.response);
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
