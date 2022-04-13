@extends('layouts.butchery_master')

@section('content')

<!-- Edit Modal product-processes -->
<div id="editProductsModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
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
                        <div class="row form-group">
                            <div class="col-md-4">
                                <label for="role_name">Item Code:</label>
                                <input autocomplete="off" type="text" class="form-control" id="code" name="code"
                                    value="" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="role_name">Product Name:</label>
                                <input autocomplete="off" type="text" class="form-control" id="product" name="product"
                                    value="" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="role_name">Product Type:</label>
                                <input autocomplete="off" type="text" class="form-control" id="product_type" name="product_type"
                                    value="" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <h5>Production Process: <code>**select all applicable</code></h5><br>
                            {{-- <select class="select2" multiple="multiple" data-placeholder="Select Production Process(es)"
                                name="production_process[]" id="production_process">
                            </select> --}}
                            <div class="row">
                                @foreach($processes as $p)
                                <div class="col-md-4">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" id="process_id" name="process_id[]"
                                            value="{{ $p->process_code }}"> {{ $p->process }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-lg float-right"><i class="fa fa-paper-plane"
                                aria-hidden="true"></i> Add/Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End edit Modal product-processes -->

<!-- delete modal -->
<div id="deleteProductsModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('butchery_products_delete') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete Item: </h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Please confirm you want to delete this item.
                    <input style="border:none; font-weight: bold"
                                type="text" id="item_name" name="item_name" value="" readonly>
                </div>
                <input type="hidden" name="item_id" id="item_id" value="">
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-flat " type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit"
                        class="btn btn-danger btn-lg  float-right"><i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- end delete -->

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
                                <th>Product Name</th>
                                <th>Product Type</th>
                                <th>Production Process</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Shortcode</th>
                                <th>Product Name</th>
                                <th>Product Type</th>
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
                                <td>{{ $data->description }}</td>
                                @if ($data->product_type == 1)
                                <td> Main Product</td>

                                @elseif($data->product_type == 2)
                                <td> By Product</td>

                                @else
                                <td> Intake</td>
                                @endif

                                <td>{{ $data->process }}</td>
                                <td>
                                    @if( Session::get('session_userName') == 'EKaranja' ||
                                    Session::get('session_userName') == 'AMugumo' || Session::get('session_userName') == 'LGithinji' ||
                                    Session::get('session_userName') == 'EMuga')
                                    
                                    <button type="button" data-id="{{ $data->id }}" data-desc="{{ trim($data->description).' for '.trim($data->process) }}" class="btn btn-danger btn-xs"
                                        id="deleteProductsModalShow" data-toggle="tooltip" title="delete"><i
                                            class="fas fa-trash"></i>
                                    </button>

                                    @endif
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

        // edit product-process
        $("body").on("click", "#editProductProcessModalShow", function (e) {
            e.preventDefault();

            var id = $(this).data('id');
            var code = $(this).data('code');
            var type = $(this).data('product_type');
            var product_id = $(this).data('product_id');
            var product = $(this).data('desc');

            loadProductProcesses(product_id, type);

            switch(type) {
                case 1:
                    // code block
                    type = 'Main Product'
                    break;
                case 2:
                    // code block
                    type = 'By Product'
                    break;
                case 3:
                    // code block
                    type = 'Intake'
                    break;
                default:
                    type = "Main Product"
            }


            $('#return_item_id').val(id);
            $('#code').val(code);
            $('#product').val(product);
            $('#product_type').val(type);

            $('#editProductsModal').modal('show');
        });

        // delete product-process
        $("body").on("click", "#deleteProductsModalShow", function (e) {
            e.preventDefault();

            var id = $(this).data('id');
            var name = $(this).data('desc');

            $('#item_id').val(id);
            $('#item_name').val(name);

            $('#deleteProductsModal').modal('show');
        });
    })

    const loadProductProcesses = (product_id, type) => {
        const url = '/products/processes_ajax/edit'

        const request_data = {
            product_id: product_id,
            type: type,
        }

        return axios.post(url, request_data)
            .then((response) => {
                // console.log(response.data)
                let data = response.data
                data.forEach(element => {
                    console.log(element.process_code)
                    
                    $("input[type=checkbox][value="+element.process_code+"]").prop("checked", true);
                    $("input[type=checkbox][value !="+element.process_code+"]").prop("checked", false);
                    
                });
            })
            .catch((error) => {
                console.log(error);
            })
    }

</script>
@endsection
