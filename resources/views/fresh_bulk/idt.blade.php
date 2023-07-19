@extends('layouts.freshcuts-bulk_master')

@section('content')
<form id="form-save-freshcuts" class="form-prevent-multiple-submits"
    action="{{ route('freshcuts_create_idt') }}" method="post">
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
                                @foreach($combinedResult as $t)
                                    <option value="{{ $t->code }}">
                                        {{ $t->code.' - '.$t->description.'- '.$t->barcode }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Transfer To</label>
                        <select class="form-control select2" name="transfer_to" id="transfer_to" required>
                            <option disabled selected> -- select a transfer location -- </option>
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
                    <div class="col-md-5">
                        <label for="exampleInputPassword1">Carriage Type</label>
                        <select class="form-control select2" name="carriage_type" id="carriage_type" required>
                            <option disabled selected value> -- select an option -- </option>
                            <option value="1.8">Crate</option>
                            <option value="40">Van</option>
                        </select>
                    </div>
                    <div hidden id="crates_div" class="col-md-7">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Total Crates </label>
                                    <input type="number" class="form-control" id="no_of_crates" value=""
                                        name="no_of_crates" min="1" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Black Crates </label>
                                    <input type="number" class="form-control" id="black_crates" value=""
                                        name="black_crates" min="1" placeholder="">
                                </div>
                            </div>
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
                {{-- @if (config('app.show_manual_weight') == 1) --}}
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="manual_weight" name="manual_weight">
                    <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                </div> <br>
                {{-- @endif --}}
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
                    <div class="row">
                        <div class="col-md-6">
                            <label for="exampleInputPassword1">Transfer To Chiller </label>
                            <select class="form-control select2 locations" name="chiller_code" id="chiller_code"
                                required>
                                <option value="">Select chiller</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="exampleInputPassword1">No. of pieces </label>
                            <input type="number" class="form-control" value="" id="no_of_pieces" name="no_of_pieces"
                                required>
                        </div>
                    </div>
                </div> <br>
                <div class="row form-group">
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Batch No </label>
                        <input type="text" class="form-control" id="batch_no" value="" name="batch_no" placeholder=""
                            required>
                    </div>
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Description (<i>optional</i>)</label>
                        <input type="text" class="form-control" id="desc1" value="" name="desc1" placeholder="">
                    </div>
                </div>
                <div hidden id="export_desc_div" class="row form-group">
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Export Customer </label>
                        <input type="text" class="form-control" id="desc" value="" name="desc" placeholder="">
                    </div>
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Order No </label>
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
        Entries
    </button>
</div>

<div id="slicing_output_show" class="collapse">
    <hr>
    <div class="row">
        <div class="col-md-12">
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
                                <th>Location </th>
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
                                <th>Location </th>
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
                                        data-unit_measure="{{ $data->qty_per_unit_of_measure }}"
                                        data-total_pieces="{{ $data->total_pieces }}"
                                        data-total_weight="{{ $data->total_weight }}"
                                        data-transfer_type="{{ $data->transfer_type }}"
                                        data-description="{{ $data->description }}"
                                        data-batch_no="{{ $data->batch_no }}"><a href="#">{{ $data->id }}</a>
                                    </td>
                                    <td>{{ $data->product_code }}</td>
                                    <td>{{ $data->product?? $data->product2 }}</td>
                                    <td>{{ number_format($data->qty_per_unit_of_measure, 2) }}</td>
                                    <td>{{ $data->location_code }}</td>
                                    <td>{{ $data->chiller_code }}</td>
                                    <td>{{ $data->total_crates }}</td>
                                    <td>{{ $data->black_crates }}</td>
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

<!-- Edit Modal -->
<div id="editIdtModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-xl">
        <!--Start create user modal-->
        <form class="form-prevent-multiple-submits" id="form-edit-role"
            action="{{ route('freshcuts_cancel_idt') }}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Cancel Transfer for Idt No: <strong><input
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
                                        <label for="inputEmail3" class="col-sm-3 col-form-label">Product Name </label>
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
    $(document).ready(function () {

        $('#transfer_to').change(function () {
            // Get references to the select elements
            const transferTypeSelect = document.getElementById('transfer_type');

            // Check if the selected value is '3535' (Despatch)
            if ($(this).val() == '3535') {
                // Enable the 'Export' option in the transferTypeSelect
                transferTypeSelect.options[2].disabled = false;
            } else {
                // Disable the 'Export' option in the transferTypeSelect
                transferTypeSelect.options[2].disabled = true;

                // remove required when its not despatch
                $('#chiller_code').prop('required', false);
                // $('#batch_no').prop('required', false);

                // If 'Export' option was selected, reset the selection
                if (transferTypeSelect.value == '2') {
                    $('#transfer_type').select2("val", "All");
                }
            }
        })

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

        $(document).on('change', '#product', function () {
            let product_code = $(this).val()
            transferToControlHandler(product_code)
            fetchTransferToLocations(product_code);
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

        $('#transfer_type').on('select2:select change', function (e) {
            let selected_type = $(this).val()
            let element = document.getElementById("export_desc_div")

            if (selected_type == '2') {
                element.removeAttribute("hidden")
            } else {
                element.setAttribute("hidden", "hidden");
            }
        });

        $('#black_crates').on("input", function () {
            getNet()
        });

        $('#reading').on("input", function () {
            getNet()
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

    const startsWithCharacter =(str, character) => {
        return str.startsWith(character);
    }

    const transferToControlHandler = (product_code) => {
        const transferToSelect = document.getElementById('transfer_to');

        const SAUSAGE_OPTION_INDEX = 1;
        const HIGHCARE_OPTION_INDEX = 2;
        const DESPATCH_OPTION_INDEX = 3;

        const isStartsWithG = startsWithCharacter(product_code, 'G');
        const isStartsWithJ = startsWithCharacter(product_code, 'J');

        transferToSelect.options[SAUSAGE_OPTION_INDEX].disabled = isStartsWithJ;
        transferToSelect.options[HIGHCARE_OPTION_INDEX].disabled = isStartsWithJ;
        transferToSelect.options[DESPATCH_OPTION_INDEX].disabled = isStartsWithG;
    };

    const fetchTransferToLocations = (prod_code) => {

        const url = '/fetch-transferToLocations-axios'

        const request_data = {
            product_code: prod_code
        }

        return axios.post(url, request_data)
            .then((res) => {
                if (res) {
                    //empty the select list first
                    $(".locations").empty();

                    $.each(res.data, function (key, value) {
                        $(".locations").append($("<option></option>").attr("value", value
                                .chiller_code)
                            .text(value.location_code + ' ' + value.description));
                    });
                }
            })
            .catch((error) => {
                console.log(error);
            })
    }

    const getTotalTareweight = () => {
        let total_tare = 0

        let tare = $('#carriage_type').val()
        let no_of_crates = $('#no_of_crates').val()
        let black_crates = $('#black_crates').val()

        if (tare == 1.8 && (no_of_crates == '' || black_crates == '')) {
            alert('please enter total crates and black crates count')

        } else {
            if (parseFloat(tare) == 40) {
                // meat van
                $('#total_tare').val(tare)
                total_tare += parseFloat(tare)
            } else {
                let total = 0
                let black_tare_add = parseFloat(black_crates) * 0.2
                total = (parseFloat(tare) * parseFloat(no_of_crates)) + black_tare_add
                total_tare += total
            }
        }
        $('#total_tare').val(total_tare.toFixed(2))

        return total_tare
    }

    const getNet = () => {
        let total_tare = 0
        let tare = $('#carriage_type').val()
        let reading = $('#reading').val()

        if (tare == '') {
            alert('Please select carriage type first')
        } else {
            // proceed
            total_tare = getTotalTareweight()
        }

        net = (parseFloat(reading) - parseFloat(total_tare)).toFixed(2)
        $('#net').val(net)
    }

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
