@extends('layouts.spices_master')

@section('content')
<div class="container-fluid">
    <div class="div">
        <form id="form-chopping-weigh" class="form-prevent-multiple-submits" action="" method="post">
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
                                <div id="loadOpenChoppingsSpinner" class="spinner-border text-success" role="status"
                                    style="display: none; margin-left: 10px;">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <br>

    <div class="div">
        <form id="form-save-weights" class="form-prevent-multiple-submits" action="" method="post">
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
                                    <div id="loadTemplateProductsSpinner" class="spinner-border text-success"
                                        role="status" style="display: none; margin-left: 2%;">
                                        <span class="sr-only">Loading...</span>
                                    </div>
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
                                <div class="col-md-4" style="padding-top:">
                                    <button type="button" id="weigh_fresh" onclick="getWeightV2('fresh')" value="" class="btn btn-primary"><i
                                            class="fas fa-balance-scale"></i>
                                        Fresh</button> <br>
                                    <small>Reading comport: <strong>{{ $scale_configs['Fresh']->comport }}</strong></small>
                                    <button type="button" id="weigh_cont" onclick="getWeightV2('cont')"value="" class="btn btn-primary"><i
                                            class="fas fa-balance-scale"></i>
                                        Con'tl</button> <br>
                                    <small>Reading comport: <strong>{{ $scale_configs['Continentals']->comport }}</strong></small>
                                </div>                                
                                <div class="col-md-4">
                                    <label for="exampleInputEmail1">Scale Reading</label>
                                    <input type="number" step="0.01" class="form-control" id="reading" name="reading"
                                        value="0.00" oninput="getNet()" placeholder="" readonly>
                                </div>
                                <div class="col-md-4 d-flex align-items-center" style="padding-top: 5%">
                                    <button type="button" class="btn btn-warning btn-sm" id="setPrev">
                                        <i class="fa fa-plus" aria-hidden="true"></i> Prev
                                    </button>
                                    <button type="button" class="btn btn-info btn-sm" id="resetButton"
                                        style="margin-left: 10px;">
                                        <i class="fa fa-times" aria-hidden="true"></i> Reset
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" id="fresh_url" value="{{ $scale_configs['Fresh']->ip_address.config('app.get_weight_endpoint').'/'. $scale_configs['Fresh']->comport }}">
                            <input type="hidden" id="cont_url" value="{{ $scale_configs['Continentals']->ip_address.config('app.get_weight_endpoint').'/'. $scale_configs['Continentals']->comport }}">

                            <div class="form-group error"></div>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="manual_weight" name="manual_weight">
                            <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                        </div> <br>
                        <div class="row form-group">
                            <div class="col-md-4">
                                <label for="exampleInputPassword1">Previous Reading</label>
                                <input type="number" class="form-control" id="previous_reading" name="previous_reading"
                                    value="0.00" step=".01" oninput="getNet()" placeholder="" readonly>
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
                                <input type="text" class="form-control" value="" id="batch_no" name="batch_no" required
                                    readonly>
                            </div>
                        </div>
                        <div class="form-group" style="padding-top: 5%">
                            <button type="button" id="save_btn" class="btn btn-primary btn-lg"><i
                                    class="fa fa-paper-plane single-click" aria-hidden="true"></i> Save</button>
                            <div id="saveWeightsSpinner" class="spinner-border text-success" role="status"
                                style="display: none; margin-top: 2%;">
                                <span class="sr-only">Loading...</span>
                            </div>
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
                <button type="button" id="modalLauncherBtn" class="btn btn-lg btn-danger" data-toggle="modal" data-target="#stopModal">
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
            <form id="form-stop-run" action="{{ route('close_chopping_run') }}" method="post"
                class="form-prevent-multiple-submits">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="complete_run_number">Chopping Run No</label>
                        <input class="form-control" id="complete_run_number" name="complete_run_number" readonly
                            required>
                        <span class="text-danger" id="err1"></span>
                    </div>
                    <div class="form-group">
                        <label for="batch_size">Batch Size Run No</label>
                        <select class="form-control select2" name="batch_size" id="batch_size" required>
                            <option value="">Select Batch Size</option>
                            <option value="0.25">Quarter Batch
                            </option>
                            <option value="0.5">Half Batch
                            </option>
                            <option selected value="1">Full Batch
                            </option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger btn-prevent-multiple-submits"><i
                            class="fa fa-paper-plane" aria-hidden="true"></i> Close Run</button>
                    <div id="closeRunSpinner" class="spinner-border text-success" role="status"
                        style="display: none; margin-left: 10px;">
                        <span class="sr-only">running...</span>
                    </div>
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
            <h3 class="card-title"> Closed Chopping Runs Registry | <span id="subtext-h1-title"><small> showing
                        <strong>{{ $filter?? 'todays' }}</strong>
                        entries
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
                            <th>Run No</th>
                            <th>Template Name</th>
                            <th>Created By</th>
                            <th>Created Time</th>
                            <th>Closed By</th>
                            <th>Closed Time</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Run No</th>
                            <th>Template Name</th>
                            <th>Created By</th>
                            <th>Created Time</th>
                            <th>Closed By</th>
                            <th>Closed Time</th>
                        </tr>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach($choppings as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><a href="{{ route('chopping_lines', $data->chopping_id) }}">{{ $data->chopping_id }}</a>
                                </td>
                                <td>{{ $data->template_name }}</td>
                                <td>{{ $data->creator_username }}</td>
                                <td>{{ $helpers->amPmDate($data->created_at) }}</td>
                                <td>{{ $data->closer_username}}</td>
                                <td>{{ $helpers->amPmDate($data->updated_at) }}</td>
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
<!-- slicing ouput data show -->

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        checkManualWeights()
        
        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        document.getElementById('modalLauncherBtn').addEventListener('click', checkClosingRunNumber);

        document.getElementById('startChoppingRunBtn').addEventListener('click', event => {
            event.preventDefault();
            const button = event.target;
            const templateNo = $('#template_no').val();

            if (!templateNo) {
                alert('Please select a template.');
                return;
            }

            // Disable the button to prevent multiple clicks
            button.disabled = true;

            const userConfirmed = confirm(
                `Do you want to create a new chopping run for template number ${templateNo}?`);

            if (userConfirmed) {
                makeChoppingRunRequest(templateNo).finally(() => {
                    // Re-enable the button after the request is completed
                    button.disabled = false;
                });
            } else {
                // Re-enable the button if the user cancels the action
                button.disabled = false;
                return false;
            }
        });

        document.getElementById('save_btn').addEventListener('click', event => {
            event.preventDefault();
            const saveBtn = event.target;
            const reading = $('#reading').val();
            const product = $('#product').val();
            const batchNo = $('#batch_no').val();
            const net = $('#net').val();

            if (!product) {
                alert('Please select a product first.');
                return;
            }

            if (!batchNo) {
                alert('Please indicate the chopping run number.');
                return;
            }

            if (parseFloat(net) <= 1 ) {
                alert('Please ensure Net weight is valid.');
                return;
            }

            // Disable the button to prevent multiple submissions
            saveBtn.disabled = true;

            saveWeighLines(product, batchNo, reading, net, saveBtn);
        });

        $('#template_no').change(function () {
            const templateNo = $(this).val();
            const truncatedTemplateNo = templateNo.split('-')[0]
            if (!templateNo) {
                alert('Please select a template.');
                return;
            }
            $('#batch_no').val('')
            loadOpenChoppings(truncatedTemplateNo)
        });

        $('#chopping_no').change(function () {
            let choppingNo = $(this).val();
            $('#batch_no').val(choppingNo)
            $('#complete_run_number').val(choppingNo)
        });

        $('#setPrev').on('click', function () {
            let currentReading = $('#reading').val();
            $('#previous_reading').val(currentReading).trigger('input');
        });

        $('#resetButton').on('click', function () {
            $('#reading').val('').focus();
            $('#previous_reading').val('0.00').trigger('input');
        });

    });

    const getNet = () => {
        let reading = parseFloat($('#reading').val());
        let prev_reading = parseFloat($('#previous_reading').val());
        let tareweight = parseFloat($('#tareweight').val());

        let netWeight = 0.00;

        // Ensure the values are numbers
        if (isNaN(reading)) reading = 0.00;
        if (isNaN(prev_reading)) prev_reading = 0.00;
        if (isNaN(tareweight)) tareweight = 0.00;

        if (reading > 0) {
            if (prev_reading > 0) {
                netWeight = reading - prev_reading;
            } else {
                netWeight = reading - tareweight;
            }
        }

        netWeight = netWeight.toFixed(2);
        $('#net').val(netWeight);
    };

    const getWeightV2 = (scaleType) => {
        let url;
        let button;

        if (scaleType === 'fresh') {
            url = document.getElementById('fresh_url').value;
            button = document.getElementById('weigh_fresh');
        } else if (scaleType === 'cont') {
            url = document.getElementById('cont_url').value;
            button = document.getElementById('weigh_cont');
        } else {
            console.error('Invalid scale type');
            return;
        }

        const fullUrl = 'http://' + url;
        console.log('full URL:', fullUrl);

        // Disable the button and change its label
        button.disabled = true;
        const originalLabel = button.innerHTML;
        button.innerHTML = '<strong>Reading...</strong>';

        // Clear any previous error message
        document.querySelector('.form-group.error').innerHTML = '';

        // Set a timeout to abort the request if it takes longer than 5 seconds
        const source = axios.CancelToken.source();
        const timeoutId = setTimeout(() => {
            source.cancel('No response received from scale');
            console.error('No response received from scale');
            // Re-enable the button and revert the label
            button.disabled = false;
            button.innerHTML = originalLabel;
            // Display the error message
            document.querySelector('.form-group.error').innerHTML = '<div class="alert alert-danger small-alert">No response received from scale</div>';
        }, 5000);

        axios.get(fullUrl, { cancelToken: source.token })
            .then(function (response) {
                console.log(response.data);
                clearTimeout(timeoutId); // Clear the timeout
                if (response.data.success) {
                    // Set the value of the input field with id="reading"
                    document.getElementById('reading').value = parseFloat(response.data.response).toFixed(2);
                } else {
                    console.error('API call was not successful.');
                    document.querySelector('.form-group.error').innerHTML = '<div class="alert alert-danger small-alert">API call was not successful.</div>';
                }
            })
            .catch(function (error) {
                if (axios.isCancel(error)) {
                    console.log(error.message);
                    document.querySelector('.form-group.error').innerHTML = '<div class="alert alert-danger small-alert">' + error.message + '</div>';
                } else {
                    console.log('There was an error making the request: ' + error.message);
                    document.querySelector('.form-group.error').innerHTML = '<div class="alert alert-danger small-alert">Error on request: ' + error.message + '</div>';
                }
            })
            .finally(function () {
                // Re-enable the button and revert the label
                button.disabled = false;
                button.innerHTML = originalLabel;
            });
    }

    const checkClosingRunNumber = () => {
        const completeRunNumber = document.getElementById('complete_run_number').value;
        const errorSpan = document.getElementById('err1');
        
        if (!completeRunNumber) {
            errorSpan.textContent = "Error: Closing Run number is required.";
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        } else {
            errorSpan.textContent = "";
            $(".btn-prevent-multiple-submits").attr('disabled', false);
        }
    }

    const saveWeighLines = (product, batchNo, reading, net, saveBtn) => {
        const loadSpinner = document.getElementById('saveWeightsSpinner');
        loadSpinner.style.display = 'inline-block';

        axios.post('/v2/chopping/save-weighings', {
                product: product,
                batch: batchNo,
                reading: reading,
                net: net
            })
            .then(response => {
                // console.log(response);
                if (response.data.success) {
                    $('#previous_reading').val(response.data.reading).trigger('input');
                }
            })
            .catch(error => {
                console.error(error);
            })
            .finally(() => {
                loadSpinner.style.display = 'none';
                // Unselect the selected product option using Select2 method
                $('#product').val(null).trigger('change');

                // Re-enable the button after the request is complete
                saveBtn.disabled = false;
            });
    }

    const loadTemplateProducts = (templateNo) => {
        const loadSpinner = document.getElementById('loadTemplateProductsSpinner');
        loadSpinner.style.display = 'inline-block';

        axios.get('/v2/chopping/fetch-products', {
                params: {
                    template_no: templateNo
                }
            })
            .then(response => {
                if (response.data.success) {
                    const products = response.data.data;
                    const selectElement = document.getElementById('product');

                    // Clear the existing options
                    selectElement.innerHTML = '<option value="">Select product</option>';

                    // Populate the select element with new options
                    products.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.item_code;
                        option.textContent = product.item_code + ' ' + product.description;
                        selectElement.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error(error);
            })
            .finally(() => {
                loadSpinner.style.display = 'none';
            });
    };

    const loadOpenChoppings = (templateNo) => {
        const loadSpinner = document.getElementById('loadOpenChoppingsSpinner');
        loadSpinner.style.display = 'inline-block';

        axios.get('/v2/chopping/fetch-open-runs', {
                params: {
                    template_no: templateNo
                }
            })
            .then(response => {
                
                if (response.data.success) {
                    const runs = response.data.data;
                    const selectElement = document.getElementById('chopping_no');

                    // Clear the existing options
                    selectElement.innerHTML = '<option value="">Select chopping</option>';

                    // Populate the select element with new options
                    runs.forEach(run => {
                        // console.log(run)
                        const option = document.createElement('option');
                        option.value = run.chopping_id;
                        option.textContent = run.chopping_id;
                        selectElement.appendChild(option);
                    });

                    // Set the newly created chopping as selected
                    const selectedChoppingNo = response.data.selectedChoppingNo;
                    if (selectedChoppingNo) {
                        selectElement.value = selectedChoppingNo;
                    }

                    // Load template products
                    loadTemplateProducts(templateNo);
                }
            })
            .catch(error => {
                console.error(error);
            })
            .finally(() => {
                loadSpinner.style.display = 'none';
            });
    };

    const checkManualWeights = () => {
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
    }

    const makeChoppingRunRequest = (templateNo) => {
        const spinner = document.getElementById('spinner');
        spinner.style.display = 'inline-block';

        // Truncate the templateNo string before the hyphen
        const truncatedTemplateNo = templateNo.split('-')[0];

        return axios.post('/v2/chopping/make/run', {
                template_no: truncatedTemplateNo
            })
            .then(response => {
                // console.log(response);
                let selectedChoppingNo = response.data.data;

                $('#batch_no').val(selectedChoppingNo)
                $('#complete_run_number').val(selectedChoppingNo)

                //Append the new option and select it
                const selectElement = document.getElementById('chopping_no');
                const option = document.createElement('option');
                option.value = selectedChoppingNo;
                option.textContent = selectedChoppingNo;
                option.selected = true;
                selectElement.appendChild(option);
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
