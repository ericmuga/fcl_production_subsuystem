@extends('layouts.spices_master')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="card-title"> Production Lines Entries | <span id="subtext-h1-title">showing batch No:
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
                                <th>Batch No</th>
                                <th>Item Code </th>
                                <th>Description </th>
                                <th>Percentage </th>
                                <th>Qty</th>
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
                                <th>Batch No</th>
                                <th>Item Code </th>
                                <th>Description </th>
                                <th>Percentage </th>
                                <th>Qty</th>
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
                                <td>{{ $data->batch_no }}</td>
                                <td>{{ $data->item_code }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ number_format($data->percentage, 2) }}</td>
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
        <form id="form-close-post" class="form-prevent-multiple-submits" action="{{ route('update_batch') }}"
            method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Items for @foreach ($lines as $l )
                        @if ($loop->last) <strong>{{ $l->description }} </strong>@endif @endforeach in Batch No: <input
                            style="border:none; font-weight: bold;" type="text" id="item_name1" name="item_name"
                            value="{{ $batch_no }}" readonly></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    @foreach ($lines as $l)
                    <div class="row">
                        <div class="col-md-3">
                            <label>Item Code</label>
                            <input type="text" class="form-control" id="item_code" value="{{ $l->item_code }}"
                                name="item_code[]" placeholder="" readonly>
                        </div>
                        <div class="col-md-3">
                            <label>Item </label>
                            <input type="text" class="form-control" id="item" value="{{ $l->description }}"
                                name="item" placeholder="" readonly>
                        </div>
                        <div class="col-md-3">
                            <label>Qty</label>
                            <input type="number" class="form-control" id="qty" value="{{ $l->quantity }}" name="qty[]"
                                step="0.01" onClick="this.select();" placeholder="">
                        </div>
                        <div class="col-md-3">
                            <label>Type</label>
                            <input type="text" class="form-control" id="type" value="{{ $l->type }}" name="type"
                                placeholder="" readonly>
                        </div>
                    </div>
                    <hr>
                    {{-- <div class="form-group">
                        @if ($l->type == 'Output')
                        <div class="row">
                            <div class="col-md-6">
                                <label>Total Intake Qty</label>
                                <input type="number" class="form-control" id="total_intake" value="{{ $l->quantity }}"
                                    name="total_intake" placeholder="" readonly>
                            </div>
                            <div class="col-md-6">
                                <label>Total Output Qty</label>
                                <input type="number" class="form-control" id="total_output" value="{{ $l->quantity }}"
                                    name="total_output" placeholder="">

                            </div>
                        </div>
                        @endif
                    </div> --}}
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-lg btn-prevent-multiple-submits"><i
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
        <form id="form-close-post" class="form-prevent-multiple-submits" action="{{ route('close_post_batch') }}"
            method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Close batch for @foreach ($lines as $l )
                        @if ($loop->last) <strong>{{ $l->description }} </strong>@endif @endforeach in Batch No: <input
                            style="border:none; font-weight: bold;" type="text" id="item_name1" name="item_name"
                            value="{{ $batch_no }}" readonly></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <strong><em> Please Confirm you want to close this Batch</em></strong>
                    <input type="hidden" value="{{ $batch_no }}" name="batch_no" id="batch_no">
                    <input type="hidden" value="close" name="filter" id="filter">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning btn-lg btn-prevent-multiple-submits"><i
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
        <form id="form-close-post" class="form-prevent-multiple-submits" action="{{ route('close_post_batch') }}"
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
                            <input type="" value="{{ $data->item_code.':' }} {{ $data->quantity }}" name="item_array[]" id="item_array">  
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

    });

    const editModal = () => {

        var item = $('#item_name1').val();

        $('#item_name3').val(item);

        $('#editModal').modal('show');

    }

</script>
@endsection
