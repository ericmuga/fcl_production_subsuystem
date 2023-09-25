@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"> {{ $title }} |<small> Asset Movement </small></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<form id="form-save-scale3" class="form-prevent-multiple-submits"
    action="{{ route('beef_slicing_save') }}" method="post">
    @csrf
    <div class="card-group">
        <div class="card">
            <div class="card-body">
                <h5>To: </h5>
                <div class="form-group text-center">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="exampleInputPassword1"> FA List</label>
                            <select class="form-control select2" name="fa" id="fa_select" required>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row form-group text-center">
                    <div class="col-md-12">
                        <div class="form-group" id="product_type_select">
                            <label for="exampleInputPassword1"> Move To Dept List</label>
                            <select class="form-control select2" name="to_dept" id="to_dept" required>
                                @foreach($data as $d)
                                    <option value="{{ trim($d->Location_code) }}" selected="selected">
                                        {{ ucwords($d->LocationName) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row form-group text-center">
                    <div class="col-md-12">
                        <div class="form-group" id="product_type_select">
                            <label for="exampleInputPassword1"> Move To User List</label>
                            <select class="form-control select2" name="to_user" id="to_user" required>
                                @foreach($data as $d)
                                    <option value="{{ trim($d->Responsible_employee) }}" selected="selected">
                                        {{ ucwords($d->Responsible_employee) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5>From: </h5>
                <div class="row form-group text-center">
                    <div class="col-md-12">
                        <label for="exampleInputPassword1"> Move From User List</label>
                        <select class="form-control select2" name="from_user" id="from_user" required>
                            @foreach($data as $d)
                                <option value="{{ trim($d->Responsible_employee) }}" selected="selected">
                                    {{ ucwords($d->Responsible_employee) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row form-group text-center">
                    <div class="col-md-12">
                        <label for="exampleInputPassword1"> Move From Dept List</label>
                        <select class="form-control select2" name="from_dept" id="from_dept" required>
                            @foreach($data as $d)
                                <option value="{{ trim($d->Location_code) }}" selected="selected">
                                    {{ ucwords($d->LocationName) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5>Authentication: </h5>
                <div class="row form-group text-center">
                    <div class="col-md-12">
                        <label for="exampleInputPassword1"> Receiving Username</label>
                        <input type="text" id="receipt_user" value="" class="form-control" required>
                        <input type="" id="auth_val" value="0">
                    </div>
                </div>
                <div class="row form-group text-center">
                    <div class="col-md-12">
                        <label for="exampleInputPassword1"> Receiving User Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Enter your password" aria-describedby="password-toggle">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="password-toggle">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
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
    <div id="loading" class="collapse">
        <div class="row d-flex justify-content-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>
</form>
<br>

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
                    <h3 class="card-title"> Movements today | <span id="subtext-h1-title"><small> entries
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
                                    <th>Production Process</th>
                                    <th>Total Crates</th>
                                    <th>Black Crates</th>
                                    <th>Scale Weight(kgs)</th>
                                    <th>Total Tare</th>
                                    <th>Net Weight(kgs)</th>
                                    <th>Total Pieces</th>
                                    <th>Prod Date</th>
                                    <th>Created Date </th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Code </th>
                                    <th>product </th>
                                    <th>Product Type</th>
                                    <th>Production Process</th>
                                    <th>Total Crates</th>
                                    <th>Black Crates</th>
                                    <th>Scale Weight(kgs)</th>
                                    <th>Total Tare</th>
                                    <th>Net Weight(kgs)</th>
                                    <th>Total Pieces</th>
                                    <th>Prod Date</th>
                                    <th>Created Date </th>
                                </tr>
                            </tfoot>
                            <tbody>
                                {{-- @foreach($entries as $data)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->item_code }}</td>
                                <td>{{ $data->description }}</td>
                                @if($data->product_type == 1)
                                    <td>Main</td>
                                @elseif($data->product_type == 2)
                                    <td>By-Product</td>
                                @else
                                    <td>Intake</td>
                                @endif
                                <td>{{ $data->process }}</td>
                                <td>{{ $data->no_of_crates }}</td>
                                <td>{{ $data->black_crates }}</td>
                                <td>{{ $data->scale_reading }}</td>
                                <td>{{ number_format(($data->no_of_crates * 1.8) + ($data->black_crates * 0.2), 2) }}
                                </td>
                                <td>{{ number_format($data->net_weight, 2) }}</td>
                                <td>{{ $data->no_of_pieces }}</td>
                                <td>{{ \Carbon\Carbon::parse($data->production_date)->format('d/m/Y') }}
                                </td>
                                <td>{{ \Carbon\Carbon::parse($data->created_at)->format('d/m/Y H:i') }}
                                </td>
                                </tr>
                                @endforeach--}}
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

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        fetchData()
        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        let isMouseDown = false;
        let passwordInput = $("#password");
        let passwordToggleBtn = $("#password-toggle");

        passwordToggleBtn.mousedown(function () {
            // Change the input type to "text" when the button is pressed
            passwordInput.attr("type", "text");
            isMouseDown = true;
        });

        $(document).mouseup(function () {
            if (isMouseDown) {
                // Change the input type back to "password" when the mouse is released
                passwordInput.attr("type", "password");
                isMouseDown = false;
            }
        });
    });

    function validateOnSubmit() {
        $valid = true;

        var net = $('#net').val();
        var product_type = $('#product_type').val();
        var no_of_pieces = $('#no_of_pieces').val();
        var process = $('#production_process').val();
        var process_substring = process.substr(0, process.indexOf(' '));

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

    const fetchData = () => {
        axios.get('/asset/fetch-data')
            .then(function (response) {
                var dataSelect = document.getElementById('fa_select');
                dataSelect.innerHTML = ''; // Clear existing options

                // Append options from Axios response
                response.data.forEach(function (item) {
                    var option = document.createElement('option');
                    option.value = item.No_; // Set the value you want to submit
                    option.text = item.Description; // Set the text displayed in the select
                    dataSelect.appendChild(option);
                });
            })
            .catch(function (error) {
                console.error(error);
            });
    }

</script>
@endsection
