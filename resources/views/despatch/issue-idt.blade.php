@extends('layouts.despatch_master')

@section('content-header')
<h1 class="m-2">Issue IDTs from Despatch</h1>
@endsection

@section('content')
    <form id="form-issue-idt" class="card-group text-center form-prevent-multiple-submits" action="{{ route('despatch_save_issued_idt') }}" method="post">
        @csrf
        <div class="card">
            <div class="card-body from-group">
                <div class="form-group">
                    <label for="product_code" >Product</label>
                    <select class="form-control select2" name="product_code" id="product_code" onchange="loadProductDetails(event)" required>
                        <option selected disabled value>Select product</option>
                        @foreach($products as $product)
                        <option value="{{ $product->code }}">
                            {{ $product->code }} - {{ $product->description }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label class="form-label" for="location_code">Transfer To</label>
                        <select class="form-control" name="location_code" id="location_code" required>
                            <option selected disabled value>Select location</option>
                            @foreach ($locations as $code => $description)
                                <option value="{{ $code }}">{{ $description }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="form-label" for="tranfer_type">Transfer From</label>
                        <select class="form-control select2" name="tranfer_type" id="tranfer_type"
                            required>
                            <option value="" selected disabled>Transfer Type </option>
                            <option value="3535"> Local</option>
                            <option value="3600"> Export</option>
                            <option value="3540"> Third Party</option>
                            <option value="3555"> Old Factory </option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="carriage_type">Carriage Type</label>
                        <select class="form-control" name="carriage_type" id="carriage_type" onchange="updateCarriage(event)" required>
                            <option disabled selected value> -- select an option -- </option>
                            <option value="crate">Crate</option>
                            <option value="van">Van</option>
                        </select>
                    </div>                    
                </div>
                <div hidden id="crates_div" class="form-row">
                    
                    <div class="col-md-4 from-group">
                        <label for="total_crates">Total Crates Kg </label>
                        <input type="number" class="form-control" id="kg_total_crates" value="1" name="total_crates_kg" min="1" oninput="updateTare()">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="black_crates">Black Crates kg</label>
                        <input type="number" class="form-control" id="kg_black_crates" value="1" name="black_crates_kg" min="0" oninput="updateTare()">
                    </div>
                </div>
            </div>
        </div>
        <div id="pcWeightInputs" class="card">
            <div class="card-body form-group">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="unit_crate_count">Unit Count Per Crate</label>
                        <input type="number" readonly class="form-control input_params crates" value="0"
                            id="unit_crate_count" name="unit_crate_count" placeholder="">
                    </div>
                    <div class="col-md-6 form-gorup">
                        <label for="unit_measure">Item Unit Measure</label>
                        <input type="text" readonly class="form-control input_params" value="0"
                            id="unit_measure" name="unit_measure" placeholder="">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-check">
                        <input type="checkbox" class="form-check-input" id="incomplete_crates" name="incomplete_crates" onchange="togglePiecesInput()">
                        <label class="form-check-label" for="incomplete_crates">Incomplete Crate</label>
                    </div>
                </div><br>
                <div class="row">                    
                    <div class="col-md-6 form-group">
                        <label for="total_crates">Full Crates(excl. of black) </label>
                        <input type="number" class="form-control crates" id="pc_total_crates" value="0" name="total_crates" min="0" value="" onkeyup="calculateWeight()" placeholder="">
                    </div>
                    <div id="incomplete_pieces_group" class="col-md-6 form-group">
                        <label id="pieces-label" for="incomplete_pieces">Pieces in incomplete Crate</label>
                        <input type="number" class="form-control crates" min="0" id="incomplete_pieces" name="incomplete_pieces" readonly oninput="calculateWeight()">
                    </div>
                    <input type="hidden" name="no_of_pieces" id="no_of_pieces" value="0">                    
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="calculated_weight">Calculated pcs (kgs)</label>
                        <input type="number" class="form-control crates" value="0" id="calculated_pieces" min="1"
                            name="calculated_pieces" placeholder="" readonly>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="calculated_weight">Calculated Weight (kgs)</label>
                        <input type="number" class="form-control crates" value="0" id="calculated_weight" min="0.01"
                            name="calculated_weight" placeholder="" readonly>
                    </div>
                </div>
                <span class="text-danger" id="err1"></span>
                <span class="text-success" id="succ1"></span>
                <input type="hidden" name="crates_valid" id="crates_valid" value="0">
                <input type="hidden" name="" id="crates_valid" value="0">
            </div>
        </div>
        <div id="scaleInputs" class="card" hidden>
            <div class="card-body">
                <div class="form-group">
                    <label for="reading">Scale Reading</label>
                    <input type="number" step="0.01" class="form-control" id="reading" name="reading" value="0.00"
                        oninput="getNet()" placeholder="" @if(count($configs) > 0) readonly @endif>
                </div>
                @if(count($configs) > 0)
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="manual_weight" name="manual_weight">
                        <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                    </div>
                @endif
                <input type="hidden" id="old_manual" value="{{ old('manual_weight') }}">
                <div class="form-row">
                    <div class="col-md-6 form-group">
                        <label for="tareweight">Tare-Weight kg</label>
                        <input type="number" class="form-control" id="tareweight" name="tareweight" value="0.00"
                            step=".01" placeholder="" readonly>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="net">Net Weight</label>
                        <input type="number" class="form-control" id="net" name="net" value="0.00" step=".01"
                            placeholder="" readonly>
                    </div>
                </div>
                <div class="form-group mt-3">
                    @if(count($configs) > 0)
                        <button type="button" onclick="getScaleReading()" id="weigh" value=""
                            class="btn btn-primary btn-lg"><i class="fas fa-balance-scale"></i> Weigh
                        </button>
                        <small class="d-block">Reading from : <input style="font-weight: bold; border: none" type="text" id="comport_value"
                            value="{{ $configs[0]->comport }}" style="border:none" disabled></small>
                    @else
                        <small class="d-block">No comport configured</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body text-center form-group">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="inputEmail3">Batch No </label>
                        <input type="text" class="form-control" value="" id="batch_no" name="batch_no" required placeholder="">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="description">Description (optional)</label>
                        <input type="text" class="form-control" value="" id="description" name="description" placeholder="">
                    </div>
    
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="inputEmail3"> Pieces</label>
                        <input type="text" class="form-control" value="" id="no_of_pieces" name="no_of_pieces" placeholder="">
                    </div>    
                </div>
                
                <div class="div" style="padding-top: 5%">
                    <button type="submit" id="submit-btn" class="btn btn-primary btn-lg btn-prevent-multiple-submits"
                        onclick="return validateSubmitValues()"><i
                            class="fa fa-paper-plane single-click" aria-hidden="true"></i> Save</button>
                </div>
            </div>
        </div>
    </form>

    <button class="btn btn-primary my-4" data-toggle="collapse" data-target="#idt_entries"><i
            class="fa fa-plus"></i>
        Entries
    </button>

<div id="idt_entries" class="collapse">
    <hr>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"> Transfer Entries | <span id="subtext-h1-title"><small> showing all
                        <strong></strong> entries
                        ordered by
                        latest</small> </span></h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="example1" class="table display nowrap table-striped table-bordered table-hover"
                    width="100%">
                    <thead>
                        <tr>
                            <th>IDT No</th>
                            <th>Product Code</th>
                            <th>Product</th>
                            <th>Std Unit Measure</th>
                            <th>Transfer To </th>
                            <th>Transfer From </th>
                            <th>Chiller</th>
                            <th>Total Crates</th>
                            <th>Black Crates</th>
                            <th>Total Pieces</th>
                            <th>Total Weight</th>
                            <th>Description</th>
                            <th>Batch No</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>IDT No</th>
                            <th>Product Code</th>
                            <th>Product</th>
                            <th>Std Unit Measure</th>
                            <th>Transfer To </th>
                            <th>Transfer From </th>
                            <th>Chiller</th>
                            <th>Total Crates</th>
                            <th>Black Crates</th>
                            <th>Total Pieces</th>
                            <th>Total Weight</th>
                            <th>Description</th>
                            <th>Batch No</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach($transfer_lines as $data)
                            <tr>
                                <td id="editIdtModalShow" data-id="{{ $data->id }}"
                                    data-product="{{ $products->firstWhere('code', $data->product_code)->description ?? 'N/A' }}"
                                    data-unit_measure="{{ $products->firstWhere('code', $data->product_code)->unit_of_measure ?? 'N/A' }}"
                                    data-total_pieces="{{ $data->total_pieces }}"
                                    data-total_weight="{{ $data->total_weight }}"
                                    data-transfer_type="{{ $data->transfer_type }}"
                                    data-description="{{ $data->description }}"
                                    data-batch_no="{{ $data->batch_no }}">{{ $data->id }}
                                </td>
                                <td>{{ $data->product_code }}</td>
                                <td>{{ $products->firstWhere('code', trim($data->product_code))->description ?? 'N/A' }}</td>
                                <td>{{ $products->firstWhere('code', trim($data->product_code))->unit_of_measure ?? 'N/A' }}</td>
                                <td>{{ $data->location_code }}</td>
                                <td>{{ $data->transfer_from }}</td>
                                <td>{{ $data->chiller_code }}</td>
                                <td>{{ $data->total_crates ?? 0 }}</td>
                                <td>{{ $data->black_crates ?? 0 }}</td>
                                <td>{{ $data->total_pieces }}</td>
                                <td>{{ $data->total_weight }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ $data->batch_no }}</td>
                                <td>{{ \Carbon\Carbon::parse($data->created_at)->format('d/m/Y H:i') }}</td>
                                @if ($data->requires_approval == true && $data->approved_by == null && auth()->user()->role == 'QA')
                                    <td>
                                        <button
                                            type="button"
                                            data-toggle="modal"
                                            data-target="#approveModal"
                                            class="btn btn-sm btn-primary"
                                            data-id="{{ $data->id }}"
                                            data-product="{{ $products->firstWhere('code', $data->product_code)->description ?? 'N/A' }}"
                                            data-weight = "{{ $data->total_weight }}"
                                            data-pieces = "{{ $data->total_pieces }}"
                                            data-batch_no = "{{ $data->batch_no }}"
                                            data-send-location = "{{ $data->location_code }}"
                                            onclick="updateApprovalModal(event)"
                                        >
                                            Approve
                                        </button></td>
                                @elseif ($data->requires_approval == true && $data->approved_by == null)
                                    <td><span class="badge badge-info">waiting approval</span></td>
                                @elseif ($data->requires_approval == true && $data->approved_by != null && $data->approved == 0)
                                    <td><span class="badge badge-info">rejected</span></td>
                                @elseif ($data->total_weight == 0 )
                                    <td><span class="badge badge-danger">cancelled</span></td>
                                @elseif($data->received_by != null)
                                    <td><span class="badge badge-success">received</span></td>
                                @else
                                    <td><span class="badge badge-info">waiting receipt</span></td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div id="approveModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-xl">
        <form class="modal-content" id="approval-form" action="{{ route('approve_idt') }}" method="post">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="editIdtModalLabel">Approve Transfer: <strong><input
                            style="border:none" type="text" id="approve_transfer_id" name="id" value="" readonly></strong>
                </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="row">
                                <label for="edit_product" class="col-sm-3 col-form-label">Product Name</label>
                                <div class="col-sm-9">
                                    <input type="text" readonly class="form-control" value="" id="approve_product"
                                        placeholder="">
                                </div>
                            </div>
                            <div class="row">
                                <label for="edit_product" class="col-sm-3 col-form-label">Batch No</label>
                                <div class="col-sm-9">
                                    <input type="text" readonly class="form-control" value="" id="approve_batch_no"
                                        placeholder="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group form-row">
                            <label for="edit_product" class="col-sm-3 col-form-label">Pieces</label>
                            <div class="col-sm-9">
                                <input type="text" readonly class="form-control" value="" id="approve_pieces">
                            </div>
                        </div>
                        <div class="form-group form-row">
                            <label for="inputEmail3" class="col-sm-3 col-form-label">Weight</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" value="" id="approve_net" name="net" required readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row">
                            <label for="inputEmail3" class="col-sm-6 col-form-label">Send to Location</label>
                            <div class="col-sm-6">
                                <select class="form-control" name="location" id="approve_location" required>
                                    <option value="">Select Location</option>
                                    <option value="1570">Butchery</option>
                                    <option value="2595">Highcare</option>
                                    <option value="2055">Sausage</option>
                                    <option value="4300">Incineration</option>
                                    <option value="4450">QA</option>
                                    <option value="3035">Petfood</option>
                                    <option value="3535">local 3535</option>
                                    <option value="3600">export 3600</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="narration">Narration</label>
                            <textarea class="form-control" name="narration" id="narration" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger btn-lg" name="approve" value="0" onclick="showLoadingModal()">
                    Reject
                </button>
                <button type="submit" class="btn btn-primary btn-lg" name="approve" value="1" onclick="showLoadingModal()">
                    Approve
                </button>
            </div>
        </form>
    </div>
</div>
<!--End Approval modal-->

@endsection

@section('scripts')
<script>

    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const param = urlParams.keys().next().value;
        if (param == 'butchery') {
            const locationSelect = document.getElementById('location_code');
            for (let i = 0; i < locationSelect.options.length; i++) {
                if (locationSelect.options[i].value === '1570') {
                    locationSelect.options[i].selected = true;
                } else {
                    locationSelect.options[i].disabled = true;
                }
            }
        }
    });

    const carriage = document.getElementById('carriage_type')
    const tareWeightInput = document.getElementById('tareweight')
    const readingInput = document.getElementById('reading')
    const crates_fields = document.getElementById("crates_div")
    const incomplete_pieces_input = document.getElementById('incomplete_pieces');
    let selectedProduct;
    const products = @json($products);
    const configs = @json($configs);

    function updateApprovalModal(event) {
        let btn = event.currentTarget
        let id = btn.getAttribute('data-id')
        let product = btn.getAttribute('data-product')
        let weight = btn.getAttribute('data-weight')
        let pieces = btn.getAttribute('data-pieces')
        let batch_no = btn.getAttribute('data-batch_no')
        let send_location = btn.getAttribute('data-send-location')

        document.getElementById('approve_transfer_id').value = id
        document.getElementById('approve_product').value = product
        document.getElementById('approve_batch_no').value = batch_no
        document.getElementById('approve_pieces').value = pieces
        document.getElementById('approve_net').value = weight
        document.getElementById('approve_location').value = send_location
    }

    function updateTare() {
        let tare = 0.00;
        let total_crates = document.getElementById('kg_total_crates').value
        let black_crates = document.getElementById('kg_black_crates').value
        if (!carriage.value) {
            tare = 0
        } else if (carriage.value == 'van') {
            tare = 40
        } else {
            tare = (parseFloat(1.8) * parseFloat(total_crates)) + parseFloat(black_crates) * 0.2
        }
        // tareWeightInput.value = tare
        tareWeightInput.value = tare.toFixed(2)
        getNet()
    }

    function getNet() {
        if (!carriage.value) {
            alert('Please select carriage type first')
            if (readingInput.value == '') {
                readingInput.value = 0;
            }
        } else {
            let readingValue = parseFloat(readingInput.value) || 0;
            let tareValue = parseFloat(tareWeightInput.value) || 0;
            let net = (readingValue - tareValue).toFixed(2);
            document.getElementById('net').value = net;
        }
    }

    function updateCarriage(event) {
        if ( event.target.value == 'crate') {
            crates_fields.removeAttribute("hidden")
        } else {
            crates_fields.setAttribute("hidden", "hidden");
        }
        updateTare()
    }

    $(document).ready(function () {        

        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        let reading = document.getElementById('reading');

        if (($('#old_manual').val()) == "on" || configs.length == 0) {
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
            $('#reading').val("");
            if (manual_weight.checked == true) {
                reading.readOnly = false;
                reading.focus();
            } else {
                reading.readOnly = true;
            }

        });

        $('.crates').on("keyup change", function () {
            validateCrates()
        })
    });

    const validateOnSubmit = () => {
        $valid = true;

        var net = $('#net').val();
        var product_type = $('#product_type').val();
        var no_of_pieces = $('#no_of_pieces').val();
        var transfer_type = $('#transfer_type').val();
        var desc = $('#desc').val();
        var order_no = $('#order_no').val();

        if (net == "" || parseFloat(net) <= 0.00) {
            $valid = false;
            alert("Please ensure you have valid netweight.");
        }

        //check main product pieces
        if (transfer_type == '2' && (desc == '' || order_no == '')) {
            $valid = false;
            alert("Please ensure you have description set,\nand order no. for exports");
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

function loadProductDetails (event) {
    // get the product
    input = event.target;
    item_code = input.value;
    selectedProduct = products.find(product => product.code === item_code);
    pcCrateInput = document.getElementById('pc_total_crates');

    // update the unit measure input
    productUnitMeasure = selectedProduct.unit_of_measure;
    product_unit_count_per_crate = selectedProduct.unit_count_per_crate;
    document.getElementById('unit_measure').value = productUnitMeasure;

    // show weight or pieces input based on the unit of measure
    const scaleInputs = document.getElementById('scaleInputs');
    const pcWeightInputs = document.getElementById('pcWeightInputs');

    if (productUnitMeasure == 'PC' && selectedProduct.unit_count_per_crate > 0) {
        pcCrateInput.setAttribute('required', true);
        crates_fields.setAttribute("hidden", "hidden");
        document.getElementById('unit_crate_count').value = selectedProduct.unit_count_per_crate;
        carriage.value = 'crate';
        carriage.setAttribute('disabled', true);
        scaleInputs.setAttribute('hidden', true);
        pcWeightInputs.removeAttribute('hidden');
        calculateWeight();
    } else { 
        scaleInputs.removeAttribute('hidden');
        pcWeightInputs.setAttribute('hidden', true);
        carriage.removeAttribute('disabled');
        pcCrateInput.removeAttribute('required');
    }

    // set max for pieces in incomplete crate
    document.getElementById('incomplete_pieces').max = selectedProduct.unit_count_per_crate;
}

const defaultCrateCounts = (product_code) => {
    const list = {
        'J31015601': 45, //safari beef sausage 500gms
        'J31011301': 45, //Pork Catering Xpt-500gms
        'J31011302': 45, //Pork Catering 1Kg Xpt
    }

    return list[product_code]
}

const validateSubmitValues = () => {
    let status = true

    if (selectedProduct.unit_of_measure == 'PC') {
        let total_crates = $("#pc_total_crates").val();
        let incomplete_crates = $('#incomplete_crates').is(':checked');
        let full_crates;
        if (incomplete_crates) {
            full_crates = total_crates
        } else {
            full_crates = total_crates - 1
        }
        let incomplete_pieces = $('#incomplete_pieces').val();

        let pieces = $('#pieces').val();
        let weight = $('#weight').val();

        let crates_validity = $("#crates_valid").val();

        let batchField = document.getElementById('batch');

        let calculated_weight = $('#calculated_weight').val();

        if (calculated_weight == 0) {
            status = false
            alert("please ensure you have valid weight")
        } else if (incomplete_crates && (parseInt(incomplete_pieces) < 1)) {
            status = false
            alert("please enter incomplete pieces")
        } else if (parseInt(pieces) <= 0 || parseFloat(weight) <= 0) {
            status = false
            alert("please ensure the pieces and weight have a value of more than zero")
        }

        if (batchField.value.trim() === '') {
            event.preventDefault();
            alert('Batch Number field is required.');
        }

    }

    return status
}

const setCratesValidityMessage = (field_succ, field_err, message_succ, message_err) => {
    document.getElementById(field_succ).innerHTML = message_succ
    document.getElementById(field_err).innerHTML = message_err
}

const validateCrates = () => {
    let btn = document.getElementById('submit-btn')
    let crate_unit_count = document.getElementById('unit_crate_count').value
    let incomplete_pieces = document.getElementById('incomplete_pieces').value
    let incomplete_crates = document.getElementById('incomplete_crates').checked

    if (incomplete_crates && (parseInt(incomplete_pieces) >= parseInt(crate_unit_count))) {
        setCratesValidity(0)
        setCratesValidityMessage('succ1', 'err1', '', 'incomplete crate pieces cannot be greater than crate unit count')
        $('#pieces').val(0)
        $('#weight').val(0)
        btn.disabled = true
    } else {
        setCratesValidity(1)
        setCratesValidityMessage('succ1', 'err1', '', '')
        calculateWeight()
        btn.disabled = false
    }
}

const calculateWeight = () => {
    let total_crates = document.getElementById('pc_total_crates').value;
    let unit_crate_count = document.getElementById('unit_crate_count').value;
    let incomplete_crates = document.getElementById('incomplete_crates').checked;
    let incomplete_pieces = document.getElementById('incomplete_pieces').value;
    let piecesInput = document.getElementById('calculated_pieces');
    let weightInput = document.getElementById('calculated_weight');
    let weight_per_unit = selectedProduct.qty_per_unit_of_measure;
    let crate_unit_count = selectedProduct.unit_count_per_crate;

    console.log('total_crates:', total_crates);
    console.log('unit_crate_count:', unit_crate_count);
    console.log('incomplete_crates:', incomplete_crates);
    console.log('incomplete_pieces:', incomplete_pieces);
    console.log('piecesInput:', piecesInput);
    console.log('weightInput:', weightInput);
    console.log('weight_per_unit:', weight_per_unit);
    console.log('crate_unit_count:', crate_unit_count);

    if (incomplete_crates) {
        let full_crates = total_crates;
        total_pieces = ((full_crates * unit_crate_count) + parseInt(incomplete_pieces)).toFixed(2);
    } else if (crate_unit_count == 0) {
        total_pieces = incomplete_pieces;
    } else {
        total_pieces = (total_crates * unit_crate_count).toFixed(2);
    } 

    let total_weight = total_pieces * weight_per_unit;
    piecesInput.value = Math.floor(total_pieces);
    weightInput.value = total_weight.toFixed(2);
}

const setCratesValidity = (status) => {
    $("#crates_valid").val(status);
}

const padZero = (num) => {
    return num < 10 ? `0${num}` : num;
}

const togglePiecesInput = () => {
    let incomplete_crates = $('#incomplete_crates').is(':checked')
    incomplete_pieces_input.value = 0
    if (incomplete_crates) {
        incomplete_pieces_input.removeAttribute('readonly')
    } else {
        incomplete_pieces_input.setAttribute('readonly', true)
    }
    calculateWeight()
}

</script>
@endsection
