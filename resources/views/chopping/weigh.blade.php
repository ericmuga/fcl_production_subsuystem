@extends('layouts.spices_master')

@section('content')
<div class="container-fluid">
    <div class="div">
        <form id="form-chopping-weigh" class="form-prevent-multiple-submits"
            action="{{ route('chopping_batch_save', 'filter') }}"
            method="post">
            @csrf
            <div class="card-group">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="row">
                            <label for="inputEmail3" class="col-sm-3 col-form-label">Recipe No </label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="template_no" id="template_no" required>
                                    <option value="">Select template</option>
                                    @foreach($templates as $tm)
                                        <option
                                            value="{{ $tm->template_no.'-'.$tm->template_output }}">
                                            {{ $tm->template_no.'-'.$tm->template_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body text-center form-group">
                        <div class="div" style="padding-top: ">
                            <button type="button" id="startChoppingRunBtn" class="btn btn-success "><i
                                    class="fa fa-play single-click" aria-hidden="true"></i> Start Chopping
                                Run</button>
                            <div id="spinner" class="spinner-border text-success" role="status"
                                style="display: none; margin-left: 10px;">
                                <span class="sr-only">running...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body text-center form-group">
                        <div class="row">
                            <label for="inputEmail3" class="col-sm-3 col-form-label">Chopping No: </label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="chopping_no" id="chopping_no" required>
                                    <option value="">Select chopping</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <br>

    <div class="div">
        <form id="form-save-weights" class="form-prevent-multiple-submits"
            action="{{ route('beef_slicing_save') }}" method="post">
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
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" value="2500" name="location_code">
                        </div>
                    </div>
                </div>
                <div class="card ">
                    <div class="card-body text-center">
                        <div class="form-group">
                            <div class="row align-items-center">
                                <div class="col-md-4" style="padding-top: 5%">
                                    <button type="button" id="weigh" value="" class="btn btn-primary"><i
                                            class="fas fa-balance-scale"></i>
                                        Weigh</button> <br>
                                    <small>Reading comport: <strong>7</strong></small>
                                </div>
                                <div class="col-md-5">
                                    <label for="exampleInputEmail1">Scale Reading</label>
                                    <input type="number" step="0.01" class="form-control" id="reading" name="reading"
                                        value="0.00" oninput="getNet()" placeholder="" readonly>
                                </div>
                                <div class="col-md-3 d-flex align-items-center" style="padding-top: 5%">
                                    <button type="button" class="btn btn-info"><i class="fa fa-times"
                                            aria-hidden="true"></i> Reset</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="manual_weight" name="manual_weight">
                            <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                        </div> <br>
                        <div class="row form-group">
                            <div class="col-md-4">
                                <label for="exampleInputPassword1">Previous Reading</label>
                                <input type="number" class="form-control" id="net" name="net" value="0.00" step=".01"
                                    placeholder="" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="exampleInputPassword1">Van Tare Weight</label>
                                <input type="number" class="form-control" id="tareweight" name="tareweight" value="40"
                                    placeholder="" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="exampleInputPassword1">Net Weight</label>
                                <input type="number" class="form-control" id="net" name="net" value="0.00" step=".01"
                                    placeholder="" readonly>
                            </div>
                        </div>
                        <input type="hidden" id="old_manual" value="{{ old('manual_weight') }}">
                    </div>
                </div>
                <div class="card ">
                    <div class="card-body text-center">
                        <div class="row form-group justify-content-center">
                            <div class="col-md-6">
                                <label for="exampleInputPassword1">Batch No</label>
                                <input type="text" class="form-control" value="" id="batch_no" name="batch_no">
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
    </div><br>

    <div class=" center-page">
        <div class="row">
            <div class="col text-center">
                <button type="button" class="btn btn-lg btn-danger" data-toggle="modal" data-target="#stopModal">
                    <i class="fa fa-stop-circle single-click" aria-hidden="true"></i> Complete Chopping Run
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="stopModal" tabindex="-1" aria-labelledby="stopModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stopModalLabel">Complete Chopping Run</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-stop-run" action="" method="post">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="stopReason">Chopping Run No</label>
                        <input class="form-control" id="stopReason" name="stop_reason" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Complete Run</button>
                </div>
            </form>
        </div>
    </div>
</div>

<hr>
<div class="div">
    <button class="btn btn-primary " data-toggle="collapse" data-target="#toggle_collapse"><i class="fa fa-plus"></i>
        Entries
    </button>
</div>

<div id="toggle_collapse" class="collapse">
    <hr>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"> Production Lines Entries | <span id="subtext-h1-title"><small> showing
                        <strong>{{ $filter?? 'All' }}</strong> entries
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
                            <th>From Batch</th>
                            <th>To Batch</th>
                            <th>Output Quantity</th>
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
                            <th>From Batch</th>
                            <th>To Batch</th>
                            <th>Output Quantity</th>
                            <th>Date</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        {{-- @foreach($batches as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                        <td><a
                                href="{{ route('chopping_production_lines', [$data->batch_no, $data->from_batch]) }}">{{ $data->batch_no }}</a>
                        </td>
                        <td>{{ $data->template_no }}</td>
                        <td>{{ $data->template_name }}</td>
                        <td>{{ $data->template_output }}</td>
                        <td>{{ $data->from_batch }}</td>
                        <td>{{ $data->to_batch }}</td>
                        <td>{{ $data->output_quantity }}</td>
                        <td>{{ $data->username }}</td>
                        <td>{{ $helpers->amPmDate($data->created_at) }}</td>
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
<!-- slicing ouput data show -->

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        let oldManual = $('#old_manual').val();
        let manualWeightCheckbox = $('#manual_weight');
        let readingInput = $('#reading');

        // Check the old_manual value on page load
        if (oldManual === 'on') {
            manualWeightCheckbox.prop('checked', true);
            readingInput.prop('readonly', false);
            readingInput.val('');
            readingInput.focus();
        }

        manualWeightCheckbox.change(function () {
            if (this.checked) {
                readingInput.prop('readonly', false);
                readingInput.val('');
                readingInput.focus();
            } else {
                readingInput.prop('readonly', true);
            }
        });

        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        document.getElementById('startChoppingRunBtn').addEventListener('click', event => {
            event.preventDefault();
            const templateNo = $('#template_no').val();

            if (!templateNo) {
                alert('Please select a template.');
                return;
            }

            const userConfirmed = confirm(
                `Do you want to create a new chopping run for template number ${templateNo}?`);

            if (userConfirmed) {
                makeChoppingRunRequest(templateNo);
            } else {
                return false;
            }
        });

    });

    const makeChoppingRunRequest = (templateNo) => {
        const spinner = document.getElementById('spinner');
        spinner.style.display = 'inline-block';

        // Truncate the templateNo string before the hyphen
        const truncatedTemplateNo = templateNo.split('-')[0];

        axios.post('/v2/chopping/make/run', {
                template_no: truncatedTemplateNo
            })
            .then(response => {
                console.log(response);
                let selectedChoppingNo = response.data.data;

                // Find the <select> element
                let selectElement = document.getElementById('chopping_no');

                // Loop through the options and set the selected attribute for the matching value
                for (let i = 0; i < selectElement.options.length; i++) {
                    if (selectElement.options[i].value === selectedChoppingNo) {
                        selectElement.options[i].setAttribute('selected', 'selected');
                        break; // Exit the loop once the option is found and selected
                    }
                }
                // Handle success, maybe show a success message
            })
            .catch(error => {
                console.error(error);
                // Handle error, maybe show an error message
            })
            .finally(() => {
                spinner.style.display = 'none';
            });
    };

</script>
@endsection
