@extends('layouts.spices_master')

@section('content')

<div class="row">
    <div class="col-md-7">
        <h3>Physical Stocks Registry </h3>
    </div>
    <div class="col-md-5">
        <button class="btn btn-primary" data-toggle="modal" data-target="#createItemModal"><i class="fa fa-plus"></i>
            New Physical Entry</button>
    </div>
</div><br>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item No</th>
                                <th>Desc</th>
                                <th>Unit Measure </th>
                                <th>Entry Type</th>
                                <th>Quantity</th>
                                <th>Created By</th>
                                <th>Date</th>                                
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Item No</th>
                                <th>Desc</th>
                                <th>Unit Measure </th>
                                <th>Entry Type</th>
                                <th>Quantity</th>
                                <th>Created By</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($lines as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->item_code }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ $data->unit_measure }}</td>
                                @if ($data->status == 1)
                                    <td><span class="badge badge-info">raw</span></td>
                                @else
                                    <td><span class="badge badge-success">post</span></td>
                                @endif        
                                <td>{{ $data->quantity }}</td>
                                <td>{{ $data->user }}</td>                        
                                <td>{{ $helpers->amPmDate($data->created_at) }}</td>
                                <td></td>
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

<!-- Start create product Modal -->
<div id="createItemModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="create_item_form" class="form-prevent-multiple-submits" action="{{ route('add_physical_stock') }}"
                method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Create New Entry Entry&hellip;</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row form-group">
                            <div class="col-md-12 form-group">
                                <label for="exampleInputFullname">Item Code</label>
                                <select class="form-control select2" name="item_code" id="item_code" >
                                    <option value="" selected="selected"></option>
                                    @foreach($items as $data)
                                    <option value="{{ $data->code }}">{{ $data->code }} {{ $data->description }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-md-12">
                                <label for="exampleInputIdNumber">Qty</label>
                                <input type="number" name="quantity" class="form-control input_checks" id="quantity"
                                    placeholder="" value="">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" id="newItem"
                            class="btn btn-primary btn-lg btn-prevent-multiple-submits"><i class="fa fa-paper-plane"
                                aria-hidden="true"></i>
                            Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End create product modal-->
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        $(".input_checks").on("change keyup", function (a) {
            a.preventDefault();
            $(".btn-prevent-multiple-submits").attr('disabled', false);
        });

        $('#create_item_form').validate({
            rules: {
                item_code: {
                    required: true,
                },
                quantity: {
                    required: true,
                    digits: true,
                },
            },
            messages: {
                item_code: {
                    required: "Please select an item",                    
                },
                quantity: {
                    required: "Please enter qty",
                    digits: "Your qty must contain numbers only",
                },
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });
    });
</script>
    
@endsection
