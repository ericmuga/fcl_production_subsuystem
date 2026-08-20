@extends('layouts.sausage_master')

@section('content-header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-7">
            <h1 class="m-0"> {{ $title }} | <small>Create & View <strong></strong> Transfers Lines </small>
            </h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')

<div class="row col-md-12 card m-2">
    <div class="card-body">
        <form id="form-chopping-receipts" class="form-prevent-multiple-submits" method="POST" action="{{ route('save_stuffing_weights') }}" onsubmit="saveChoppingReceipt()">
            @csrf
            <div class="row text-center">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="product_code">Product Name</label>
                        <select class="custom-select select2" id="product_code" name="product_code" required>
                            <option value="">Select Item</option>
                            @foreach ($items as $item)
                                <option value={{ $item->item_code }}>{{ $item->item_code }} {{ $item->description }}</option>
                            @endforeach
                        </select>    
                    </div>

                    <div class="form-group mb-3">
                        <label for="output_item">Stuffing for (Output)</label>
                        <select class="custom-select select2" id="output_item" name="output_item">
                            <option value="">Select output</option>
                        </select>
                    </div>

                    <div class="form-group" >
                        <label for="batch_no">Batch No</label>
                        <input type="text" class="form-control" id="batch_no" name="batch_no" value="" required>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            @if($configs && $configs->comport)
                                <small>
                                    <label>Reading from ComPort:</label>
                                    <strong>
                                    <input 
                                        type="text" style="text-align: center; border:none" id="comport_value" 
                                        value="{{ $configs->comport ?? '' }}" disabled
                                        >
                                    </strong>
                                </small>   
                            @else
                                <small class="font-weight-bold">No comport conifgured</small>
                            @endif
                        </div>
                        <div class="col-12">
                            <button id="weigh_btn"
                                    data-scale-ip="{{ $configs->ip_address ?? ''}}"
                                    data-comport="{{ $configs->comport ?? '' }}"
                                    onclick="getWeightV2(this.dataset.scaleIp, this.dataset.comport)"
                                    class="btn btn-primary btn-lg">
                                <i class="fas fa-balance-scale"></i> Weigh</button>
                        </div>
                    </div>
                    
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="reading">Reading</label>
                        <input type="number" step="0.01" class="form-control" id="reading" name="reading" value=""
                            oninput="getNet()" placeholder="" readonly required>
                    </div>
                    <div class="form-group error"></div>

                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="manual_weight" name="manual_weight" onchange="toggleManualWeight()">
                        <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="tare_weight">Tare-Weight</label>
                                <input type="number" class="form-control" id="tare_weight" name="tare_weight" value="{{ ($configs && $configs->tareweight) ? number_format($configs->tareweight, 2) : 40 }}" readonly required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="net_weight">Net-Weight</label>
                                <input type="number" class="form-control" id="net_weight" name="net_weight"
                                    value="" readonly required>
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="btn_save" class="btn btn-primary btn-lg btn-prevent-multiple-submits mt-3">
                        <i class="fa fa-paper-plane" aria-hidden="true"></i>
                        Save
                    </button>

                </div>
            </div>
        </form>
    </div>

</div>

<div class="col-md-12 text-left" style="margin-bottom: 1%">
    <button class="btn btn-success btn-lg" data-toggle="collapse" data-target="#export_production_orders"><i
            class="fas fa-file-excel"></i> Export Generated Production Orders</button>
    <div id="export_production_orders" class="collapse"><br>
        <div class="form-inputs">
            <div class="row">
                <div class="col-lg-8" style="margin: 0 auto; float: none;">
                    <div class="card mb-3">
                        <div class="card-header">
                            <i class="fa fa-user-secret"></i>
                            Export data</div>
                        <div class="card-body">
                            <form action="{{ route('export_generated_production_orders') }}" method="post"
                                id="export-production-orders-form">
                                @csrf

                                <h6>*Filter by date range</h6>
                                <div class="row form-group">
                                    <div class="col-md-6">
                                        <label for="orders_from_date">From: (dd/mm/yyyy)</label>
                                        <input type="date" class="form-control" name="from_date"
                                            id="orders_from_date" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="orders_to_date">To: (dd/mm/yyyy)</label>
                                        <input type="date" class="form-control" name="to_date"
                                            id="orders_to_date" required>
                                    </div>
                                </div>

                                <h6>*Narrow down (optional)</h6>
                                <div class="row form-group">
                                    <div class="col-md-6">
                                        <label for="orders_packed_item">Packed Item</label>
                                        <select class="form-control select2" name="packed_item" id="orders_packed_item">
                                            <option value="all" selected>All packed items</option>
                                            @foreach($generated_packed_items as $packed)
                                                <option value="{{ $packed->packed_item }}">
                                                    {{ $packed->packed_item }}{{ $packed->description ? ' - ' . $packed->description : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="orders_process">Process</label>
                                        <select class="form-control select2" name="process" id="orders_process">
                                            <option value="all" selected>All processes</option>
                                            @foreach($generated_processes as $process)
                                                <option value="{{ $process }}">{{ $process }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="col-md-4">
                                        <label for="orders_line_type">Line Type</label>
                                        <select class="form-control" name="line_type" id="orders_line_type">
                                            <option value="all" selected>All lines</option>
                                            <option value="output">Output only</option>
                                            <option value="consumption">Consumption only</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="orders_published">Status</label>
                                        <select class="form-control" name="published" id="orders_published">
                                            <option value="all" selected>All</option>
                                            <option value="1">Published</option>
                                            <option value="0">Pending</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="orders_batch_no">Batch No</label>
                                        <input type="text" class="form-control" name="batch_no" id="orders_batch_no"
                                            placeholder="leave blank for all">
                                    </div>
                                </div> <br>
                                <div class="div" align="center">
                                    <button type="submit" class="btn btn-primary "><i class="fa fa-paper-plane"
                                            aria-hidden="true"></i> Export now</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<hr />

<div class="div">
    <button class="btn btn-primary " data-toggle="collapse" data-target="#generated_production_orders"><i class="fa fa-plus"></i>
        Generated Production Orders
    </button>
</div>

<hr />

<div id="generated_production_orders" class="collapse card">
    <div class="card-header">
        <h3 class="card-title">Generated Production Orders | <span id="subtext-h1-title"><small> orders created
                    between the weighed item and packing, last 2 days</small> </span></h3>
    </div>
    <div class="card-body">
        @if($generated_orders->isEmpty())
            <p class="text-muted mb-0">No production orders generated in the last 2 days.</p>
        @else
            <div class="row form-group">
                <div class="col-md-3">
                    <label for="filter_order_no" class="small mb-1">Order No</label>
                    <select class="form-control form-control-sm generated-orders-filter" id="filter_order_no" data-column="0">
                        <option value="">All orders</option>
                        @foreach($generated_orders->pluck('production_order_no')->unique()->sort() as $value)
                            <option value="{{ $value }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filter_process" class="small mb-1">Process</label>
                    <select class="form-control form-control-sm generated-orders-filter" id="filter_process" data-column="2">
                        <option value="">All processes</option>
                        @foreach($generated_orders->pluck('process')->filter()->unique()->sort() as $value)
                            <option value="{{ $value }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_packed_item" class="small mb-1">Packed Item</label>
                    <select class="form-control form-control-sm generated-orders-filter" id="filter_packed_item" data-column="15">
                        <option value="">All packed items</option>
                        @foreach($generated_orders->pluck('packed_item')->filter()->unique()->sort() as $value)
                            <option value="{{ $value }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filter_line_type" class="small mb-1">Line Type</label>
                    <select class="form-control form-control-sm generated-orders-filter" id="filter_line_type" data-column="7">
                        <option value="">All lines</option>
                        <option value="Output">Output</option>
                        <option value="Consumption">Consumption</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filter_published" class="small mb-1">Status</label>
                    <select class="form-control form-control-sm generated-orders-filter" id="filter_published" data-column="16">
                        <option value="">All</option>
                        <option value="Published">Published</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table id="generated_orders_table" class="table table-sm table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Order No</th>
                            <th>Step</th>
                            <th>Process</th>
                            <th>Routing</th>
                            <th>Line</th>
                            <th>Item</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th class="text-right">Quantity</th>
                            <th>UOM</th>
                            <th>Location</th>
                            <th>Recipe</th>
                            <th>Ext. Doc</th>
                            <th>Batch</th>
                            <th>Weighed Item</th>
                            <th>Packed Item</th>
                            <th>Status</th>
                            <th>By</th>
                            <th>Generated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($generated_orders as $line)
                            <tr @if($line->line_type === 'output') class="font-weight-bold" @endif>
                                <td>{{ $line->production_order_no }}</td>
                                <td>{{ $line->step }}</td>
                                <td>{{ $line->process }}</td>
                                <td>{{ $line->routing }}</td>
                                <td>{{ $line->line_no }}</td>
                                <td>{{ $line->item_no }}</td>
                                <td>{{ $line->item_description }}</td>
                                <td>{{ ucfirst($line->line_type) }}</td>
                                <td class="text-right">{{ number_format($line->quantity, 2) }}</td>
                                <td>{{ $line->uom }}</td>
                                <td>{{ $line->location_code }}</td>
                                <td>{{ $line->recipe }}</td>
                                <td>{{ $line->external_document_no }}</td>
                                <td>{{ $line->batch_no }}</td>
                                <td>{{ $line->weighed_item }}</td>
                                <td>{{ $line->packed_item }}</td>
                                <td>
                                    @if($line->published)
                                        <span class="badge badge-success">Published</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                                <td>{{ $line->username }}</td>
                                <td>{{ $helpers->amPmDate($line->created_at) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<hr />

<div class="div">
    <button class="btn btn-primary " data-toggle="collapse" data-target="#chopping_receipts_entries"><i class="fa fa-plus"></i>
        Entries
    </button>
</div>

<hr />

<div id="chopping_receipts_entries" class="collapse card">
    <div class="card-header">
        <h3 class="card-title"></h3>
        <h3 class="card-title"> Weighed Entries | <span id="subtext-h1-title"><small> view weighed ordered
                    by latest</small> </span></h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="example1" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product Code</th>
                        <th>Product Description</th>
                        <th>Net Weight (kgs)</th>
                        <th>Manual weights?</th>
                        <th>Recorded by</th>
                        <th>Weigh Date</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>Product Code</th>
                        <th>Product Description</th>
                        <th>Net Weight (kgs)</th>
                        <th>Manual weights?</th>
                        <th>Recorded by</th>
                        <th>Weigh Date</th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach($stuffing_transfers as $data)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $data->product_code }}</td>
                        <td>
                            @php
                                // Filter the $items array by matching the 'code' key
                                $item = collect($items)->firstWhere('item_code', $data->product_code);
                            @endphp
                            {{ $item->description ?? 'No description available' }}
                        </td>
                        <td>{{ number_format($data->total_weight, 2) }}</td>
                        @if($data->manual_weight == 0)
                            <td>
                                <span class="badge badge-success">No</span>
                            </td>
                        @else
                            <td>
                                <span class="badge badge-danger">Yes</span>
                            </td>
                        @endif
                        <td>{{ $data->username }}</td>
                        <td>{{ $helpers->amPmDate($data->created_at) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection


@section('scripts')
<script>
    $(document).ready(function () {
        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        // Outputs the selected product can be stuffed into, keyed by product (input) code
        const recipeOutputs = @json($recipe_outputs);

        $('#product_code').on('change', function () {
            const outputs = recipeOutputs[this.value] || [];
            const $output = $('#output_item');

            $output.empty().append(new Option('Select output', ''));

            outputs.forEach(function (row) {
                const label = [row.output_item, row.output_item_dec].filter(Boolean).join(' - ');
                $output.append(new Option(label, row.output_item));
            });

            $output.val('').trigger('change.select2');
        });

        // Generated orders are one flat list; searching, the column filters and any
        // re-sorting all happen here rather than server-side.
        const $ordersTable = $('#generated_orders_table');

        if ($ordersTable.length) {
            const ordersTable = $ordersTable.DataTable({
                responsive: false,
                autoWidth: false,
                lengthChange: true,
                // Keep the order the controller sent: newest weighing first, then
                // each order's steps and lines in sequence.
                order: [],
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                buttons: ['excel', 'csv', 'pdf', 'colvis'],
            });

            ordersTable.buttons().container().appendTo('#generated_orders_table_wrapper .col-md-6:eq(0)');

            $('.generated-orders-filter').on('change', function () {
                const value = this.value ? '^' + $.fn.dataTable.util.escapeRegex(this.value) + '$' : '';

                ordersTable.column($(this).data('column')).search(value, true, false).draw();
            });

            // The panel starts collapsed, so the table is measured at zero width
            // until it is opened.
            $('#generated_production_orders').on('shown.bs.collapse', function () {
                ordersTable.columns.adjust();
            });
        }
    });

    const netWeightInput = document.getElementById('net_weight');
    const readingInput = document.getElementById('reading');
    const tareInput = document.getElementById('tare_weight');

    function getNet() {
        netWeightInput.value = parseFloat(readingInput.value) - parseFloat(tareInput.value);
    }

    const getWeightV2 = (ip,comport) => {
        let url;
        let weight_url = @json(config('app.get_weight_endpoint'));
        
        let button;

        if (!ip || !comport) {
            alert('Scale IP address or COM port is not configured.');
            return;
        } else {    
            url    = ip + weight_url + '/'+ comport; 
            button = document.getElementById('weigh_btn');
        }

        const fullUrl = 'http://'+url;
        // console.log('full URL:', fullUrl);

        // Disable the button and change its label
        button.disabled   = true;
        const originalLabel = button.innerHTML;
        button.innerHTML  = '<strong>Reading...</strong>';

        // Clear any previous error message
        document.querySelector('.form-group.error').innerHTML = '';

        // Set a timeout to abort the request if it takes longer than 5 seconds
        const source    = axios.CancelToken.source();
        const timeoutId = setTimeout(() => {
            source.cancel('No response received from scale');
            console.error('No response received from scale');
            // Re-enable the button and revert the label
            button.disabled  = false;
            button.innerHTML = originalLabel;
            // Display the error message
            document.querySelector('.form-group.error').innerHTML = '<div class="alert alert-danger small-alert">No response received from scale</div>';
        }, 5000);

        axios.get(fullUrl, {cancelToken: source.token})
        .then(function (response) {
            console.log(response.data);
            clearTimeout(timeoutId); // Clear the timeout
            if (response.data.success) {
                // Set the value of the input field with id="reading"
                const readingInput   = document.getElementById('reading');
                readingInput.value = parseFloat(response.data.response).toFixed(2);

                // Trigger the getNet function manually
                getNet();
            } else {
                console.error('API call was not successful.');
                document.querySelector('.form-group.error').innerHTML = '<div class="alert alert-danger small-alert">API call was not successful.</div>';
            }
        })
        .catch(function (error) {
            if (axios.isCancel(error)) {
                console.log(error.message);
                document.querySelector('.form-group.error').innerHTML = '<div class="alert alert-danger small-alert">'+error.message+'</div>';
            } else {
                console.log('There was an error making the request: '+error.message);
                document.querySelector('.form-group.error').innerHTML = '<div class="alert alert-danger small-alert">Error on request: '+error.message+'</div>';
            }
        })
        .finally(function () {
            // Re-enable the button and revert the label
            button.disabled  = false;
            button.innerHTML = originalLabel;
        });
    };

    function getScaleReading() {
        var comport = $('#comport_value').val();

        if (comport != null) {
            $.ajax({
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                        .attr('content')
                },
                url: "{{ url('slaughter/read-scale-api-service') }}",

                data: {
                    'comport': comport,

                },
                dataType: 'JSON',
                success: function (data) {
                    //console.log(data);

                    var obj = JSON.parse(data);
                    //console.log(obj.success);

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
                    console.log(errors);
                    alert('error occured when sending request');
                }
            });

        } else {
            alert("Please set comport value first");
        }
    }

    function toggleManualWeight() {
        var manual_weight = document.getElementById('manual_weight');
        var reading = document.getElementById('reading');
        if (manual_weight.checked == true) {
            reading.readOnly = false;
            reading.focus();
            $('#reading').val("");
            $('#net_weight').val("");

        } else {
            reading.readOnly = true;
            $('#reading').val("");
            $('#net_weight').val("");
        }
    }

    function saveChoppingReceipt() {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const url = form.action;
        const saveBtn = document.getElementById('btn_save');
        saveBtn.disabled = true;
        saveBtn.classList.add('disabled');

        try {

            // ensure weight is entered
            if (!formData.get('reading') || !formData.get('net_weight')) {
                throw new Error('Please enter weight');
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                        .attr('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_code: formData.get('product_code'),
                    output_item: formData.get('output_item'),
                    batch_no: formData.get('batch_no'),
                    net_weight: formData.get('net_weight'),
                    manual_weight: formData.get('manual_weight'),
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Receipt saved successfully');

                    form.reset();
                    // Orders are generated after this response is sent, so the pause
                    // gives that a moment to land before the panel is re-read.
                    setTimeout(() => location.reload(), 1500);
                } else {
                    console.error(data);
                    toastr.error(data.message);
                }
            })

        } catch (error) {
            console.error(error);

            if (error.message) {
                toastr.error(error.message);
            } else {
                toastr.error('Failed to save receipt');
            }
        } finally {
            saveBtn.disabled = false;
            saveBtn.classList.remove('disabled');
            return;
        }
    }
</script>
@endsection