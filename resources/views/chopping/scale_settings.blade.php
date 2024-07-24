@extends('layouts.spices_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            {{-- <h1 class="m-0"> {{ $title }}<small></small></h1> --}}
            <h1 class="card-title"> Scale configs | <span id="subtext-h1-title"><small> view and edit scale
                        configs</small> </span></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<div class="row">
    <div class="col-md-8" style="margin: 0 auto; float: none;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Showing all Entries</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Scale Name</th>
                                <th>ComPort</th>
                                @if($filter == 'chopping')
                                    <th>Ip Address</th>
                                @endif
                                {{-- <th>BaudRate</th> --}}
                                <th>Tareweight</th>
                                <Th>Date Created</Th>
                                <th style="width: 30px">Config</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Scale Name</th>
                                <th>ComPort</th>
                                @if($filter == 'chopping')
                                    <th>Ip Address</th>
                                @endif
                                {{-- <th>BaudRate</th> --}}
                                <th>Tareweight</th>
                                <Th>Date Created</Th>
                                <th style="width: 30px">Config</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($scale_settings as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->scale }}</td>
                                    <td>{{ $data->comport }}</td>
                                    @if($filter == 'chopping')
                                        <td>{{ $data->ip_address }}</td>
                                    @endif
                                    {{-- <td>{{ $data->baudrate }}</td> --}}
                                    <td>{{ number_format($data->tareweight, 2) }}</td>
                                    <td>{{ $helpers->dateToHumanFormat($data->created_at) }}</td>
                                    <td>
                                        <button type="button" data-id="{{ $data->id }}"
                                            data-item="{{ $data->scale }}" data-comport="{{ $data->comport }}"
                                            data-ip_address="{{ $data->ip_address }}"
                                            data-tareweight="{{ number_format($data->tareweight, 2) }}"
                                            class="btn btn-primary btn-sm " id="editScaleModalShow"><i
                                                class="nav-icon fas fa-edit"></i>
                                            Edit</button>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
    </div>
</div>

<!-- Start Edit Scale Modal -->
<div id="editScaleModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <form id="form-edit-scale" action="{{ route('butchery_update_scale_settings') }}"
            method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Scale: <code><strong><input style="border:none"
                                    type="text" id="item_name" name="item_name" value="" readonly></strong></code></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="baud">ComPort:</label>
                                <select class="form-control" name="edit_comport" id="edit_comport" required>

                                </select>
                            </div>
                            <div class="col-md-4" style="padding-top: 6.5%">
                                <button id="refreshButton" class="btn btn-outline-info btn-sm form-control" type="button" onclick="getComportListv2()">
                                    <strong>Refresh Comports</strong>
                                </button>
                            </div>
                            <div class="col-md-2" style="padding-top: 6.5%">
                                <button class="btn btn-outline-success btn-sm form-control"
                                    id="comports_success">success</button>
                                <button class="btn btn-outline-danger btn-sm form-control"
                                    id="comports_error">error!</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group error">

                    </div>
                    @if($filter == 'chopping')
                        <div class="form-group">
                            <label for="baud">Scale Host Ip Address:</label>
                            <input type="text" class="form-control" id="edit_ip_address" name="edit_ip_address" value=""
                                placeholder="eg. 100.100.3.47" required>
                        </div>
                        <input type="hidden" id="filter" name="filter" value="{{ $filter }}">
                    @endif
                    <div class="form-group">
                        <label for="baud">Tareweight:</label>
                        <input type="number" class="form-control" id="edit_tareweight" step="0.01" value=""
                            name="edit_tareweight" placeholder="" required>
                    </div>
                    <input type="hidden" name="item_id" id="item_id" value="">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa fa-save"></i> Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- End Edit Scale modal-->

@endsection


@section('scripts')
<script>
    $(document).ready(function () {
        $('#comports_success').hide();
        $('#comports_error').hide();
        // edit
        $("body").on("click", "#editScaleModalShow", function (a) {
            a.preventDefault();

            let scale = $(this).data('item');
            let comport = $(this).data('comport');
            let tareweight = $(this).data('tareweight');
            let ipAddress = $(this).data('ip_address');
            let id = $(this).data('id');

            $('#item_name').val(scale);
            $('#edit_comport').val(comport);
            $('#edit_tareweight').val(tareweight);
            $('#edit_ip_address').val(ipAddress);
            $('#item_id').val(id);


            $('#editScaleModal').modal('show');
        });

    });

    // Function to display error messages
    function displayError(message) {
        const errorDiv = document.querySelector('.form-group.error');
        errorDiv.textContent = message;
        errorDiv.style.color = 'red';
    }

    function getComportList() {
        $.ajax({
            type: "GET",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                    .attr('content')
            },
            url: "{{ url('butchery/comport-list-api-service') }}",
            dataType: 'JSON',
            success: function (data) {
                console.log(data);

                var obj = JSON.parse(data);
                console.log(obj.success);

                if (obj.success == true) {

                    var formOptions = "";
                    $.each(obj.response, function (v) {
                        var val = obj.response[v];
                        formOptions += "<option value='" + val + "'>" + val +
                            "</option>";
                    });
                    $('#edit_comport').html(formOptions);
                    $('#comports_success').show();
                    $('#comports_error').hide();

                } else {
                    $('#comports_success').hide();
                    $('#comports_error').show();
                    alert('No response from Api service');
                }

            },
            error: function (data) {
                var errors = data.responseJSON;
                console.log(errors);
                $('#comports_success').hide();
                $('#comports_error').show();
                alert('error occured when sending request');
            }
        });
    }

    const isValidIpAddress = (ipAddress) => {
        const ipPattern =
            /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
        return ipPattern.test(ipAddress);
    }

    const isValidEndpoint = (endpoint) => {
        return endpoint && endpoint.trim() !== '';
    }

    const getComportListv2 = () => {
        const button = document.getElementById('refreshButton');
        const comportListEndpoint = "{{ config('app.comport_list_endpoint') }}";
        let ipAddress = document.getElementById('edit_ip_address').value;

        if (!isValidIpAddress(ipAddress)) {
            displayError('Invalid IP address.');
            return;
        }

        if (!isValidEndpoint(comportListEndpoint)) {
            displayError('Invalid endpoint.');
            return;
        }

        const fullUrl = 'http://' + ipAddress + comportListEndpoint;

        // Change button label to 'loading...' and disable it
        button.innerHTML = '<strong>Loading...</strong>';
        button.disabled = true;

        axios.get(fullUrl)
            .then(function (response) {
                console.log(response.data); // Log the response data to the console
                if (response.data.success) {
                    // Clear any previous error messages
                    displayError('');

                    $('#comports_success').show();
                    $('#comports_error').hide();
                    const comports = response.data.response;
                    const selectElement = document.getElementById('edit_comport');

                    // Clear any existing options
                    selectElement.innerHTML = '';

                    // Add new options to the select element
                    comports.forEach(function (comport) {
                        const option = document.createElement('option');
                        option.value = comport;
                        option.textContent = comport;
                        selectElement.appendChild(option);
                    });
                } else {
                    displayError('API call was not successful.');

                    $('#comports_success').hide();
                    $('#comports_error').show();
                    console.error('API call was not successful.');
                }
            })
            .catch(function (error) {
                displayError('There was an error making the request: ' + error.message);
            })
            .finally(function () {
                // Reset button to original state
                button.innerHTML = '<strong>Refresh Comports</strong>';
                button.disabled = false;
            });
    }

</script>
@endsection
