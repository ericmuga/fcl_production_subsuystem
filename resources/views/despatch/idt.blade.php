@extends('layouts.despatch_master')

@section('content')
<div class="modal fade" id="despatchReceiveModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form id="form-save-batch" class="form-prevent-multiple-submits" action="{{ route('receive_idt') }}"
            method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Receive Dispatch Transfer</h5>
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
                                            <input type="text" readonly class="form-control" value=""
                                                id="item" placeholder=""
                                                name="item" readonly>
                                            <input type="hidden" name="product" id="product" value="">
                                            <input type="hidden" name="item_id" id="item_id" value="">
                                        </div>
                                    </div><br>
                                    <div class="row">
                                        <label for="inputEmail3" class="col-sm-3 col-form-label">Unit Count Per Crate
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="number" readonly class="form-control input_params" value="0"
                                                id="unit_crate_count" name="unit_crate_count" placeholder=""
                                                name="unit_crate_count">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label for="inputEmail3" class="col-sm-3 col-form-label">Item Unit Measure
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="number" readonly class="form-control input_params" value="0"
                                                id="unit_measure" name="unit_measure" placeholder=""
                                                name="unit_measure">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body form-group">
                                <div class="row">
                                    <label for="inputEmail3" class="col-sm-3 col-form-label">Transfer To </label>
                                    <div class="col-sm-9">
                                        <select class="form-control select2 locations" name="chiller_code"
                                            id="chiller_code" required>
                                            <option value="">Select chiller</option>
                                        </select>
                                    </div>
                                </div><br>
                                <div class="row">
                                    <label for="inputEmail3" class="col-sm-3 col-form-label">Total Crates</label>
                                    <div class="col-sm-9">
                                        <input type="number" class="form-control crates" id="total_crates"
                                            name="total_crates" value="" required onkeyup="handleChange()"
                                            placeholder="">
                                    </div>
                                </div><br>
                                <div class="row">
                                    <label for="inputEmail3" class="col-sm-3 col-form-label">No. of full Crates</label>
                                    <div class="col-sm-9">
                                        <input type="number" class="form-control crates" value="" id="full_crates"
                                            name="full_crates" required onkeyup="handleChange()" placeholder="">
                                    </div>
                                </div><br>
                                <div class="row incomplete_pieces">
                                    <label for="inputEmail3" class="col-sm-3 col-form-label">Pieces in incomplete
                                        Crate</label>
                                    <div class="col-sm-9">
                                        <input type="number" class="form-control crates" value="0"
                                            id="incomplete_pieces" name="incomplete_pieces" onClick="this.select();"
                                            placeholder="">
                                    </div>
                                </div>
                                <span class="text-danger" id="err1"></span>
                                <span class="text-success" id="succ1"></span>
                                <input type="hidden" name="crates_valid" id="crates_valid" value="0">
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body text-center form-group">
                                <div class="row">
                                    <label for="inputEmail3" class="col-sm-3 col-form-label">Calc Pieces</label>
                                    <div class="col-sm-9">
                                        <input type="number" class="form-control" value="0" id="pieces" name="pieces"
                                            placeholder="">
                                    </div>
                                </div><br>
                                <div class="row">
                                    <label for="inputEmail3" class="col-sm-3 col-form-label">Calc Weight(Kgs)</label>
                                    <div class="col-sm-9">
                                        <input type="number" step=".01" class="form-control" value="0" id="weight"
                                            name="weight" placeholder="">
                                    </div>
                                </div>
                                {{-- <div class="div" style="padding-top: ">
                                    <button type="submit" class="btn btn-primary btn-lg btn-prevent-multiple-submits"
                                        onclick="return validateOnSubmit()"><i class="fa fa-paper-plane single-click"
                                            aria-hidden="true"></i> Save</button>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="location_code" id="location_code" value="3535">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-lg btn-prevent-multiple-submits"
                        onclick="return validateOnSubmit()"><i class="fa fa-paper-plane single-click"
                            aria-hidden="true"></i> Save</button>
                </div>
            </div>
        </form>
        <div id="loading" class="collapse">
            <div class="row d-flex justify-content-center">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"> Transfer Lines Entries | <span id="subtext-h1-title"><small> showing all
                            <strong></strong> entries
                            ordered by
                            latest</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example1" class="table table-striped table-bordered table-hover" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Code</th>
                                <th>Product</th>
                                <th>Std Crate Count</th>
                                <th>Std Unit Measure</th>
                                <th>Location </th>
                                <th>Chiller</th>
                                <th>Total Crates</th>
                                <th>Full Crates</th>
                                <th>Incomplete Crate Pieces</th>
                                <th>Total Pieces</th>
                                <th>Total Weight</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Product Code</th>
                                <th>Product</th>
                                <th>Std Crate Count</th>
                                <th>Std Unit Measure</th>
                                <th>Location </th>
                                <th>Chiller</th>
                                <th>Total Crates</th>
                                <th>Full Crates</th>
                                <th>Incomplete Crate Pieces</th>
                                <th>Total Pieces</th>
                                <th>Total Weight</th>
                                <th>Status</th>
                                <th>Issued By</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($transfer_lines as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->product_code }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ $data->unit_count_per_crate }}</td>
                                <td>{{ number_format($data->qty_per_unit_of_measure, 2) }}</td>
                                <td>{{ $data->location_code }}</td>
                                <td>{{ $data->chiller_code }}</td>
                                <td>{{ number_format($data->receiver_total_crates, 2) }}</td>
                                <td>{{ number_format($data->receiver_full_crates, 2) }}</td>
                                <td>{{ number_format($data->receiver_incomplete_crate_pieces,2) }}</td>
                                <td>{{ number_format($data->receiver_total_pieces,2) }}</td>
                                <td>{{ number_format($data->receiver_total_weight,2) }}</td>
                                @if ($data->received_by == null)
                                <td><span class="badge badge-secondary">pending</span></td>
                                @elseif ($data->received_by != null)
                                <td><span class="badge badge-success">received</span></td>
                                @endif
                                <td>{{ $data->username }}</td>
                                <td>{{ $helpers->amPmDate($data->created_at) }}</td>
                                @if ($data->received_by == null)
                                <td><button type="button" data-id="{{$data->id}}" data-product="{{ $data->product_code }}" data-unit_count="{{ $data->unit_count_per_crate }}" data-unit_measure="{{ number_format($data->qty_per_unit_of_measure, 2) }}" data-item="{{ $data->description }}" class="btn btn-warning btn-xs"
                                        title="Receive transfer" id="despatchReceiveModalShow"><i
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
        $('.incomplete_pieces').hide();

        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        $('#despatchReceiveModal').on('shown.bs.modal', function (e) {
            e.preventDefault()
            let product_code = $('#product').val()
            fetchTransferToLocations(product_code)
        })

        $('.crates').keyup(function () {
            validateCrates()
        })

        $("body").on("click", "#despatchReceiveModalShow", function (e) {
            e.preventDefault();

            let id = $(this).data('id');
            let code = $(this).data('product');
            let item = $(this).data('item');
            let unit_count = $(this).data('unit_count');
            let unit_measure = $(this).data('unit_measure');

            $('#item_id').val(id);
            $('#product').val(code);//item_code
            $('#item').val(item);//item
            $('#unit_crate_count').val(unit_count);
            $('#unit_measure').val(unit_measure);

            $('#despatchReceiveModal').modal('show');
        });
    });

    const validateOnSubmit = () => {
        let status = true

        let total_crates = $("#total_crates").val();
        let full_crates = $("#full_crates").val();
        let incomplete_pieces = $('#incomplete_pieces').val();

        let pieces = $('#pieces').val();
        let weight = $('#weight').val();

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
        }

        return status
    }

    const handleChange = () => {
        let total_crates = $("#total_crates").val();
        let full_crates = $("#full_crates").val();

        if (total_crates != '' && full_crates != '') {
            if (total_crates > full_crates) {
                $('.incomplete_pieces').show();
            } else {
                $('.incomplete_pieces').hide();
                $('#incomplete_pieces').val(0)
            }
        }
    }

    const fetchTransferToLocations = (prod_code) => {
        $('#loading').collapse('show');

        const url = '/fetch-transferToLocations-axios'

        const request_data = {
            product_code: prod_code
        }

        return axios.post(url, request_data)
            .then((res) => {
                if (res) {
                    $('#loading').collapse('hide');
                    //empty the select list first
                    $(".locations").empty();

                    $.each(res.data, function (key, value) {
                        $(".locations").append($("<option></option>").attr("value", value
                                .chiller_code)
                            .text(value.description));
                    });
                }
            })
            .catch((error) => {
                console.log(error);
            })
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
        $('#weight').val(weight.toFixed(2))
    }

    const setCratesValidity = (status) => {
        $("#crates_valid").val(status);
    }

</script>
@endsection