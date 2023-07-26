@extends('layouts.highcare_master')

@section('content')

<!--freshCuts-->
<div class="modal fade" id="despatchReceiveFreshModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form id="form-save-batch" class="form-prevent-multiple-submits"
            action="{{ route('update_idt_receive') }}" method="post"> 
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Receive Fresh Cuts & Bulk Dispatch Transfer</h5>
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
                                        <label for="inputEmail3" class="col-sm-4 col-form-label">Product Name </label>
                                        <div class="col-sm-8">
                                            <input type="text" readonly class="form-control" value="" id="f_item"
                                                placeholder="" name="item">
                                            <input type="hidden" name="product" id="f_product" value="">
                                            <input type="hidden" name="item_id" id="f_item_id" value="">
                                        </div>
                                    </div> <br>
                                    <div class="row">
                                        <label for="inputEmail3" class="col-sm-4 col-form-label">Item Unit Measure
                                        </label>
                                        <div class="col-sm-8">
                                            <input type="number" readonly class="form-control input_params" value="0"
                                                id="f_unit_measure" name="unit_measure" placeholder="">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="row">
                                                <label for="inputEmail3" class="col-sm-5 col-form-label">Total Crates
                                                </label>
                                                <div class="col-sm-7">
                                                    <input type="number" class="form-control tareweight" value="0"
                                                        id="f_no_of_crates" placeholder="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="row">
                                                <label for="inputEmail3" class="col-sm-5 col-form-label">Black Crates
                                                </label>
                                                <div class="col-sm-7">
                                                    <input type="number" class="form-control tareweight" value="0"
                                                        id="f_black_crates" placeholder="">
                                                </div>
                                            </div>
                                        </div>
                                    </div> <br>
                                    <div class="row">
                                        <label for="inputEmail3" class="col-sm-5 col-form-label">Total Tareweight(kgs)
                                        </label>
                                        <div class="col-sm-7">
                                            <input type="number" readonly class="form-control input_params" value="0"
                                                id="f_tareweight" name="f_tareweight" placeholder="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body row">
                                <label for="inputEmail3" class="col-sm-5 col-form-label"> Scale Reading(Kgs)</label>
                                <div class="col-sm-7">
                                    <input type="number" step=".01" class="form-control" value="0" id="f_weight"
                                        name="weight" placeholder="" readonly required>
                                </div>
                            </div>
                            {{-- @if (config('app.show_manual_weight') == 1) --}}
                            <div class="form-check" style="text-align: center">
                                <input type="checkbox" class="form-check-input" id="manual_weight" name="manual_weight">
                                <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                            </div>
                            {{-- @endif --}}
                            <div class="card-body row">
                                <label for="inputEmail3" class="col-sm-5 col-form-label"> Net Weight(Kgs)</label>
                                <div class="col-sm-7">
                                    <input type="number" step=".01" class="form-control" value="0" id="net" name="net"
                                        placeholder="" readonly required>
                                </div>
                            </div>
                            <div class="form-group" style="padding-left: 30%; padding-top: 5%">
                                <button type="button" onclick="getScaleReading()" id="weigh" value=""
                                    class="btn btn-primary btn-lg"><i class="fas fa-balance-scale"></i> Weigh</button>
                                <br>
                                <small>Reading from : <input style="font-weight: bold; border: none" type="text"
                                        id="comport_value" value="{{ $configs[0]->comport }}" style="border:none"
                                        disabled></small>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body form-group">
                                <div class="row">
                                    <label for="inputEmail3" class="col-sm-4 col-form-label">No of Pieces
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="number" class="form-control input_params" value=""
                                            id="f_no_of_pieces" name="f_no_of_pieces" placeholder="" required>
                                    </div>
                                </div>
                                <div class="form-group" style="padding-left: 30%; padding-top: 15%">
                                    <button type="submit" class="btn btn-primary btn-lg btn-prevent-multiple-submits"
                                        onclick="return validateOnSubmitFresh()"><i
                                            class="fa fa-paper-plane single-click" aria-hidden="true"></i> Save</button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="f_issued_weight" value="0">
                        <input type="hidden" id="f_valid_match" name="valid_match" value="1">
                    </div>
                    <input type="hidden" name="location_code" id="location_code" value="3535">
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
<!--freshCuts-->

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"> Transfer From Butchery Lines Entries | <span id="subtext-h1-title"><small> showing all
                            <strong></strong> entries
                            ordered by
                            latest</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example1" class="table display nowrap table-striped table-bordered table-hover"
                        width="100%">
                        <thead>
                            <tr>
                                <th>IDT No</th>
                                <th>Product Code</th>
                                <th>Product</th>
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
                                    <td>{{ $data->product?? $data->product2  }}</td>
                                    <td>{{ $data->total_crates }}</td>
                                    <td>{{ $data->black_crates }}</td>
                                    @if($data->received_by == null)
                                        <td><span class="badge badge-secondary">pending</span></td>
                                    @elseif($data->received_by != null)
                                        <td><span class="badge badge-success">received</span></td>
                                    @endif
                                    <td>{{ $data->username }}</td>
                                    <td>{{ $data->batch_no }}</td>
                                    <td>{{ $helpers->amPmDate($data->created_at) }}</td>
                                    @if ($data->received_by == null)
                                    <td>
                                        <button type="button" data-id="{{$data->id}}"
                                            data-product="{{ $data->product_code }}"
                                            data-unit_count="{{ $data->unit_count_per_crate }}"
                                            data-unit_measure="{{ number_format($data->qty_per_unit_of_measure, 3) }}"
                                            data-item="{{ $data->product?? $data->product2 }}" data-total_pieces="{{ $data->total_pieces }}"
                                            data-total_weight="{{ $data->total_weight }}"
                                            data-carriage="{{ $data->total_weight }}"
                                            data-crates="{{ $data->total_crates }}"
                                            data-black_crates="{{ $data->black_crates }}" class="btn btn-warning btn-xs"
                                            title="Receive transfer" id="despatchReceiveFreshModalShow"><i
                                                class="fa fa-check"></i>
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
<!-- slicing ouput data show -->

@endsection

@section('scripts')
<script>
    $(document).ready(function () {

        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        $('#despatchReceiveFreshModal').on('shown.bs.modal', function (e) {
            e.preventDefault()
            // calculate total tare..
            getIssuedTareweight()
        })

        $('.crates').keyup(function () {
            validateCrates()
        })

        $('#f_weight').on("input", function () {
            getNet()
        });

        $('#manual_weight').change(function () {
            let manual_weight = document.getElementById('manual_weight');
            let reading = document.getElementById('f_weight');
            if (manual_weight.checked == true) {
                reading.readOnly = false;
                reading.focus();
                $('#f_weight').val("");

            } else {
                reading.readOnly = true;
            }
        });

        $("body").on("click", "#despatchReceiveFreshModalShow", function (e) {
            e.preventDefault();

            let id = $(this).data('id');
            let code = $(this).data('product');
            let item = $(this).data('item');
            let unit_measure = $(this).data('unit_measure');
            let issued_weight = $(this).data('total_weight');
            let crates = $(this).data('crates');
            let black_crates = $(this).data('black_crates');

            $('#f_item_id').val(id);
            $('#f_product').val(code); //item_code
            $('#f_item').val(item); //item
            $('#f_unit_measure').val(unit_measure);
            $('#f_issued_weight').val(issued_weight);
            $('#f_no_of_crates').val(crates);
            $('#f_black_crates').val(black_crates);

            $('#despatchReceiveFreshModal').modal('show');
        });

    });

    const getIssuedTareweight = () => {
        let tare = 40
        let no_of_crates = $('#f_no_of_crates').val();
        let black_crates = $('#f_black_crates').val();

        if (no_of_crates != 0) {
            tare = (parseInt(no_of_crates) * 1.8) + (parseInt(black_crates) * 0.2)
        }

        $('#f_tareweight').val(tare.toFixed(2))

        propCratesProperty(no_of_crates)
    }

    const propCratesProperty = (no_of_crates) => {
        const isReadOnly = no_of_crates === 0;
        $('#f_no_of_crates, #f_black_crates').prop('readonly', isReadOnly);
    }

    const validateOnSubmitFresh = () => {
        let status = true

        let weight = $('#net').val();
        let issued_weight = $('#f_issued_weight').val();

        if (parseFloat(weight) <= 0) {
            status = false
            alert("please ensure the weight has a value of more than zero")
        } else if (parseFloat(weight) != parseFloat(issued_weight)) {
            const response = confirm(
                "Issued weight does not match Receiving Weight. Are you sure you want to continue?");

            if (response) {
                setMatchValidityFresh(0)
                alert("Thanks for confirming");
            } else {
                status = false
                alert("You have cancelled this process");
            }
        }

        return status
    }
    const validateOnSubmit = () => {
        let status = true

        let total_crates = $("#total_crates").val();
        let full_crates = $("#full_crates").val();
        let incomplete_pieces = $('#incomplete_pieces').val();

        let pieces = $('#pieces').val();
        let weight = $('#weight').val();

        let issued_pieces = $('#issued_pieces').val();
        let issued_weight = $('#issued_weight').val();

        let crates_validity = $("#crates_valid").val();

        if (crates_validity == 0) {
            status = false
            alert("please ensure you have valid crates before submitting")

        } else if (parseInt(full_crates) < parseInt(total_crates) && (parseInt(incomplete_pieces) < 1)) {
            status = false
            alert("please enter incomplete pieces")
        } else if (parseInt(pieces) <= 0 || parseFloat(weight) <= 0) {
            status = false
            alert("please ensure the pieces and weight have a value of more than zero")
        } else if (parseInt(issued_pieces) != parseInt(pieces)) {
            const response = confirm("Issued does not match Receiving Qty. Are you sure you want to continue?");

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

    const setCratesValidityMessage = (field_succ, field_err, message_succ, message_err) => {
        document.getElementById(field_succ).innerHTML = message_succ
        document.getElementById(field_err).innerHTML = message_err
    }

    const setUserMessage = (field_succ, field_err, message_succ, message_err) => {
        document.getElementById(field_succ).innerHTML = message_succ
        document.getElementById(field_err).innerHTML = message_err
    }

    const validateCrates = () => {
        console.log('validating crates')
        let crate_unit_count = $('#unit_crate_count').val()
        let incomplete_pieces = $('#incomplete_pieces').val()

        let total_crates = $("#total_crates").val();
        let full_crates = $("#full_crates").val();

        let diff = parseInt(total_crates) - parseInt(full_crates)

        if (parseInt(incomplete_pieces) >= parseInt(crate_unit_count)) {
            setCratesValidity(0)
            setCratesValidityMessage('succ1', 'err1', '', 'invalid incomplete crate pieces')
            $('#pieces').val(0)
            $('#weight').val(0)

        } else {
            if (diff < 0 || diff > 1) {
                setCratesValidity(0)
                setCratesValidityMessage('succ1', 'err1', '', 'invalid crates')
                $('#pieces').val(0)
                $('#weight').val(0)

            } else {
                setCratesValidity(1)
                setCratesValidityMessage('succ1', 'err1', '', '')
                calculatePiecesAndWeight(full_crates)
            }
        }

    }

    const calculatePiecesAndWeight = (full_crates) => {
        let crate_unit_count = $('#unit_crate_count').val()
        let incomplete_pieces = $('#incomplete_pieces').val()
        let unit_measure = $('#unit_measure').val()

        let pieces = (parseInt(full_crates) * parseInt(crate_unit_count)) + parseInt(incomplete_pieces)
        let weight = pieces * unit_measure

        $('#pieces').val(pieces)
        $('#weight').val(weight)
    }

    const setCratesValidity = (status) => {
        $("#crates_valid").val(status)
    }

    const setMatchValidity = (status) => {
        $("#valid_match").val(status)
    }

    const setMatchValidityFresh = (status) => {
        $("#f_valid_match").val(status)
    }

    const getNet = () => {
        let issued_tareweight = $('#f_tareweight').val()
        let scale_reading = $('#f_weight').val()

        let net = parseFloat(scale_reading) - parseFloat(issued_tareweight)
        $('#net').val(net.toFixed(2));
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
