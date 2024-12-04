@extends('layouts.despatch_master')

@section('content')
    <form id="form-save-freshcuts" class="card-group text-center form-prevent-multiple-submits" action="{{ route('despatch_save_issued_idt') }}" method="post">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label for="product_code"> Product</label>
                    <select class="form-control select2" name="product_code" id="product_code" required>
                        <option selected disabled value>Select product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->code }}">
                                {{ $product->code }} - {{ $product->description }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-row">
                    <div class="col-md-6 form-group">
                        <label for="location_code">Transfer To</label>
                        <select class="form-control" name="location_code" id="location_code" required readonly>
                            <option selected value="1570">Butchery</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="carriage_type">Carriage Type</label>
                        <select class="form-control select2" name="carriage_type" id="carriage_type" onchange="updateCarriage(event)" required>
                            <option disabled selected value> -- select an option -- </option>
                            <option value="crate">Crate</option>
                            <option value="van">Van</option>
                        </select>
                    </div>
                </div>
                <div hidden id="crates_div" class="form-row">
                    <div class="col-md-6 from-group">
                        <label for="total_crates">Total Crates </label>
                        <input type="number" class="form-control" id="total_crates" value="1" name="total_crates" min="1" oninput="updateTare()">
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="black_crates">Black Crates </label>
                        <input type="number" class="form-control" id="black_crates" value="1" name="black_crates" min="0" oninput="updateTare()">
                    </div>
                </div>
                <div class="form-group mt-3">
                    <button type="button" onclick="getScaleReading()" id="weigh" value=""
                        class="btn btn-primary btn-lg"><i class="fas fa-balance-scale"></i> Weigh
                    </button>
                    @if(count($configs) > 0)
                        <small class="d-block">Reading from : <input style="font-weight: bold; border: none" type="text" id="comport_value"
                                value="{{ $configs[0]->comport }}" style="border:none" disabled></small>
                    @else
                        <small class="d-block">No comport configured</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label for="reading">Scale Reading</label>
                    <input type="number" step="0.01" class="form-control" id="reading" name="reading" value="0.00"
                        oninput="getNet()" placeholder="" readonly>
                </div>
                @if(count($configs) === 0)
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="manual_weight" name="manual_weight">
                    <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                </div> <br>
                @endif
                <input type="hidden" id="old_manual" value="{{ old('manual_weight') }}">
                <div class="form-row">
                    <div class="col-md-6 form-group">
                        <label for="tareweight">Tare-Weight</label>
                        <input type="number" class="form-control" id="tareweight" name="tareweight" value="0.00"
                            step=".01" placeholder="" readonly>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="net">Net Weight</label>
                        <input type="number" class="form-control" id="net" name="net" value="0.00" step=".01"
                            placeholder="" readonly>
                    </div>
                </div>
            </div>
        </div>
        <div class="card ">
            <div class="card-body">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="chiller_code">Transfer To Chiller </label>
                            <select class="form-control select2 locations" name="chiller_code" id="chiller_code" required>
                                <option disabled selected value> -- select an option -- </option>
                                @foreach($chillers as $chiller)
                                    <option value="{{ $chiller->chiller_code }}">
                                        {{ $chiller->chiller_code }} - {{ $chiller->description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="no_of_pieces">No. of pieces </label>
                            <input type="number" class="form-control" value="" id="no_of_pieces" name="no_of_pieces"
                                required>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 col-md-6">
                        <label for="batch_no">Batch No </label>
                        <input type="text" class="form-control" id="batch_no" value="" name="batch_no" required>
                    </div>
                    <div class="form-group col-12 col-md-6">
                        <label for="description">Description (optional)</label>
                        <input type="text" class="form-control" id="description" value="" name="description">
                    </div>
                </div> 
                
                <div hidden id="export_desc_div" class="row form-group">
                    <div class="col-md-6">
                        <label for="desc">Export Customer </label>
                        <input type="text" class="form-control" id="desc" value="" name="desc" placeholder="">
                    </div>
                    <div class="col-md-6">
                        <label for="order_no">Order No </label>
                        <input type="text" class="form-control" id="order_no" value="" name="order_no" placeholder="">
                    </div>
                </div>
                <div class="form-group" style="padding-top: 5%">
                    <button type="submit" onclick="return validateOnSubmit()"
                        class="btn btn-primary btn-lg btn-prevent-multiple-submits"><i
                            class="fa fa-paper-plane single-click" aria-hidden="true"></i> Save</button>
                </div>
            </div>
        </div>
    </form>
    <input type="hidden" class="input_checks" id="loading_value" value="0">

    <div id="loading" class="collapse">
        <div class="row d-flex justify-content-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>

    <button class="btn btn-primary my-4" data-toggle="collapse" data-target="#idt_entries"><i
            class="fa fa-plus"></i>
        Entries
    </button>

<div id="idt_entries" class="collapse">
    <hr>

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
                        <th>Chiller</th>
                        <th>Total Crates</th>
                        <th>Black Crates</th>
                        <th>Total Pieces</th>
                        <th>Total Weight</th>
                        <th>Description</th>
                        <th>Batch No</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>IDT No</th>
                        <th>Product Code</th>
                        <th>Product</th>
                        <th>Std Unit Measure</th>
                        <th>Transfer To </th>
                        <th>Chiller</th>
                        <th>Total Crates</th>
                        <th>Black Crates</th>
                        <th>Total Pieces</th>
                        <th>Total Weight</th>
                        <th>Description</th>
                        <th>Batch No</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach($transfer_lines as $data)
                        <tr>
                            <td id="editIdtModalShow" data-id="{{ $data->id }}"
                                data-product="{{ $data->product?? $data->product2  }}"
                                data-unit_measure="{{ $data->unit_of_measure }}"
                                data-total_pieces="{{ $data->total_pieces }}"
                                data-total_weight="{{ $data->total_weight }}"
                                data-transfer_type="{{ $data->transfer_type }}"
                                data-description="{{ $data->description }}"
                                data-batch_no="{{ $data->batch_no }}"><a href="#">{{ $data->id }}</a>
                            </td>
                            <td>{{ $data->product_code }}</td>
                            <td>{{ $data->product?? $data->product2 }}</td>
                            <td>{{ $data->unit_of_measure, 2 }}</td>
                            <td>{{ $data->location_code }}</td>
                            <td>{{ $data->chiller_code }}</td>
                            <td>{{ $data->total_crates?? 0 }}</td>
                            <td>{{ $data->black_crates?? 0 }}</td>
                            <td>{{ $data->total_pieces }}</td>
                            <td>{{ $data->total_weight }}</td>
                            <td>{{ $data->description }}</td>
                            <td>{{ $data->batch_no }}</td>
                            @if ($data->total_weight == 0 )
                            <td><span class="badge badge-danger">cancelled</span></td>
                            @elseif($data->total_weight > 0 && $data->received_by != null)
                            <td><span class="badge badge-success">received</span></td>
                            @elseif($data->total_weight > 0 && $data->received_by == null)
                            <td><span class="badge badge-info">waiting receipt</span></td>
                            @endif
                            <td>{{ \Carbon\Carbon::parse($data->created_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- slicing ouput data show -->

<!-- Edit Modal -->
<div id="editIdtModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-xl">
        <!--Start create user modal-->
        <form class="form-prevent-multiple-submits" id="form-edit-role"
            action="{{ route('freshcuts_cancel_idt') }}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editIdtModalLabel">Cancel Transfer for Idt No: <strong><input
                                style="border:none" type="text" id="item_id" name="item_id" value="" readonly></strong>
                    </h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card-group">
                        <div class="card">
                            <div class="card-body" style="">
                                <div class="form-group">
                                    <div class="row">
                                        <label for="edit_product" class="col-sm-3 col-form-label">Product Name </label>
                                        <div class="col-sm-9">
                                            <input type="text" readonly class="form-control" value="" id="edit_product"
                                                placeholder="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body form-group">
                                <div class="row">
                                    <label for="inputEmail3" class="col-sm-3 col-form-label">Transfer Type </label>
                                    <div class="col-sm-9">
                                        <select class="form-control select2" name="for_export_edit" id="for_export_edit"
                                            selected="selected" readonly>
                                            <option value="" selected disabled>Transfer Type </option>
                                            <option value="0"> Local</option>
                                            <option value="1"> Export</option>
                                        </select>
                                    </div>
                                </div><br>
                                <div class="row">
                                    <label for="inputEmail3" class="col-sm-3 col-form-label">Batch No </label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" value="" id="batch_no_edit"
                                            name="batch_no_edit" required placeholder="" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body text-center form-group">
                                <div class="row">
                                    <label for="inputEmail3" class="col-sm-6 col-form-label">Net
                                        Weight(Kgs)</label>
                                    <div class="col-sm-6">
                                        <input type="number" step="0.1" class="form-control" value="0" id="weight_edit"
                                            readonly name="weight_edit" placeholder="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-group">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button class="btn btn-warning btn-lg btn-prevent-multiple-submits"
                            onclick="return validateOnEditSubmit()" type="submit">
                            <i class="fa fa-save"></i> Cancel Transfer
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
    const carriage = document.getElementById('carriage_type')
    const tareWeightInput = document.getElementById('tareweight')
    const readingInput = document.getElementById('reading')

    function updateTare() {
        let tare
        let total_crates = document.getElementById('total_crates').value
        let black_crates = document.getElementById('black_crates').value
        if (!carriage.value) {
            tare = 0
        } else if (carriage.value == 'van') {
            tare = 40
        } else {
            tare = (parseFloat(1.8) * parseFloat(total_crates)) + parseFloat(black_crates) * 0.2
        }
        tareWeightInput.value = tare
        getNet()
    }

    function getNet() {
        if (!carriage.value) {
            alert('Please select carriage type first')
        } else {
            net = (parseFloat(readingInput.value) - parseFloat(tareWeightInput.value)).toFixed(2)
            document.getElementById('net').value = net
        }
    }

    function updateCarriage(event) {
        let crates_fields = document.getElementById("crates_div")
        if ( event.target.value == 'crate') {
            crates_fields.removeAttribute("hidden")
            $('#carriage_type').select2('destroy').select2();
            $('#total_crates').focus()
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

        $("body").on("click", "#editIdtModalShow", function (e) {
            e.preventDefault();

            let id = $(this).data('id');
            let product = $(this).data('product');
            let weight = $(this).data('total_weight');
            let transfer_type = $(this).data('transfer_type');
            let batch_no = $(this).data('batch_no');

            $('#item_id').val(id);
            $('#edit_product').val(product);
            $('#weight_edit').val(weight);
            $('#for_export_edit').val(transfer_type);
            $('#batch_no_edit').val(batch_no);

            $('#for_export_edit').select2('destroy').select2();

            $('#editIdtModal').modal('show');
        });
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

</script>
@endsection
