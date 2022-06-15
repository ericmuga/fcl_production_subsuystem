@extends('layouts.spices_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"> {{ $title }} |<small>Production Lines </small></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<div class="div">
    <button class="btn btn-primary " data-toggle="collapse" data-target="#toggle_collapse"><i class="fas fa-plus"></i>
        Create
        New Batch</button>
</div>
<hr>

<div id="toggle_collapse" class="collapse">
    <form id="form-save-batch" class="form-prevent-multiple-submits" action="{{ route('batches_create') }}"
        method="post">
        @csrf
        <div class="card-group">
            <div class="card">
                <div class="card-body" style="">
                    <div class="form-group">
                        <div class="row">
                            <label for="inputEmail3" class="col-sm-3 col-form-label">Template No</label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="temp_no" id="temp_no" required>
                                    <option value="" disabled>Select template</option>
                                    @foreach($templates as $tm)
                                    <option value="{{ $tm->template_no }}">
                                        {{ $tm->template_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div><br>
                        <div class="row">
                            <label for="inputEmail3" class="col-sm-3 col-form-label">Batch No</label>
                            <div class="col-sm-9">
                                <input type="number" value="{{ time() }}" class="form-control" id="batch_no" name="batch_no" placeholder="" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body form-group">
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">Output Qty</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="output_qty" name="output_qty" value="" placeholder="" required>
                        </div>
                    </div><br>
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">Status</label>
                        <div class="col-sm-9">
                            <input type="email" readonly class="form-control" value="Open" id="status" name="status" placeholder="" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body text-center">
                    <div class="form-group" style="padding-top: 5%">
                        <button type="submit" onclick="return checkNetOnSubmit()"
                            class="btn btn-primary btn-lg btn-prevent-multiple-submits"><i
                                class="fa fa-paper-plane single-click" aria-hidden="true"></i> Run</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div><br>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"> Production Lines Entries | <span id="subtext-h1-title"><small> showing all
                            <strong>{{ $status }}</strong> entries
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
                                <th>Template</th>
                                <th>Batch No</th>
                                <th>Item Code </th>
                                <th>Description </th>
                                <th>Unit Measure</th>
                                <th>Qty</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>created By</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Template</th>
                                <th>Batch No</th>
                                <th>Item Code </th>
                                <th>Description </th>
                                <th>Unit Measure</th>
                                <th>Qty</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>created By</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($lines as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
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
