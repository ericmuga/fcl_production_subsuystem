@extends('layouts.butchery_master')

@section('content')

<!-- Edit Modal product-processes -->
<div id="addProductProcessModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('butchery_products_add') }}" class="form-prevent-multiple-submits" method="post"
                    id="add-branch-form">
                    @csrf
                    <div class="card-body">
                        <div class="row form-group">
                            <div class="col-md-8">
                                <label for="role_name">Item :</label>
                                <select class="form-control select2 load_processes" name="product" id="product"
                                    required>
                                    <option value="" selected disabled>Select product</option>
                                    @foreach($products_list as $data)
                                    <option value="{{trim($data->id.'-'.$data->code)}}">
                                        {{$data->code .' '.$data->description}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="role_name">Product Type:</label>
                                <select name="product_type" id="product_type"
                                    class="form-control select2 load_processes" required autofocus>
                                    <option value="" selected disabled>Choose One</option>
                                    <option value="1">Main Product</option>
                                    <option value="2">By Product</option>
                                    <option value="3">Intake</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <h5>Production Process: <code>**select one process per Product Type</code></h5><br>
                            <div id="loading" class="collapse">
                                <div class="row d-flex justify-content-center">
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div><br>

                            <div class="row">
                                @foreach($processes as $p)
                                <div class="col-md-4">
                                    <label class="checkbox-inline">
                                        <input class="check_group" type="checkbox" id="process_code"
                                            name="process_code[]" value="{{ $p->process_code }}"> {{ $p->process }}
                                    </label>
                                </div>
                                @endforeach

                                @error('process_id')
                                <div class="error alert alert-danger alert-dismissible fade show">{{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" onclick="return validateOnSubmit()"
                            class="btn btn-primary btn-prevent-multiple-submits btn-lg float-right"><i
                                class="fa fa-paper-plane" aria-hidden="true"></i> Add/Update
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
                    <input style="border:none; font-weight: bold" type="text" id="item_name" name="item_name" value=""
                        readonly>
                </div>
                <input type="hidden" name="item_id" id="item_id" value="">
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-flat " type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-lg  float-right"><i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- end delete -->

@if( Session::get('session_userName') == 'EKaranja' || Session::get('session_userName') == 'LGithinji' ||
Session::get('session_userName') == 'AMugumo' ||
Session::get('session_userName') == 'EMuga')
<div class="div">
    <button type="button" class="btn btn-primary" id="addProductProcessModalShow" data-toggle="tooltip" title="add"><i
            class="fas fa-plus"> Add Product Process</i>
    </button> <br><br>
</div>
@endif

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
                                    Session::get('session_userName') == 'AMugumo' || Session::get('session_userName') ==
                                    'LGithinji' ||
                                    Session::get('session_userName') == 'EMuga')

                                    <button type="button" data-id="{{ $data->id }}"
                                        data-desc="{{ trim($data->description).' for '.trim($data->process) }}"
                                        class="btn btn-danger btn-xs" id="deleteProductsModalShow" data-toggle="tooltip"
                                        title="delete"><i class="fas fa-trash"></i>
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

@if(Session::get('input_errors') == 'add_productProcess' )
<script>
    $(function () {
        $('#addProductProcessModal').modal('show');
    });

</script>
@endif

<script>
    $(document).ready(function () {
        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        // modify product-process
        $("body").on("click", "#addProductProcessModalShow", function (e) {
            e.preventDefault();

            $('#addProductProcessModal').modal('show');
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

        $('.load_processes').change(function () {

            if ($('#product').val() != null && $('#product_type').val() != null) {
                let product = $('#product').val()
                let product_id = product.substring(0, product.indexOf("-"));
                let product_type = $('#product_type').val()

                loadProductProcesses(product_id, product_type)
            }
        });
    })

    const validateOnSubmit = () => {
        if ($('.check_group').filter(':checked').length < 1) {
            alert("Please Check at least one process Box");
            return false;
        }
    }

    const loadProductProcesses = (prod_id, prod_type) => {
        $('#loading').collapse('show'); 

        const url = '/products/processes_ajax/edit'

        const request_data = {
            product_id: prod_id,
            product_type: prod_type,
        }

        return axios.post(url, request_data)
            .then((response) => {
                $('#loading').collapse('hide'); 

                let data = response.data
                data.forEach(element => {

                    $("input[type=checkbox][value=" + element.process_code + "]").prop("checked", true);
                    $("input[type=checkbox][value !=" + element.process_code + "]").prop("checked",
                        false);

                });
            })
            .catch((error) => {
                console.log(error);
            })
    }

</script>
@endsection
