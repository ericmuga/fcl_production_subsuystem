@extends('layouts.butchery_master')

@section('content')

<div class="div">
    <button class="btn btn-primary add_product"  onclick="isCollapsed()"
        ><i class="fa fa-plus"></i> Add/Update
        Product</button> <br> <br>
</div>

<!-- create product-->
<div id="add_product" class="collapse">
    <div class="form-inputs">
        <div class="row">
            <div class="col-lg-8" style="margin: 0 auto; float: none;">
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="fa fa-plus"></i>
                        Add/Update Product</div>
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
                                <select class="select2" multiple="multiple" data-placeholder="Select Production Process(es)" name="production_process[]" id="production_process">

                                </select>

                                @error('production_process')
                                <div class="error alert alert-danger alert-dismissible fade show">{{ $message }}
                                </div>
                                @enderror

                            </div>
                        </div>
                        <div class="card-footer ">
                            <button type="submit" class="btn btn-primary btn-lg float-right"><i
                                    class="fa fa-paper-plane" aria-hidden="true"></i> Add/Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!--End create product-->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"> Products Entries | <span id="subtext-h1-title"><small> view, add, edit
                            products</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <table id="example1" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Product Type</th>
                            <th>Production Process(es)</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Product Type</th>
                            <th>Production Process(es)</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach($products as $data)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $data->code }}</td>
                            <td>{{ $data->description }}</td>
                            @if ($data->product_type == 1)
                                <td> Main Product</td>
                            @else
                                <td> By Product</td>
                            @endif
                            
                            <td> @foreach ($helpers->getProductProcesses($data->id) as $process)
                                <span class="badge badge-info">{{ $process }}</span>

                                @endforeach </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>
@endsection

@section('scripts')
@if(Session::get('input_errors') == 'add_product' )
<script>
    $(function () {
        // $('#myModal').modal('show');
        $('#add_product').toggle('collapse');

        // pull production processes
        loadProductProcesses();

    });

</script>
@endif

<script>
    function isCollapsed() {
        $('#add_product').toggle('collapse');

        $('#code').focus();

        var isExpanded = $(".add_product").toggle($("#add_product").is(':visible'));
        if (isExpanded) {

            // pull production processes
            loadProductProcesses();
        }
    }

    function loadProductProcesses() {
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
