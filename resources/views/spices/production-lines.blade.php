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
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editModal"><i class="fas fa-pencil-alt"></i>
                            Edit Batch 
                        </button>
                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#closeModal"><i
                                class="fas fa-lock"></i>
                            Close Batch
                        </button>

                        @elseif ($lines->first()->status == 'closed')   
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#exampleModal"><i
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
                                <td>{{ $data->percentage }}</td>
                                <td>{{ $data->quantity }}</td>

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
    <div class="modal-dialog" role="document">
        <form id="form-close-post" class="form-prevent-multiple-submits" action="#"
            method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Items in Batch No: <strong><input style="border:none"
                                type="text" id="item_name" name="item_name" value="" readonly></strong></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <strong><em> please wait, we are working on this part</em></strong>
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
<!--Close Modal -->

<!--Close Modal -->
<div class="modal fade" id="closeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-close-post" class="form-prevent-multiple-submits" action="#"
            method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Close Batch No: <strong><input style="border:none"
                                type="text" id="item_name" name="item_name" value="" readonly></strong></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <strong><em> please wait, we are working on this part</em></strong>
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
<!--Edit Modal -->

@endsection

@section('scripts')
<script>
    $(document).ready(function () {

        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        $("body").on("click", "#itemCodeModalShow", function (e) {
            e.preventDefault();

            var code = $(this).data('code');
            var item = $(this).data('item');
            var weight = $(this).data('weight');
            var no_of_pieces = $(this).data('no_of_pieces');
            var id = $(this).data('id');
            var process_code = $(this).data('production_process');
            var type_id = $(this).data('type_id');

            $('#edit_product').val(code);
            $('#item_name').val(item);
            $('#edit_weight').val(weight);
            $('#edit_no_pieces').val(no_of_pieces)
            $('#item_id').val(id);
            $('#edit_production_process').val(process_code);
            $('#edit_product_type2').val(type_id);

            $('#edit_product').select2('destroy').select2();

            $('#itemCodeModal').modal('show');
        });
    });

</script>
@endsection
