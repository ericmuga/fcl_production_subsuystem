@extends('layouts.butchery_master')

@section('content')

<!-- Edit Modal product-processes -->
<div id="editProductsModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('butchery_add_product') }}" method="post" id="add-branch-form">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="role_name">Item Code:</label>
                            <input autocomplete="off" type="text" class="form-control" id="code" name="code"
                                value="{{ old('code') }}" required>

                            @error('code')
                            <div class="error alert alert-danger alert-dismissible fade show">{{ $message }}
                            </div>
                            @enderror

                        </div>
                        <div class="form-group">
                            <label for="role_name">Product Name:</label>
                            <input autocomplete="off" type="text" class="form-control" id="product" name="product"
                                value="{{ old('product') }}" required>

                            @error('product')
                            <div class="error alert alert-danger alert-dismissible fade show">{{ $message }}
                            </div>
                            @enderror

                        </div>
                        <div class="form-group">
                            <label for="role_name">Product Type:</label>
                            <select name="product_type" id="product_type" class="form-control" required autofocus>
                                <option value="" selected disabled>Choose One</option>
                                <option value="1">Main Product</option>
                                <option value="2">By Product</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="role_name">Production Process: <code>**select all applicable</code></label>
                            <select class="select2" multiple="multiple" data-placeholder="Select Production Process(es)"
                                name="production_process[]" id="production_process">

                            </select>

                            @error('production_process')
                            <div class="error alert alert-danger alert-dismissible fade show">{{ $message }}
                            </div>
                            @enderror

                        </div>
                    </div>
                    <div class="card-footer ">
                        <button type="submit" class="btn btn-primary btn-lg float-right"><i class="fa fa-paper-plane"
                                aria-hidden="true"></i> Add/Update
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary">Save changes</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!--End edit Modal product-processes -->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Deboning Products Registry </h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Shortcode</th>
                                <th>Product Code</th>
                                <th>Product Name</th>
                                <th>Product Type</th>
                                <th>Production Code</th>
                                <th>Production Process</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Shortcode</th>
                                <th>Product Code</th>
                                <th>Product Name</th>
                                <th>Product Type</th>
                                <th>Production Code</th>
                                <th>Production Process</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($products as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->shortcode . substr($data->code, strpos($data->code, "G") + 1) }}
                                </td>
                                <td>{{ $data->code }}</td>
                                <td>{{ $data->description }}</td>
                                @if ($data->product_type == 1)
                                <td> Main Product</td>

                                @elseif($data->product_type == 2)
                                <td> By Product</td>

                                @else
                                <td> Intake</td>
                                @endif

                                <td>{{ $data->process_code }}</td>
                                <td>{{ $data->process }}</td>
                                <td>
                                    <button type="button" data-id="{{ $data->code }}" class="btn btn-info btn-xs"
                                        id="editProductsModalShow" data-toggle="tooltip" title="edit"><i
                                            class="fas fa-edit"></i>
                                    </button>
                                </td>
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
@endsection

@section('scripts')
<script>
    $(document).ready(function () {

        loadProductProcesses();

        // edit product-process
        $("body").on("click", "#editProductsModalShow", function (a) {
            a.preventDefault();

            var id = $(this).data('id');


            $('#return_item_id').val(id);

            $('#editProductsModal').modal('show');
        });
    })

    const loadProductProcesses = () => {
        $.ajax({
            type: "GET",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                    .attr('content')
            },
            url: "{{ url('products/processes_ajax') }}",
            dataType: 'JSON',
            success: function (data) {
                // console.log(data);
                var formOptions = "";
                for (var key in data) {
                    var process_code = data[key].process_code;

                    var process_name = data[key].process;

                    formOptions += "<option value='" +
                        process_code + "'>" + process_name +
                        "</option>";
                }

                $('#production_process').html(formOptions);

            },
            error: function (data) {
                var errors = data.responseJSON;
                // console.log(errors);
                alert(
                    'error occured when pulling production processes'
                );
            }

        });
    }

</script>
@endsection
