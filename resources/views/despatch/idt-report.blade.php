@extends('layouts.despatch_master')

@section('content')

<div class="row">
    <div class="col-md-6">
        <!-- Start export -->
        <div class="col-md-12 text-left" style="margin-bottom: 1%">
            <button class="btn btn-success btn-lg" data-toggle="collapse" data-target="#export_data"><i
                    class="fas fa-file-excel"></i> Export History Data</button>
            <div id="export_data" class="collapse"><br>
                <div class="form-inputs">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <i class="fa fa-user-secret"></i>
                                    Export data</div>
                                <div class="card-body">
                                    <form action="{{ route('despatch_export_idt') }}" method="post" id="export-logs-form">
                                        @csrf
        
                                        <h6>*Filter by date range</h6>
                                        <div class="row form-group">
                                            <div class="col-md-6">
                                                <label for="stemplate_date_created_from_flagged">From: (dd/mm/yyyy)</label>
                                                <input type="date" class="form-control" name="from_date"
                                                    id="stemplate_date_created_from_flagged" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="stemplate_date_created_from_flagged">To: (dd/mm/yyyy)</label>
                                                <input type="date" class="form-control" name="to_date"
                                                    id="stemplate_date_created_from_flagged" required>
                                            </div>
                                        </div>
                                        <div class="row form-group">
                                            <div class="col-md-6">
                                                <label for="stemplate_date_created_from_flagged">Transfer From</label>
                                                <select class="form-control select2" name="transfer_from"
                                                    id="transfer_from" required>
                                                    <option disabled selected value> -- select an option -- </option>
                                                    <option value="1570">Butchery</option>
                                                    <option value="2055">Sausage</option>
                                                    <option value="2595">Bacon & Ham</option>
                                                    <option value="2500">Bacon & Ham Curing</option>
                                                    <option value="3535">Despatch</option>
                                                    <option value="3035">Petfood</option>
                                                    <option value="3555">old Factory</option>
                                                    <option value="4450">QA</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="stemplate_date_created_from_flagged">Transfer To</label>
                                                <select class="form-control select2" name="transfer_to"
                                                    id="transfer_to" required>
                                                    <option disabled selected value> -- select an option -- </option>
                                                    <option value="1570">Butchery</option>
                                                    <option value="2055">Sausage</option>
                                                    <option value="2595">Bacon & Ham</option>
                                                    <option value="2500">Bacon & Ham Curing</option>
                                                    <option value="3535">Despatch</option>
                                                    <option value="3035">Petfood</option>
                                                    <option value="3555">old Factory</option>
                                                    <option value="3540"> Third Party</option>
                                                    <option value="4450"> QA</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 text-center">
                                                <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"
                                                        aria-hidden="true"></i> Export Lines</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End export -->
    </div>
    <div class="col-md-6">
        <!-- Start Summary -->
        <div class="col-md-12 text-left" style="margin-bottom: 1%">
            <button class="btn btn-warning btn-lg" data-toggle="collapse" data-target="#summary_data"><i
                    class="fas fa-file-excel"></i> Export Summary Data</button>
            <div id="summary_data" class="collapse"><br>
                <div class="form-inputs">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <i class="fa fa-user-secret"></i>
                                    Export data</div>
                                <div class="card-body">
                                    <form action="{{ route('despatch_export_idt_summary') }}" method="post" id="summary-idt-form">
                                        @csrf
        
                                        <h6>*Filter by date range</h6>
                                        <div class="row form-group">
                                            <div class="col-md-6">
                                                <label for="summary_date_from">From: (dd/mm/yyyy)</label>
                                                <input type="date" class="form-control" name="from_date"
                                                    id="summary_date_from" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="summary_date_to">To: (dd/mm/yyyy)</label>
                                                <input type="date" class="form-control" name="to_date"
                                                    id="summary_date_to" required>
                                            </div>
                                        </div>
                                        <div class="row form-group">
                                            <div class="col-md-6">
                                                <label for="summary_transfer_from">Transfer From</label>
                                                <select class="form-control select2" name="transfer_from"
                                                    id="summary_transfer_from" required>
                                                    <option disabled selected value> -- select an option -- </option>
                                                    <option value="1570">Butchery</option>
                                                    <option value="2055">Sausage</option>
                                                    <option value="2595">Bacon & Ham</option>
                                                    <option value="2500">Bacon & Ham Curing</option>
                                                    <option value="3535">Despatch</option>
                                                    <option value="3035">Petfood</option>
                                                    <option value="3555">old Factory</option>
                                                    <option value="4450">QA</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="summary_transfer_to">Transfer To</label>
                                                <select class="form-control select2" name="transfer_to"
                                                    id="summary_transfer_to" required>
                                                    <option disabled selected value> -- select an option -- </option>
                                                    <option value="1570">Butchery</option>
                                                    <option value="2055">Sausage</option>
                                                    <option value="2595">Bacon & Ham</option>
                                                    <option value="2500">Bacon & Ham Curing</option>
                                                    <option value="3535">Despatch</option>
                                                    <option value="3035">Petfood</option>
                                                    <option value="3555">old Factory</option>
                                                    <option value="3540"> Third Party</option>
                                                    <option value="4450"> QA</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 text-center">
                                                <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"
                                                        aria-hidden="true"></i> Export Summary</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End summary -->
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Despatch Production Registry | showing last                    <strong> {{ $days_filter?? 'Today' }}</strong> Days Entries with limit of <strong> {{ $limiter }} </strong>entries</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table display nowrap table-striped table-bordered table-hover"
                        width="100%">
                        <thead>
                            <tr>
                                <th>IDT No</th>
                                <th>Product Code</th>
                                <th>Product</th>
                                {{-- <th>Std Crate Count</th>
                                <th>Std Unit Measure</th> --}}
                                <th>Location</th>
                                <th>Transfer From </th>
                                {{-- <th>Chiller</th> 
                                <th>Issued Total Crates</th>
                                <th>Issued Full Crates</th>
                                <th>Issued Incomplete Crate Pieces</th> --}}
                                <th>Issued Total Pieces</th>
                                <th>Issued Total Weight</th>
                                {{-- <th>Received Total Crates</th>
                                <th>Received Full Crates</th>
                                <th>Received Incomplete Crate Pieces</th> --}}
                                <th>Received Total Pieces</th>
                                <th>Received Total Weight</th>
                                <th>Flagged Variance?</th>
                                <th>Received By</th>
                                <th>Export No</th>
                                <th>Batch No</th>
                                <th>Issue Date</th>
                                <th>Receipt Date</th>
                                {{-- <th>Action</th> --}}
                        </thead>
                        <tfoot>
                            <tr>
                                <th>IDT No</th>
                                <th>Product Code</th>
                                <th>Product</th>
                                {{-- <th>Std Crate Count</th>
                                <th>Std Unit Measure</th> --}}
                                <th>Location</th>
                                <th>Transfer From </th>
                                {{-- <th>Chiller</th> 
                                <th>Issued Total Crates</th>
                                <th>Issued Full Crates</th>
                                <th>Issued Incomplete Crate Pieces</th> --}}
                                <th>Issued Total Pieces</th>
                                <th>Issued Total Weight</th>
                                {{-- <th>Received Total Crates</th>
                                <th>Received Full Crates</th>
                                <th>Received Incomplete Crate Pieces</th> --}}
                                <th>Received Total Pieces</th>
                                <th>Received Total Weight</th>
                                <th>Flagged Variance?</th>
                                <th>Received By</th>
                                <th>Export No</th>
                                <th>Batch No</th>
                                <th>Issue Date</th>
                                <th>Receipt Date</th>
                                {{-- <th>Action</th> --}}
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($transfer_lines as $data)
                            <tr>
                                <td>{{ $data->id }}</td>
                                <td>{{ $data->product_code }}</td>
                                <td>{{ $data->product }}</td>
                                {{-- <td>{{ $data->unit_count_per_crate }}</td>
                                <td>{{ number_format($data->qty_per_unit_of_measure, 2) }}</td> --}}
                                <td>{{ $data->location_code }}</td>
                                <td>{{ $data->transfer_from }}</td>
                                {{-- <td>{{ $data->chiller_code }}</td>
                                <td>{{ $data->total_crates }}</td>
                                <td>{{ $data->full_crates }}</td>
                                <td>{{ number_format($data->incomplete_crate_pieces, 1) }}</td> --}}
                                <td>{{ $data->total_pieces }}</td>
                                <td>{{ $data->total_weight }}</td>
                                {{-- <td>{{ number_format($data->receiver_total_crates, 1) }}</td>
                                <td>{{ number_format($data->receiver_full_crates, 1) }}</td>
                                <td>{{ number_format($data->receiver_incomplete_crate_pieces, 1) }}</td> --}}
                                <td>{{ $data->receiver_total_pieces ?? 0 }}</td>
                                <td>{{ $data->receiver_total_weight ?? 0 }}</td>

                                @if ($data->received_by == null )
                                <td><span class="badge badge-info">pending receipt</span></td>
                                @elseif ($data->received_by != null && $data->with_variance == 0)
                                <td><span class="badge badge-warning">Yes</span></td>
                                @else
                                <td><span class="badge badge-success">No</span></td>
                                @endif

                                <td>{{ $data->username }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ $data->batch_no }}</td>
                                <td>{{ $helpers->amPmDate($data->created_at) }}</td>
                                <td>{{ $helpers->amPmDate($data->updated_at) }}</td>
                                {{-- <td>
                                    <button type="button" data-id="{{$data->id}}"
                                data-product="{{ $data->product_code }}"
                                data-unit_count="{{ $data->unit_count_per_crate }}"
                                data-unit_measure="{{ number_format($data->qty_per_unit_of_measure, 2) }}"
                                data-item="{{ $data->product }}" class="btn btn-primary btn-xs"
                                title="Correct Received transfer" id="despatchCorrectionModalShow"><i
                                    class="fas fa-edit"></i>
                                </button>
                                </td> --}}
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>


<div class="modal fade" id="despatchCorrectionModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form id="form-save-batch" class="form-prevent-multiple-submits" action="{{ route('receive_idt') }}"
            method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Receive Dispatch Transfer</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card-group">
                        <div class="card">
                            <div class="card-body" style="">
                                <div class="form-group">
                                    <div class="row">
                                        <label for="inputEmail3" class="col-sm-3 col-form-label">Product Name </label>
                                        <div class="col-sm-9">
                                            <input type="text" readonly class="form-control" value="" id="item"
                                                placeholder="" name="item" readonly>
                                            <input type="hidden" name="product" id="product" value="">
                                            <input type="hidden" name="item_id" id="item_id" value="">
                                        </div>
                                    </div><br>
                                    <div class="row">
                                        <label for="inputEmail3" class="col-sm-3 col-form-label">Unit Count Per Crate
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="number" readonly class="form-control input_params" value="0"
                                                id="unit_crate_count" name="unit_crate_count" placeholder=""
                                                name="unit_crate_count">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label for="inputEmail3" class="col-sm-3 col-form-label">Item Unit Measure
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="number" readonly class="form-control input_params" value="0"
                                                id="unit_measure" name="unit_measure" placeholder=""
                                                name="unit_measure">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body form-group">
                                <div class="row">
                                    <label for="inputEmail3" class="col-sm-3 col-form-label">Transfer To uu</label>
                                    <div class="col-sm-9">
                                        <select class="form-control select2 locations" name="chiller_code"
                                            id="chiller_code" required>
                                            <option value="">Select chiller</option>
                                        </select>
                                    </div>
                                </div><br>
                                <div class="row">
                                    <label for="inputEmail3" class="col-sm-3 col-form-label">Total Crates</label>
                                    <div class="col-sm-9">
                                        <input type="number" class="form-control crates" id="total_crates"
                                            name="total_crates" value="" required onkeyup="handleChange()"
                                            placeholder="">
                                    </div>
                                </div><br>
                                <div class="row">
                                    <label for="inputEmail3" class="col-sm-3 col-form-label">No. of full Crates</label>
                                    <div class="col-sm-9">
                                        <input type="number" class="form-control crates" value="" id="full_crates"
                                            name="full_crates" required onkeyup="handleChange()" placeholder="">
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
                                        <input type="number" class="form-control" value="0" id="pieces" name="pieces"
                                            placeholder="">
                                    </div>
                                </div><br>
                                <div class="row">
                                    <label for="inputEmail3" class="col-sm-3 col-form-label">Calc Weight(Kgs)</label>
                                    <div class="col-sm-9">
                                        <input type="number" step=".01" class="form-control" value="0" id="weight"
                                            name="weight" placeholder="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="location_code" id="location_code" value="3535">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-lg btn-prevent-multiple-submits"
                        onclick="return validateOnSubmit()"><i class="fa fa-paper-plane single-click"
                            aria-hidden="true"></i> Save</button>
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
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {

        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        $('#pieces').keyup(function () {
            calculateWeight()
        })

        $('#despatchCorrectionModal').on('shown.bs.modal', function (e) {
            e.preventDefault()
            let product_code = $('#product').val()
            fetchTransferToLocations(product_code)
        })

        $("body").on("click", "#despatchCorrectionModalShow", function (e) {
            e.preventDefault();

            let id = $(this).data('id');
            let code = $(this).data('product');
            let item = $(this).data('item');
            let unit_count = $(this).data('unit_count');
            let unit_measure = $(this).data('unit_measure');

            $('#item_id').val(id);
            $('#product').val(code); //item_code
            $('#item').val(item); //item
            $('#unit_crate_count').val(unit_count);
            $('#unit_measure').val(unit_measure);

            $('#despatchCorrectionModal').modal('show');
        });
    });

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

    const validateOnSubmit = () => {
        let status = true

        return status
    }

    const setUserMessage = (field_succ, field_err, message_succ, message_err) => {
        document.getElementById(field_succ).innerHTML = message_succ
        document.getElementById(field_err).innerHTML = message_err
    }

    const calculateWeight = (pieces) => {
        let weight = parseInt(pieces) * parseFloat(unit_measure)

        $('#weight').val(weight.toFixed(2))
    }

    const fetchTransferToLocations = (prod_code) => {
        $('#loading').collapse('show');

        const url = '/fetch-transferToLocations-axios'

        const request_data = {
            product_code: prod_code
        }

        return axios.post(url, request_data)
            .then((res) => {
                if (res) {
                    $('#loading').collapse('hide');
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

</script>
@endsection
