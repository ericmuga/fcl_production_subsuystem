@extends('layouts.butchery_master')

@section('content-header')
<div class="container mb-2">
    <h1> {{ $title }} |<small> Beef/Lamb Products Receiving </small></h1>
</div>

@endsection

@section('content')

@section('content')
<div class="modal fade" id="receiveModal" tabindex="-1" role="dialog"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form id="form-update-receiving" class=" modal-content form-prevent-multiple-submits" action="{{ route('update_idt_receiving') }}" onsubmit="updateIdtReceiving(event)"
            method="post">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Beef/Lamb Products Receiving </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body p-4 row text-center">
                <div class="col-md-6">  
                    <div class="form-group">
                        <label for="vehicle">Vehicle No</label>
                        <select class="form-control select2" name="vehicle" id="vehicle" required>
                            <option value="">Select Vehicle</option>
                            <option value="KAQ 714R">KAQ 714R</option>
                            <option value="KAS 004G">KAS 004G</option>
                        </select>
                        <input type="hidden" id="session_vehicle" name="session_vehicle" value="{{ old('vehicle') }}">
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-form-label">Production Date (dd/mm/yyyy)</label>
                        <div class="input-group date" id="productiondate" data-target-input="nearest">
                            <input type="text" class="form-control datetimepicker-input" id="prod_date"
                                name="prod_date" required data-target="#productiondate" />
                            <div class="input-group-append" data-target="#productiondate"
                                data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="no_of_pieces">No. of pieces(Optional) </label>
                        <input type="number" class="form-control" value="" id="no_of_pieces" name="no_of_pieces" min="0"
                            >
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="reading">Reading</label>
                        <input type="number" step="0.01" class="form-control" id="reading" name="reading" value="0.00"
                            oninput="getNet()" placeholder="" readonly>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="manual_weight" name="manual_weight">
                        <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                    </div>
                    <input type="hidden" id="old_manual" value="{{ old('manual_weight') }}">
                    <div class="row form-group">
                        <div class="crates col-md-6">
                            <label for="total_crates">Total Crates</label>
                            <input type="number" class="form-control" id="total_crates" value="0" min="0" name="total_crates"
                                placeholder="" required>
                        </div>
                        <div class="col-md-6">
                            <label for="tareweight">Net Weight</label>
                            <input type="number" class="form-control" id="tareweight" name="tareweight" value="0.0" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="net">Net</label>
                        <input type="number" class="form-control" id="net" name="net" placeholder="0.00" step=".01" placeholder=""
                            readonly>
                    </div>
                    <div class="form-group">
                        <button
                            type="button"
                            onclick="getScaleReading()"
                            id="weigh"
                            value=""
                            class="btn btn-primary btn-lg"
                        >
                            <i class="fas fa-balance-scale"></i> Weigh
                        </button>
                        <p class="mt-2"><small>
                            Reading from <input type="text" id="comport_value" value="{{ $configs[0]->comport?? '' }}"
                                style="border:none" disabled>
                        </small></p>
                    </div>
                </div>
                <input type="hidden" name="from_location" id="from_location" value="">
                <input type="hidden" name="product_code" id="product_code" value="">
                <input type="hidden" name="description" id="description" value="">
                <input type="hidden" name="transfer_id" id="transfer_id" value="">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submit-btn">
                    <i class="fa fa-paper-plane single-click" aria-hidden="true"></i> Save
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <div id="loading" class="collapse">
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

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            Transfer Lines Entries | <small id="subtext-h1-title">showing all entries ordered by latest</small>
        </h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
        <div class="table-responsive">
            <table
                id="example1"
                class="table display nowrap table-striped table-bordered table-hover"
                width="100%"
            >
                <thead>
                    <tr>
                        <th>IDT No</th>
                        <th>Product Code</th>
                        <th>Product Description</th>
                        <th>Location </th>
                        <th>Status</th>
                        <th>Batch No</th>
                        <th>Issue Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>IDT No</th>
                        <th>Product Code</th>
                        <th>Product Description</th>
                        <th>Location </th>
                        <th>Status</th>
                        <th>Batch No</th>
                        <th>Issue Date</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach($entries as $data)
                    <tr>
                        <td>{{ $data->id }}</td>
                        <td>{{ $data->product_code }}</td>
                        <td>{{ $data->description }}</td>
                        <td>{{ $data->location_code }}</td>
                        @if ($data->received_by == null)
                        <td><span class="badge badge-secondary">pending</span></td>
                        @elseif ($data->received_by != null)
                        <td><span class="badge badge-success">received</span></td>
                        @endif
                        <td>{{ $data->batch_no }}</td>
                        <td>{{ $helpers->amPmDate($data->created_at) }}</td>
                        @if ($data->received_by == null)
                        <td>
                            <button
                                type="button"
                                data-transferid="{{$data->id}}"
                                data-product="{{ $data->product_code }}"
                                data-description="{{ $data->description }}"
                                data-fromlocation="{{ $data->transfer_from }}"
                                data-totalweight="{{ $data->total_weight }}"
                                data-toggle="modal"
                                data-target="#receiveModal"
                                class="btn btn-warning btn-xs"
                                title="Receive transfer">
                                <i class="fa fa-check"></i>
                            </button>
                        </td>
                        @else
                        <td><span class="badge badge-secondary">no action</span></td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!-- /.card-body -->
</div>


@endsection

@section('scripts')
<script>
    var readingInput = document.getElementById('reading');
    var tareInput = document.getElementById('tareweight');
    var netInput = document.getElementById('net');

    //Date picker
    $('#productiondate').datetimepicker({
        format : "DD/MM/YYYY"
    });

    $(document).ready(function () {

        getTareweight()
        getNet()
        setProductionDate() //set production date default

        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        getSessionVehicle()

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
                getNet()

            } else {
                reading.readOnly = true;
                reading.focus();
                $('#reading').val("");
                getNet()
            }
        });

        $(".crates").on("input", function () {
            getTareweight()
            getNet()
        });

        $('#reading').on("input", function () {
            getNet()
        });
    });

    $('#receiveModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget)
        var id = button.data('id')
        var product = button.data('product')
        var description = button.data('description')
        var transferid = button.data('transferid')
        var fromlocation = button.data('fromlocation')
        var totalweight = button.data('totalweight')

        var modal = $(this)
        modal.find('.modal-body #product_code').val(product)
        modal.find('.modal-body #description').val(description)
        modal.find('.modal-body #transfer_id').val(transferid)
        modal.find('.modal-body #from_location').val(fromlocation)
        modal.find('.modal-body #totalweight').val(totalweight)
    })

    $('receiveModal').on('hidden.bs.modal', function () {
        document.getElementById('form-update-receiving').reset();
    });

    const getTareweight = () => {
        let total_crates = $('#total_crates').val()
        let tareweight = 0

        if (parseInt(total_crates) > 0 ) {
            tareweight = parseInt(total_crates) * 1.8
            let formatted = Math.round((tareweight + Number.EPSILON) * 100) / 100;
            $('#tareweight').val(formatted);
        }
    }

    const getSessionVehicle = () => {
        let old_val = $('#session_vehicle').val()

        if (old_val) {
            // Select the option with the specified value
            $('#description').select2('destroy').select2();
            $("#description").val(old_val).trigger('change');
        }
    }

    //read scale
    function getScaleReading() {
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

                    var obj = JSON.parse(data);

                    if (obj.success == true) {
                        var reading = document.getElementById('reading');
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
                    alert('error occured when sending request');
                }
            });
        } else {
            alert("Please set comport value first");
        }
    }

    const getNet = () => {
        netInput.value = readingInput.value - tareInput.value;
    }

    const setProductionDate = () => {
        let dateToday = new Date()

        // Format the date as "DD/MM/YYYY"
        var formattedDateToday =
            `${padZero(dateToday.getDate())}/${padZero(dateToday.getMonth() + 1)}/${dateToday.getFullYear()}`
        $('#prod_date').val(formattedDateToday)

        // Split the date by slashes to get day, month, and year parts
        let dateParts = formattedDateToday.split('/');

        // Get the day part (the second element after splitting)
        let day = dateParts[0]
    }

    const padZero = (num) => {
        return num < 10 ? `0${num}` : num;
    }

    function updateIdtReceiving(event) {
        event.preventDefault();
        const form = event.target;
        var formData = new FormData(form);
        const submitButton = document.getElementById('submit-btn');
        submitButton.disabled = true;

        if (formData.get('net') <= 0.00) {
            alert('Please ensure you have valid netweight.');
            submitButton.disabled = false;
            return;
        }

        if (formData.get('total_weight') != formData.get('net')) {
            const response = confirm(
                "Issued weight does not match Receiving Weight. Are you sure you want to continue?");

            if (response) {
                formData.append('with_variance', 1);
                alert("Thanks for confirming");
            } else {
                status = false
                alert("You have cancelled this process");
                submitButton.disabled = false;
                return;
            }
        }

        try {
            fetch(form.action, {
                method: "POST",
                body: formData
            }).then(response => {
                if (response.ok) {
                    return response.json();
                }
                throw new Error("Network response was not ok.");
            }).then(data => {
                if (data.success) {
                    $('#receiveModal').modal('hide');
                    toastr.success(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                    toastr.error(data.message);
                }
            })
        } catch {
            console.error(error);

            if (error.message) {
                toastr.error(error.message);
            } else {
                toastr.error('Failed to update transfer');
            }
        } finally {
            submitButton.disabled = false;
            return;
        }
    }

</script>
@endsection
