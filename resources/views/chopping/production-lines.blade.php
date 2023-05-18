@extends('layouts.spices_master')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="card-title"> Chopping Production Lines Entries | <span id="subtext-h1-title">showing
                                batch No:
                                <strong>{{ $batch_no }}</strong> entries
                                ordered by
                                latest</span></h3>
                    </div>
                    <div class="col-md-5">

                        @if ($lines->first()->status == 'open')
                        <button type="button" onClick="editModal()" class="btn btn-primary" id="#editModalShow"><i
                                class="fas fa-pencil-alt"></i>
                            Edit Batch
                        </button>
                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#closeModal"><i
                                class="fas fa-lock"></i>
                            Close Batch
                        </button>

                        @elseif ($lines->first()->status == 'closed')
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#postModal"><i
                                class="fas fa-save"></i>
                            Post Batch
                        </button>
                        @endif

                    </div>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example1" class="table table-striped table-bordered table-hover" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item Code </th>
                                <th>Description </th>
                                <th>std Qty </th>
                                <th>Used Qty</th>
                                <th>Main Product</th>
                                <th>Type</th>
                                <th>Unit Measure</th>
                                <th>Location</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Item Code </th>
                                <th>Description </th>
                                <<th>std Qty </th>
                                    <th>Used Qty</th>
                                    <th>Main Product</th>
                                    <th>Type</th>
                                    <th>Unit Measure</th>
                                    <th>Location</th>
                                    <th>Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($lines as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->item_code }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ number_format($data->units_per_100, 2) }}</td>
                                <td>{{ number_format($data->quantity, 2) }}</td>
                                @if ($data->main_product == 'No')
                                <td><span class="badge badge-warning">No</span></td>
                                @else
                                <td><span class="badge badge-success">Yes</span></td>
                                @endif

                                @if ($data->type == 'Intake')
                                <td><span class="badge badge-warning">Intake</span></td>
                                @else
                                <td><span class="badge badge-success">Output</span></td>
                                @endif

                                <td>{{ $data->unit_measure }}</td>
                                <td>{{ $data->location }}</td>
                                <td>{{ $helpers->amPmDate($data->created_at) }}</td>
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
<!-- slicing ouput data show -->

<!--Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form id="form-close-post" class="form-prevent-multiple-submits" action="{{ route('chopping_update_batch') }}"
            method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Items for @foreach ($lines as $l )
                        @if ($loop->last) <input type="text" hidden value="{{ $l->item_code }}" name="main_item">
                        <strong>{{ $l->description }} </strong>@endif @endforeach in Batch No: <input
                            style="border:none; font-weight: bold;" type="text" id="item_name1" name="item_name"
                            value="{{ $batch_no }}" readonly></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    @foreach ($lines as $l)
                    @if($l->type != 'Output' )
                    <div class="row">
                        <div hidden class="col-md-3">
                            <label>Item Code</label>
                            <input type="text" class="form-control" id="item_code" value="{{ $l->item_code }}"
                                name="item_code[]" placeholder="" readonly>
                        </div>
                        <div class="col-md-4">
                            <label>Item </label>
                            <input type="text" class="form-control" id="item"
                                value="{{ $l->item_code.' - '.$l->description }}" name="item" placeholder="" readonly>
                        </div>
                        <div class="col-md-2">
                            <label>Std Qty</label>
                            <input type="number" class="form-control" id="std_qty" value="{{ $l->quantity }}"
                                name="std_qty[]" step="0.01" readonly placeholder="">
                        </div>
                        <div class="col-md-2">
                            <label>Used Qty</label>
                            <input type="number" class="form-control qty_used" id="qty" value="{{ $l->quantity }}"
                                name="qty[]" step="0.01" onClick="this.select();" placeholder="">
                        </div>
                        <div class="col-md-4">
                            <label>Type</label>
                            <input type="text" class="form-control" id="type" value="{{ $l->type }}" name="type"
                                placeholder="" readonly>
                        </div>
                    </div>
                    @endif
                    @endforeach
                    <hr>
                    <div class="row form-group">
                        {{-- <div class="col-md-4">
                            <button type="button" onclick="calculateTotal()" id="calculate-btn" value=""
                                class="btn btn-primary btn-lg"><i class="fas fa-calculator"></i> Calculate
                                Total</button>
                        </div> --}}
                        <div class="col-md-8">
                            <div class="row">
                                <label for="inputEmail3" class="col-sm-3 col-form-label">Total Output Qty</label>
                                <div class="col-sm-6">
                                    <input type="number" class="form-control" id="total_output" name="total_output"
                                        value="" placeholder="" required readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" onclick="return validateOnSubmit()"
                        class="btn btn-primary btn-lg btn-prevent-multiple-submits"><i
                            class="fa fa-paper-plane single-click" aria-hidden="true"></i> Update</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!--Edit Modal -->

<!--Close Modal -->
<div class="modal fade" id="closeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-close-post" class="form-prevent-multiple-submits" action="{{ route('chopping_close_batch') }}"
            method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Close batch for @foreach ($lines as $l )
                        @if ($loop->last) <input type="text" hidden value="{{ $l->item_code }}" name="main_item"> <strong>{{ $l->description }} </strong>@endif @endforeach in Batch No: <input
                            style="border:none; font-weight: bold;" type="text" id="item_name1" name="item_name"
                            value="{{ $batch_no }}" readonly></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><i> Close Batch by entering end of batch no below</i></p>
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">From Batch No: </label>
                        <div class="col-sm-9">
                            <input type="number" readonly class="form-control" id="from_batch" name="from_batch" value="{{ $from_batch }}">
                        </div>
                    </div>
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">To Batch No: </label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="to_batch" name="to_batch" value=""
                                placeholder="" min="1" step="1" required>
                        </div>
                    </div>
                    <input type="hidden" value="{{ $batch_no }}" name="batch_no" id="batch_no">
                    <input type="hidden" value="close" name="filter" id="filter">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" onclick="return validateCloseOnSubmit()" class="btn btn-warning btn-lg btn-prevent-multiple-submits"><i
                            class="fa fa-paper-plane single-click" aria-hidden="true"></i> Close Batch</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!--Close Modal -->

<!--Post Modal -->
<div class="modal fade" id="postModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-close-post" class="form-prevent-multiple-submits" action="{{ route('chopping_close_batch') }}"
            method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Post Batch Items for @foreach ($lines as $l )
                        @if ($loop->last) <strong>{{ $l->description }} </strong>@endif @endforeach in Batch No: <input
                            style="border:none; font-weight: bold;" type="text" id="item_name1" name="item_name"
                            value="{{ $batch_no }}" readonly></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <strong><em> Please Confirm you want to Post this Batch</em></strong>
                    <input type="hidden" value="{{ $batch_no }}" name="batch_no" id="batch_no">
                    <input type="hidden" value="post" name="filter" id="filter">
                    @foreach($lines as $data)
                    @if (str_starts_with($data->item_code, 'H'))
                    <input type="" readonly value="{{ $data->item_code.':' }} {{ $data->quantity }}" name="item_array[]"
                        id="item_array">
                    @else
                    @endif
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger btn-lg btn-prevent-multiple-submits"><i
                            class="fa fa-paper-plane single-click" aria-hidden="true"></i> Post Batch</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!--Post Modal -->

@endsection

@section('scripts')
<script>
    $(document).ready(function () {

        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        $('.qty_used').on("input", function () {
            calculateTotal()
        });

    });

    const editModal = () => {

        var item = $('#item_name1').val();

        $('#item_name3').val(item);

        $('#editModal').modal('show');
        calculateTotal()
    }

    const calculateTotal = () => {
        let inputs = document.getElementsByClassName('qty_used');
        let total = 0;

        for (let i = 0; i < inputs.length; i++) {
            total += parseFloat(inputs[i].value);
        }

        $('#total_output').val(total)
    }

    const validateOnSubmit = () => {
        let valid = false

        // Get the input value
        let inputVal = $('#total_output').val();

        // Check if it's a number
        if (!inputVal == '') {
            // It's a number
            valid = true
        } else {
            // It's not a number
            alert('The output total value must be valid');
        }

        return valid;
    }

    const validateCloseOnSubmit = () => {
        let valid = false

        // Get the input value
        let start_batch = parseInt($('#from_batch').val());
        let close_batch = parseInt($('#to_batch').val());

        // Check close batch is bigger than start batch
        if (close_batch > start_batch) {
            valid = true            
        } else {
            alert('The value of to batch number must be bigger than start batch ')
        }

        return valid;
    }

</script>
@endsection
