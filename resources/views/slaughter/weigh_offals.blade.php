@extends('layouts.slaughter_master')

@section('content')

<!-- weigh -->

<div class="card m-2">
    <h2 class="card-header">Weigh Offals</h2>
    <div class="card-body">
        <form id="form-weigh-offals" method="POST" action="{{ route('save_offals_weight') }}">
            @csrf
            <div class="row text-center">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="product_code">Product Name</label>
                        <select class="custom-select" id="product_code" name="product_code" required>
                            <option value="">Choose...</option>
                            @foreach ($productCodes as $key => $value)
                                <option value={{ $key }}>{{ $key }} {{ $value }}</option>
                            @endforeach
                        </select>    
                    </div>

                    <div class="row">
                        <div class="col-12">
                            @if(empty($configs))
                                <small>No comport conifgured</small>
                            @else
                            <small>
                                <label>Reading from ComPort:</label>
                                <strong>
                                <input 
                                    type="text" style="text-align: center; border:none" id="comport_value" 
                                    value="{{ $configs[0]->comport?? "" }}" disabled
                                    >
                                </strong>
                            </small>
                            @endif
                        </div>
                        <div class="col-12">
                            <button type="button" onclick="getScaleReading()" class="btn btn-primary btn-lg">
                                <i class="fas fa-balance-scale"></i> Weigh</button>
                        </div>
                    </div>
                    
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="reading">Reading</label>
                        <input type="number" step="0.01" class="form-control" id="reading" name="reading" value=""
                            oninput="updateNetWeight()" placeholder="" readonly required>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="manual_weight" name="manual_weight">
                        <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                            <label for="tare_weight">Tare-Weight</label>
                                @if(empty($configs))
                                <input type="number" class="form-control" id="tare_weight" name="tare_weight" value="0.00" readonly required>
                                @else
                                <input type="number" class="form-control" id="tare_weight" name="tare_weight"
                                    value="{{ number_format($configs[0]->tareweight, 2)?? "" }}" readonly required>
                                @endif
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

<!--End weigh -->

<hr>

<div class="div">
    <button class="btn btn-primary " data-toggle="collapse" data-target="#slaughter_entries"><i class="fa fa-plus"></i>
        Entries
    </button>
</div>

<div id="slaughter_entries" class="collapse">
    <hr>
    <div class="row">
        <!-- offals data Table-->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"></h3>
                    <h3 class="card-title"> Weighed Entries | <span id="subtext-h1-title"><small> view weighed ordered
                                by latest</small> </span></h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="hidden" hidden>{{ $i = 1 }}</div>
                    <div class="table-responsive">
                        <table id="example1" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product Code</th>
                                    <th>Net Weight (kgs)</th>
                                    <th>Scale Reading (kgs)</th>
                                    <th>Manually Recorded</th>
                                    <th>Recorded by</th>
                                    <th>Weigh Date</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Product Code</th>
                                    <th>Net Weight (kgs)</th>
                                    <th>Scale Reading (kgs)</th>
                                    <th>Manually Recorded</th>
                                    <th>Recorded by</th>
                                    <th>Weigh Date</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                @foreach($offalsData as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->product_code }}</td>
                                    <td>{{ number_format($data->net_weight, 2) }}</td>
                                    <td>{{ number_format($data->scale_reading, 2) }}</td>
                                    @if($data->is_manual == 0)
                                        <td>
                                            <span class="badge badge-success">No</span>
                                        </td>
                                    @else
                                        <td>
                                            <span class="badge badge-danger">Yes</span>
                                        </td>
                                    @endif
                                    <td>{{ $data->username }}</td>
                                    <td>{{  $helpers->dateToHumanFormat($data->created_at) }}</td>
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
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function () {

        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        $(".readonly").keydown(function (e) {
            e.preventDefault();
            alert('please ensure you have net reading from scale');
        });

        $('#manual_weight').change(function () {
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

        });

    });

    function updateNetWeight() {
        var reading = document.getElementById('reading').value;
        var tareweight = document.getElementById('tare_weight').value;
        var netWeightInput = document.getElementById('net_weight');
        netWeightInput.value = parseFloat(reading) - parseFloat(tareweight);
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

</script>
@endsection
