@extends('layouts.spices_master')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"> Production Lines Entries | <span id="subtext-h1-title"><small> showing batch
                            <strong>{{ $batch_no }}</strong> entries
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
                                <th>Item Code </th>
                                <th>Description </th>
                                <th>Percentage </th>
                                <th>Qty</th>
                                <th>Main Product</th>
                                <th>Type</th>
                                <th>Unit Measure</th>
                                <th>Location</th>
                                <th>Date</th>
                                <th>Action</th>
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
                                <th>Action</th>
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
        <!-- /.col -->
    </div>
</div>
<!-- slicing ouput data show -->

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
