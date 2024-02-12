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
<div class="div">
    <button class="btn btn-primary " data-toggle="collapse" id="collapseBtn"
        data-target="#slicing_output_show"><i class="fa fa-plus"></i>
        Create
    </button>
</div>
<br>

<div id="slicing_output_show" class="collapse">
    <form id="form-save-scale3" class="form-prevent-multiple-submits"
        action="{{ route('save_movement') }}" method="post">
        @csrf
        <div class="card-group">
            <div class="card">
                <div class="card-body">
                    <h5><strong>To:</strong></h5>
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
                            <label for="exampleInputPassword1"> Move To User List</label>
                            <select class="form-control select2" name="to_user" id="to_user_select" required>
                            </select>
                        </div>
                    </div>
                    <div class="row form-group text-center">
                        <div class="col-md-12">
                            <label for="exampleInputPassword1"> Move To Dept List</label>
                            <select class="form-control select2" name="to_dept" id="to_dept_select" required>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5><strong>From:</strong></h5>
                    <div class="row form-group text-center">
                        <div class="col-md-12">
                            <label for="exampleInputPassword1"> Move From User List</label>
                            <select class="form-control select2" name="from_user" id="from_user_select" required>
                            </select>
                        </div>
                    </div>
                    <div class="row form-group text-center">
                        <div class="col-md-12">
                            <label for="exampleInputPassword1"> Move From Dept List</label>
                            <select class="form-control select2" name="from_dept" id="from_dept_select" required>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5><strong>Authentication:</strong></h5>
                    <div class="row form-group text-center">
                        <div class="col-md-12">
                            <label for="exampleInputPassword1"> Receiving Username</label>
                            <input type="text" id="receipt_user" value="" class="form-control" required>
                        </div>
                    </div>
                    <div class="row form-group text-center">
                        <div class="col-md-8">
                            <label for="exampleInputPassword1"> Receiving User Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password"
                                    placeholder="Enter your password" aria-describedby="password-toggle">
                                <div class="input-group-append">
                                    <button class="btn btn-secondary" type="button" id="password-toggle">
                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4" style="padding-top: 7%">
                            <button type="button" id="validate_user_btn" class="btn btn-info">Validate</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <span class="text-danger" id="err"></span>
                        <span class="text-success" id="succ"></span>
                    </div>
                    <input type="hidden" id="auth_val" value="0">
                    <input type="hidden" class="form-control" id="auth_user" name="auth_username" value="">
                    <div class="row form-group text-center" style="padding-top: 5%; padding-left: 40%">
                        <button type="submit" onclick="return validateOnSubmit()" id="save_btn"
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
</div><br>

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
                                <th>Fa </th>
                                <th>Description </th>
                                <th>To User</th>
                                <th>To Dept </th>
                                <th>From User</th>
                                <th>From Dept</th>
                                <th>Created By</th>
                                <th>Autheticated user</th>
                                <th>Status</th>
                                <th>Created Date </th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Fa </th>
                                <th>Description </th>
                                <th>To User</th>
                                <th>To Dept </th>
                                <th>From User</th>
                                <th>From Dept</th>
                                <th>Created By</th>
                                <th>Autheticated user</th>
                                <th>Status</th>
                                <th>Created Date </th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($entries as $e)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    @if ($e->status == 1)
                                        <td id="editIdtModalShow" data-id="{{ $e->id }}"
                                            data-desc="{{ $e->description }}"
                                            data-to_user="{{ $e->to_user }}"
                                            data-to_dept="{{ $e->to_dept }}"
                                            data-from_user="{{ $e->from_user }}"
                                            data-from_dept="{{ $e->from_dept }}"
                                            data-auth_user="{{ $e->authenticated_username }}"
                                            data-created_by="{{ $e->username }}"><a href="#">{{ $e->fa }}</a>
                                        </td>
                                    @else
                                        <td>{{ $e->fa }}</td>
                                    @endif
                                    <td>{{ $e->description }}</td>
                                    <td>{{ $e->to_user }}</td>
                                    <td>{{ $e->to_dept }}</td>
                                    <td>{{ $e->from_user }}</td>
                                    <td>{{ $e->from_dept }}</td>
                                    <td>{{ $e->username }}</td>
                                    <td>{{ $e->authenticated_username }}</td>
                                    <td>
                                        @if ($e->status == 2)
                                        <span class="badge badge-danger">cancelled</span>
                                        @else
                                         <span class="badge badge-success">unedited</span>   
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($e->created_at)->format('d/m/Y H:i') }}
                                    </td>
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

<!-- Cancel Modal -->
<div id="editIdtModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-xl">
        <!--Start create user modal-->
        <form class="form-prevent-multiple-submits" id="form-edit-role"
            action="{{ route('assets_cancel_trans') }}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Cancel Asset Movement for Movement No: <strong><input
                                style="border:none" type="text" id="edit_desc" name="edit_desc" value="" readonly></strong>
                    </h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card-group">
                        <div class="card">
                            <h5><strong>Transfer To:</strong></h5>
                            <div class="card-body" style="">
                                <div class="form-group">
                                    <div class="row mb-3">
                                        <label for="inputEmail3" class="col-sm-4 col-form-label"> user </label>
                                        <div class="col-sm-8">
                                            <input type="text" readonly class="form-control" value="" id="edit_to_user"
                                                placeholder="">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="inputEmail3" class="col-sm-4 col-form-label"> Dept </label>
                                        <div class="col-sm-8">
                                            <input type="text" readonly class="form-control" value="" id="edit_to_dept"
                                                placeholder="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <h5><strong>Transfer From:</strong></h5>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="row mb-3">
                                        <label for="inputEmail3" class="col-sm-4 col-form-label"> user </label>
                                        <div class="col-sm-8">
                                            <input type="text" readonly class="form-control" value="" id="edit_from_user"
                                                placeholder="">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="inputEmail3" class="col-sm-4 col-form-label"> Dept </label>
                                        <div class="col-sm-8">
                                            <input type="text" readonly class="form-control" value="" id="edit_from_dept"
                                                placeholder="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <h5><strong>Authenticating User:</strong></h5>
                            <div class="card-body text-center form-group">
                                <div class="row mb-3">
                                    <label for="inputEmail3" class="col-sm-4 col-form-label"> Username </label>
                                    <div class="col-sm-8">
                                        <input type="text" readonly class="form-control" value="" id="edit_auth_user"
                                            placeholder="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="item_id" name="item_id" value="">
                <div class="modal-footer">
                    <div class="form-group">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button class="btn btn-warning btn-lg btn-prevent-multiple-submits"
                            onclick="return validateOnEditSubmit()" type="submit">
                            <i class="fa fa-save"></i> Cancel Movement
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

        $('#validate_user_btn').on('click', function (e) {
            e.preventDefault()
            checkParams()
        });

        $('#collapseBtn').on('click', function (a) {
            a.preventDefault()
            fetchData()
            fetchEmployees()
            fetchLocationsData()
        });

        $('#fa_select').change(function () {
            let data = $(this).val();

            var location = data.split(':')[1];
            var emp = data.split(':')[2];

            $('#from_dept_select').val(location);
            $('#from_user_select').val(emp);

            $('#from_dept_select').select2('destroy').select2();
            $('#from_user_select').select2('destroy').select2();
        });

        $("body").on("click", "#editIdtModalShow", function (e) {
            e.preventDefault();

            let id = $(this).data('id');
            let desc = $(this).data('desc');
            let to_user = $(this).data('to_user');
            let to_dept = $(this).data('to_dept');
            let from_user = $(this).data('from_user');
            let from_dept = $(this).data('from_dept');
            let auth_user = $(this).data('auth_user');

            $('#item_id').val(id);
            $('#edit_desc').val(desc);
            $('#edit_to_user').val(to_user);
            $('#edit_to_dept').val(to_dept);
            $('#edit_from_user').val(from_user);
            $('#edit_from_dept').val(from_dept);
            $('#edit_auth_user').val(auth_user);

            $('#editIdtModal').modal('show');
        });
    });

    const setUserMessage = (field_succ, field_err, message_succ, message_err) => {
        document.getElementById(field_succ).innerHTML = message_succ
        document.getElementById(field_err).innerHTML = message_err
    }

    const checkValidUserValue = () => {
        let auth_val = $('#auth_val').val()

        // Check if the value not zero and enable it
        if (auth_val != 0) {
            $('#save_btn').removeClass('disabled') // enable the save button
        }
    }

    const checkParams = () => {
        let username = $('#receipt_user').val()
        let password = $('#password').val()

        if (username == '' || password == '') {
            setUserMessage('succ', 'err', '', 'please enter both username & Password')
        } else {
            setUserMessage('succ', 'err', '', '')
            validateUser(username, password)
        }
    }

    const setUserValidity = (status) => {
        $("#auth_val").val(status);
    }

    const setValidatedUsername = (username) => {
        $("#auth_user").val(username);
        $("#receipt_user").prop('disabled', true);
    }

    const validateUser = (username, password) => {
        const url = "/asset/check-user"

        $('#validate_user_btn').addClass('disabled')
        setUserMessage('succ', 'err', 'Validating user...', '')

        const request_data = {
            username: 'FARMERSCHOICE\\' + username,
            password: password
        }

        axios.post(url, request_data)
            .then((res) => {
                if (res) {
                    const obj = JSON.parse(res.data)
                    if (obj.success == true) {
                        setUserMessage('succ', 'err', 'validated receiver', '')
                        setUserValidity(1)
                        setValidatedUsername(request_data.username)
                        checkValidUserValue()
                    } else {
                        setUserMessage('succ', 'err', '', 'Wrong credentials')
                        setUserValidity(0)
                    }

                } else {
                    setUserMessage('succ', 'err', '', 'No response from login Api service. Contact IT')
                }

                $('#validate_user_btn').removeClass('disabled')

            })
            .catch((error) => {
                console.log(error);
                setUserMessage('succ', 'err', '', error)
                $('#validate_user_btn').removeClass('disabled')
            })
    }

    const validateOnSubmit = () => {
        $valid = true;

        let auth_val = parseInt($('#auth_val').val())

        if (auth_val != 1 ) {
            $valid = false;
            alert("Please validate receiver first.");
        }

        return $valid;
    }

    const fetchData = () => {
        $('#loading').collapse('show');
        axios.get('/asset/fetch-data')
            .then(function (response) {
                $('#loading').collapse('hide');
                let faSelect = document.getElementById('fa_select');
                // let fromDeptSelect = document.getElementById('from_dept_select');
                // let toUserSelect = document.getElementById('to_user_select');
                // let fromUserSelect = document.getElementById('from_user_select');

                // Create an object to keep track of unique values
                let uniqueValues = {};

                // Clear existing options and add an empty option
                faSelect.innerHTML = '<option value="">Select an option</option>';
                // toDeptSelect.innerHTML = '<option value="">Select an option</option>';
                // fromDeptSelect.innerHTML = '<option value="">Select an option</option>';
                // toUserSelect.innerHTML = '<option value="">Select an option</option>';
                // fromUserSelect.innerHTML = '<option value="">Select an option</option>';

                // Append options from Axios response
                response.data.forEach(function (item) {
                    appendOption(faSelect, item.No_ + ':' + item.Location_code + ':' + item
                        .Responsible_employee + ':' + item.Description, item.No_ + ' ' + item
                        .Description);

                    // Check if the value is unique
                    if (!uniqueValues.hasOwnProperty(item.Location_code)) {
                        // appendOption(toDeptSelect, item.Location_code, item.LocationName);
                        // uniqueValues[item.Location_code] = true;

                        // appendOption(fromDeptSelect, item.Location_code, item.LocationName);
                        // uniqueValues[item.Location_code] = true;
                    }
                    if (!uniqueValues.hasOwnProperty(item.Responsible_employee)) {
                        // appendOption(toUserSelect, item.Responsible_employee, item
                        //     .Responsible_employee);
                        // uniqueValues[item.Responsible_employee] = true;

                        // appendOption(fromUserSelect, item.Responsible_employee, item
                        //     .Responsible_employee);
                        // uniqueValues[item.Responsible_employee] = true;
                    }
                });
            })
            .catch(function (error) {
                console.error(error);
            });
    }
    const fetchLocationsData = () => {
        $('#loading').collapse('show');
        axios.get('/asset/fetch-depts')
            .then(function (response) {
                $('#loading').collapse('hide');
                // console.log(response)
                let toDeptSelect = document.getElementById('to_dept_select');
                let fromDeptSelect = document.getElementById('from_dept_select');

                // Clear existing options and add an empty option
                toDeptSelect.innerHTML = '<option value="">Select an option</option>';
                fromDeptSelect.innerHTML = '<option value="">Select an option</option>';

                // Create an object to keep track of unique values
                let uniqueValues = {};

                // Append options from Axios response
                response.data.forEach(function (item) {     
                    if (!uniqueValues.hasOwnProperty(item.Code)) {               
                        appendOption(toDeptSelect, item.Code , item.Name);
                        appendOption(fromDeptSelect, item.Code , item.Name);
                    }
                });
            })
            .catch(function (error) {
                console.error(error);
            });
    }

    const fetchEmployees = () => {
        $('#loading').collapse('show');
        axios.get('/asset/fetch-employees')
            .then(function (response) {
                $('#loading').collapse('hide');
                let toUserSelect = document.getElementById('to_user_select');
                let fromUserSelect = document.getElementById('from_user_select');

                // Clear existing options and add an empty option
                toUserSelect.innerHTML = '<option value="">Select an option</option>';
                fromUserSelect.innerHTML = '<option value="">Select an option</option>';

                // Create an object to keep track of unique values
                let uniqueValues = {};

                // Append options from Axios response
                response.data.forEach(function (item) {
                    if (!uniqueValues.hasOwnProperty(item.No_)) {
                        appendOption(toUserSelect, item.No_, item
                            .No_);
                        appendOption(fromUserSelect, item.No_, item
                            .No_);
                    }
                });
            })
            .catch(function (error) {
                console.error(error);
            });
    }

    const appendOption = (selectElement, value, text) => {
        var option = document.createElement('option');
        option.value = value;
        option.text = text;
        selectElement.appendChild(option);
    }

</script>
@endsection
