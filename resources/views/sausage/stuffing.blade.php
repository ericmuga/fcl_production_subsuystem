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
                            <button id="weigh_btn" type="button" data-scale-ip="{{ $configs->ip_address ?? ''}}" onclick="getScaleReading()" class="btn btn-primary btn-lg">
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
    });

    const netWeightInput = document.getElementById('net_weight');
    const readingInput = document.getElementById('reading');
    const tareInput = document.getElementById('tare_weight');

    function getNet() {
        netWeightInput.value = parseFloat(readingInput.value) - parseFloat(tareInput.value);
    }

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
                    location.reload();
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