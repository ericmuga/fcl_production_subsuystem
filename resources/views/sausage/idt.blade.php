@extends('layouts.sausage_master')

@section('content-header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-5">
            <h1 class="m-0"> Sausage | {{ $title }} | <small>Create & View <strong></strong> Transfers Lines </small>
            </h1>
        </div><!-- /.col -->
        <div class="col-sm-7">
            <button class="btn btn-primary " data-toggle="collapse" data-target="#toggle_collapse"><i
                    class="fas fa-plus"></i>
                Create
                New IDT</button>
        </div>
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<div id="toggle_collapse" class="collapse">
    <form id="form-save-batch" class="form-prevent-multiple-submits" action="{{ route('save_idt') }}" method="post">
        @csrf
        <div class="card-group">
            <div class="card">
                <div class="card-body" style="">
                    <div class="form-group">
                        <div class="row">
                            <label for="inputEmail3" class="col-sm-3 col-form-label">Product Name </label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="product" id="product" required>
                                    <option value="">Select Product</option>
                                    @foreach($items as $tm)
                                    <option value="{{ $tm->code }}">
                                        {{ $tm->code. ' ' .$tm->barcode.' '.$tm->description }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div><br>
                        <div class="row">
                            <label for="inputEmail3" class="col-sm-3 col-form-label">Unit Count Per Crate </label>
                            <div class="col-sm-9">
                                <input type="number" readonly class="form-control input_params crates" value="0"
                                    id="unit_crate_count" name="unit_crate_count" placeholder="">
                            </div>
                        </div>
                        <div class="row">
                            <label for="inputEmail3" class="col-sm-3 col-form-label">Item Unit Measure </label>
                            <div class="col-sm-9">
                                <input type="number" readonly class="form-control input_params" value="0"
                                    id="unit_measure" name="unit_measure" placeholder="">
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
                            <div class="row form-group">
                                <div class="col-md-6">
                                    <select class="form-control select2 locations" name="chiller_code" id="chiller_code"
                                        required>
                                        <option value="">Select chiller</option>
                                    </select>
                                </div>
                                <div class=" col-md-6">
                                    {{-- <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="for_export" name="for_export" value="3600">
                                        <label class="custom-control-label" for="for_export">For Export?</label>
                                    </div> --}}
                                    <select class="form-control select2" name="for_export" id="for_export" required>
                                        <option value="" selected disabled>Transfer Type </option>
                                        <option value="0"> Local</option>
                                        <option value="1"> Export</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div><br>
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">Total Crates</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control crates" id="total_crates" name="total_crates"
                                value="" required onkeyup="handleChange()" placeholder="">
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
                        <label for="inputEmail3" class="col-sm-3 col-form-label">Pieces in incomplete Crate</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control crates" value="0" id="incomplete_pieces"
                                name="incomplete_pieces" onClick="this.select();" placeholder="">
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
                        <label for="inputEmail3" class="col-sm-3 col-form-label">Calc Pieces | Weight(Kgs)</label>
                        <div class="col-sm-9">
                            <div class="row">
                                <div class="col-sm-6">
                                    <input type="number" class="form-control" value="0" id="pieces" name="pieces"
                                        placeholder="" readonly>
                                </div>
                                <div class="col-sm-6">
                                    <input type="number" step=".01" class="form-control" value="0" id="weight"
                                        name="weight" placeholder="" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">Description <i>(optional)</i></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" value="" id="desc" name="desc" placeholder="">
                        </div>
                    </div>
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">Batch No </label>
                        <div class="col-sm-9">
                            <div class="row">
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" value="{{ $helpers->generateIdtBatch() }}"
                                        id="batch" name="batch" required readonly>
                                </div>
                                <div class="col-sm-6">
                                    <input type="number" class="form-control" value="" id="batch_no" name="batch_no"
                                        required placeholder="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="div" style="padding-top: 5%">
                        <button type="submit" class="btn btn-primary btn-lg btn-prevent-multiple-submits"
                            onclick="return validateOnSubmit()"><i class="fa fa-paper-plane single-click"
                                aria-hidden="true"></i> Save</button>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="location_code" id="location_code" value="3535">
    </form>
    <div id="loading" class="collapse">
        <div class="row d-flex justify-content-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>
</div><br>

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
                    <table id="example1" class="table display nowrap table-striped table-bordered table-hover"
                        width="100%">
                        <thead>
                            <tr>
                                <th>IDT No</th>
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
                                <th>Std Crate Count</th>
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
                                    data-description="{{ $data->description }}"
                                    data-batch_no="{{ $data->batch_no }}"><a href="#">{{ $data->id }}</a>
                                </td>
                                <td>{{ $data->product_code }}</td>
                                <td>{{ $data->product }}</td>
                                <td>{{ $data->unit_count_per_crate }}</td>
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
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
        <!-- /.col -->
    </div>
</div>
<!-- slicing ouput data show -->

<!-- Edit Modal -->
<div id="editIdtModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-xl">
        <!--Start create user modal-->
        <form class="form-prevent-multiple-submits" id="form-edit-role" action="{{route('edit_idt_issue')}}"
            method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Idt No: <strong><input style="border:none"
                                type="text" id="item_id" name="item_id" value="" readonly></strong></h5>
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
                                                id="edit_product" placeholder="">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label for="inputEmail3" class="col-sm-3 col-form-label">Description
                                            <i>(optional)</i></label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" value="" id="desc_edit"
                                                name="desc_edit" placeholder="">
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
                                            selected="selected" required>
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
                                            name="batch_no_edit" required placeholder="">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body text-center form-group">
                                <input type="hidden" class="form-control" value="0" id="unit_measure_edit"
                                            placeholder="">
                                <div class="row">
                                    <label for="inputEmail3" class="col-sm-6 col-form-label">Calc Pieces</label>
                                    <div class="col-sm-6">
                                        <input type="number" class="form-control" value="0" id="pieces_edit"
                                            name="pieces_edit" placeholder="">
                                    </div>
                                </div><br>
                                <div class="row">
                                    <label for="inputEmail3" class="col-sm-6 col-form-label">Calc
                                        Weight(Kgs)</label>
                                    <div class="col-sm-6">
                                        <input type="number" step="0.1" class="form-control" value="0" id="weight_edit" readonly
                                            name="weight_edit" placeholder="">
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
    $(document).ready(function () {
        $('.incomplete_pieces').hide();

        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        $('#product').change(function () {
            let product_code = $(this).val()
            fetchTransferToLocations(product_code);
            loadProductDetails(product_code);
        });

        $("#pieces_edit").on("keyup change", function(e) {
            calculateWeightEdit()
        });

        $('#for_export').on("change", function () {
            // updateExportUnitCount()
            let transfer_type = $('#for_export').val()

            if (transfer_type == 1) {
                $("#unit_crate_count").prop('readonly', false);
            } else {
                $("#unit_crate_count").prop('readonly', true);
            }
        })

        $("body").on("click", "#editIdtModalShow", function (e) {
            e.preventDefault();

            let id = $(this).data('id');
            let product = $(this).data('product');
            let weight = $(this).data('total_weight');
            let pieces = $(this).data('total_pieces');
            let transfer_type = $(this).data('transfer_type');
            let batch_no = $(this).data('batch_no');
            let desc = $(this).data('description');
            let unit_measure = $(this).data('unit_measure');

            $('#item_id').val(id);
            $('#edit_product').val(product);
            $('#weight_edit').val(weight);
            $('#pieces_edit').val(pieces)
            $('#for_export_edit').val(transfer_type);
            $('#batch_no_edit').val(batch_no);
            $('#desc_edit').val(desc);
            $('#unit_measure_edit').val(unit_measure);

            $('#for_export_edit').select2('destroy').select2();

            $('#editIdtModal').modal('show');
        });


        $('.crates').on("keyup change", function () {
            validateCrates()
        })

        $("#validate").on("click", function () {
            validateUser()
        });
    });

    const defaultCrateCounts = (product_code) => {
        const list = {
            'J31015601': 45, //safari beef sausage 500gms
            'J31011301': 45, //Pork Catering Xpt-500gms
            'J31011302': 45, //Pork Catering 1Kg Xpt
        }

        return list[product_code]
    }

    const updateExportUnitCount = () => {
        let product_code = $('#product').val()
        let transfer_type = $('#for_export').val()

        const list = {
            'J31015601': 45, //safari beef sausage 500gms
            'J31011301': 45, //Pork Catering Xpt-500gms
            'J31011302': 45, //Pork Catering 1Kg Xpt
        }

        if (product_code in list && transfer_type == 1) {

            if (product_code == 'J31015601') {
                //safari beef sausage 500gms
                $('#unit_crate_count').val(40)
            }
            calculatePiecesAndWeight()
        }
    }

    const validateOnSubmit = () => {
        let status = true

        let total_crates = $("#total_crates").val();
        let full_crates = $("#full_crates").val();
        let incomplete_pieces = $('#incomplete_pieces').val();

        let pieces = $('#pieces').val();
        let weight = $('#weight').val();

        let crates_validity = $("#crates_valid").val();
        let user_validity = $("#user_valid").val();

        if (crates_validity == 0) {
            status = false
            alert("please ensure you have valid crates before submitting")

        } else if (user_validity == 0) {
            status = false
            alert("please ensure you have validated receiver before submitting")

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

    const loadProductDetails = (prod_code) => {
        $('#loading').collapse('show');

        const url = '/item/details-axios'

        const request_data = {
            product_code: prod_code
        }

        return axios.post(url, request_data)
            .then((response) => {
                $('#loading').collapse('hide');

                $('#unit_crate_count').val(response.data.unit_count_per_crate)
                $('#unit_measure').val(parseFloat(response.data.qty_per_unit_of_measure).toFixed(2))
                validateCrates()
            })
            .catch((error) => {
                console.log(error);
            })
    }

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

    const validateUser = () => {
        let username = $("#username").val()
        let password = $("#password").val()

        if (username == '' || password == '') {
            setUserMessage('succ', 'err', '', 'please enter both username & Password')
        } else {
            setUserMessage('succ', 'err', '', '')
            checkIfUserHasRights(username, password)
        }
    }

    const checkIfUserHasRights = (username, password) => {
        let status = 0

        const url = "/check-user-rights"

        const request_data = {
            username: 'FARMERSCHOICE\\' + username,
            location_code: $('#location_code').val()
        }

        return axios.post(url, request_data)
            .then((res) => {
                if (res) {
                    if (res.data == 1) {
                        // proceed to domain check
                        LdapApiRequest(username, password)
                    } else {
                        setUserMessage('succ', 'err', '', 'user does not have rights')
                        setUserValidity(0)
                    }
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

    const setUserValidity = (status) => {
        $("#user_valid").val(status);
    }

    const validateCrates = () => {
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
                calculatePiecesAndWeight()
            }
        }

    }

    const calculatePiecesAndWeight = () => {
        let full_crates = $('#full_crates').val()
        let crate_unit_count = $('#unit_crate_count').val()
        let incomplete_pieces = $('#incomplete_pieces').val()
        let unit_measure = $('#unit_measure').val()

        let pieces = (parseInt(full_crates) * parseInt(crate_unit_count)) + parseInt(incomplete_pieces)
        let weight = pieces * unit_measure

        $('#pieces').val(pieces)
        $('#weight').val(weight.toFixed(2))
    }

    const calculateWeightEdit = () => {
        let unit_measure = $('#unit_measure_edit').val()

        let pieces = $('#pieces_edit').val()
        let weight = pieces * unit_measure

        $('#weight_edit').val(weight.toFixed(2))
    }

    const setCratesValidity = (status) => {
        $("#crates_valid").val(status);
    }

    const LdapApiRequest = (user_name, pass) => {
        $('#loading').collapse('show');

        const url = "/validate-user"

        const headers = {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Credentials': true
        }

        const request_data = {
            username: 'FARMERSCHOICE\\' + user_name,
            password: pass
        }

        return axios.post(url, request_data, {
                headers: headers
            })
            .then((res) => {
                $('#loading').collapse('hide');
                if (res) {
                    const obj = JSON.parse(res.data)
                    if (obj.success == true) {
                        setUserMessage('succ', 'err', 'validated receiver', '')
                        setUserValidity(1)
                    } else {
                        setUserMessage('succ', 'err', '', 'Wrong credentials')
                        setUserValidity(0)
                    }

                } else {
                    setUserMessage('succ', 'err', '', 'No response from login Api service. Contact IT')
                }
            })
            .catch((error) => {
                console.log(error);
            })
    }

</script>
@endsection
