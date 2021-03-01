@extends('layouts.butchery_master')

@section('content')

{{-- <div class="div">
    <button class="btn btn-primary " data-toggle="collapse" data-target="#add_product"><i class="fa fa-plus"></i> Add
        New Product</button> <br> <br>
</div> --}}

<!-- create product-->
<div id="add_product" class="collapse">
    <div class="form-inputs">
        <div class="row">
            <div class="col-lg-8" style="margin: 0 auto; float: none;">
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="fa fa-users"></i>
                        Add Product</div>
                    <div class="card-body">
                        <form action="{{ route('butchery_add_product') }}" method="post" id="add-branch-form">
                            @csrf
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
                            </div>
                            <div class="form-group">
                                <label for="role_name">Product Type:</label>
                                <select name="product_type" id="product_type" class="form-control" required autofocus>
                                    <option value="" selected disabled>Choose One</option>
                                    <option value="Main Product">Main Product</option>
                                    <option value="By Product">By Product</option>
                                    <option value="Intake">Intake</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="role_name">Input Type:</label>
                                <input autocomplete="off" type="text" class="form-control" id="input_type"
                                    name="input_type" value="{{ old('product') }}" placeholder="eg. sow, baconer, leg "
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="role_name"> Often used:</label>
                                <select name="often" id="often" class="form-control" required autofocus>
                                    <option value="" selected disabled>Choose One</option>
                                    <option value="Yes">Yes </option>
                                    <option value="No">No </option>
                                </select>
                            </div>
                            <br>
                            <div>
                                <button type="submit" class="btn btn-primary btn-lg float-right"><i
                                        class="fa fa-paper-plane" aria-hidden="true"></i> Save
                                </button>
                            </div>

                        </form>
                    </div>
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
                            <th>Production Process(es)</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Production Process(es)</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach($products as $data)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $data->code }}</td>
                            <td>{{ $data->description }}</td>
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

    });

</script>
@endif
@endsection
