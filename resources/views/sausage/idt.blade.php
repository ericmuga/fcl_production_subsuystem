@extends('layouts.sausage_master')

@section('content-header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-5">
            <h1 class="m-0"> {{ $title }} |<small>Create & View <strong></strong> Transfers Lines </small></h1>
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
    <form id="form-save-batch" class="form-prevent-multiple-submits" action="{{ route('save_idt') }}"
        method="post">
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
                                        {{ $tm->barcode.' '.$tm->description }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div><br>
                        <div class="row">
                            <label for="inputEmail3" class="col-sm-3 col-form-label">Unit Count Per Crate </label>
                            <div class="col-sm-9">
                                <input type="number" readonly class="form-control" value="0" id="unit_crate_count"
                                    name="status" placeholder="" name="unit_crate_count">
                            </div>
                        </div>
                        <div class="row">
                            <label for="inputEmail3" class="col-sm-3 col-form-label">Item Unit Measure </label>
                            <div class="col-sm-9">
                                <input type="number" readonly class="form-control" value="0" id="unit_measure"
                                    name="status" placeholder="" name="unit_measure">
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
                            <select class="form-control select2 locations" name="location_to" id="location_to" required>
                                <option value="">Select chiller</option>
                            </select>
                        </div>
                    </div><br>
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">Total Crates</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control crates" id="total_crates" name="total_crates" value=""
                                required onkeyup="handleChange()" placeholder="">
                        </div>
                    </div><br>
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">No. of full Crates</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control crates" value="" id="full_crates" name="full_crates"
                                required onkeyup="handleChange()" placeholder="">
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
                        <label for="inputEmail3" class="col-sm-3 col-form-label">Calc Pieces</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" value="0" id="pieces" name="status"
                                placeholder="">
                        </div>
                    </div><br>
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">Calc Weight(Kgs)</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" value="0" id="weight" name="status"
                                placeholder="" >
                        </div>
                    </div>
                    <hr>
                    <div class="row form-group">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">Dispatch Receiver</label>
                        <div class="col-sm-9">
                            <div class="row form-group">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" value="" id="username" name="username"
                                        placeholder="username">
                                </div>
                                <div class="col-md-4">
                                    <input type="password" class="form-control" value="" id="password" name="password"
                                        placeholder="Password">
                                </div>
                                <div class="col-md-4">
                                    <button type="button" id="validate" class="btn btn-success"><i
                                            class="fa fa-paper-plane" aria-hidden="true"></i>
                                        Validate</button>
                                </div>
                                <span class="text-danger" id="err"></span>
                                <span class="text-success" id="succ"></span>
                            </div>
                            <input type="hidden" name="user_valid" id="user_valid" value="0">
                            <input type="hidden" name="location_code" id="location_code" value="3535">
                        </div>
                    </div>
                    <div class="div" style="padding-top: ">
                        <button type="submit" class="btn btn-primary btn-lg btn-prevent-multiple-submits"
                            onclick="return validateOnSubmit()"><i class="fa fa-paper-plane single-click"
                                aria-hidden="true"></i> Post</button>
                    </div>
                </div>
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
                    <table id="example1" class="table table-striped table-bordered table-hover" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Batch No</th>
                                <th>Template No</th>
                                <th>Template</th>
                                <th>Status</th>
                                <th>Output Product</th>
                                <th>Output Quantity</th>
                                @if ($filter == 'open' || $filter == '')
                                <th>created By</th>

                                @elseif ($filter == 'closed')
                                <th>closed By</th>
                                @elseif ($filter == 'posted')
                                <th>posted By</th>
                                @endif

                                <th>Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Batch No</th>
                                <th>Template No</th>
                                <th>Template</th>
                                <th>Status</th>
                                <th>Output Product</th>
                                <th>Output Quantity</th>
                                @if ($filter == 'open' || $filter == '')
                                <th>created By</th>

                                @elseif ($filter == 'closed')
                                <th>closed By</th>
                                @elseif ($filter == 'posted')
                                <th>posted By</th>
                                @endif
                                <th>Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            {{-- @foreach($transfer_lines as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                            <td><a href="{{ route('production_lines', $data->batch_no) }}">{{ $data->batch_no }}</a>
                            </td>
                            <td>{{ $data->template_no }}</td>
                            <td>{{ $data->template_name }}</td>

                            @if ($data->status == 'open')
                            <td><span class="badge badge-success">Open</span></td>
                            @elseif ($data->status == 'closed')
                            <td><span class="badge badge-warning">Closed</span></td>
                            @else
                            <td><span class="badge badge-danger">Posted</span></td>
                            @endif

                            <td>{{ $data->template_output }}</td>
                            <td>{{ $data->output_quantity }}</td>
                            <td>{{ $data->username }}</td>
                            <td>{{ $helpers->amPmDate($data->created_at) }}</td>
                            </tr>
                            @endforeach --}}
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

        $('#product').change(function () {
            let product_code = $(this).val()
            fetchTransferToLocations(product_code);
            loadProductDetails(product_code);
        });

        $('.crates').keyup(function () {
            validateCrates()
        })

        $("#validate").on("click", function () {
            validateUser()
        });
    });

    const validateOnSubmit = () => {
        let status = true

        let total_crates = $("#total_crates").val();
        let full_crates = $("#full_crates").val();
        let incomplete_pieces = $('#incomplete_pieces').val();

        let crates_validity = $("#crates_valid").val();
        let user_validity = $("#user_valid").val();

        if (crates_validity == 0) {
            status = false
            alert("please ensure you have valid crates before submitting")

        } else if(user_validity == 0){
            status = false
            alert("please ensure you have validated user before submitting")

        } else if (parseInt(full_crates) < parseInt(total_crates) && (parseInt(incomplete_pieces) < 1) ){
            status = false
            alert("please enter incomplete pieces")
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
                            .text(value.description));
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
        let total_crates = $("#total_crates").val();
        let full_crates = $("#full_crates").val();

        let diff = parseInt(total_crates) - parseInt(full_crates)

        if (diff < 0 || diff > 1) {
            setCratesValidity(0)
            setCratesValidityMessage('succ1', 'err1', '', 'invalid crates')
            $('#pieces').val(0)
            $('#weight').val(0)
            // alert(
            //     "Please ensure there can only be maximum one incomplete crate & the full crates cannot exceed total crates")
                
        } else {
            setCratesValidity(1)
            setCratesValidityMessage('succ1', 'err1', '', '')
            calculatePiecesAndWeight(full_crates)

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
                        setUserMessage('succ', 'err', 'validated user', '')
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
