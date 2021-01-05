@extends('layouts.butchery_master')

@section('content')

<div class="div">
    <button class="btn btn-primary " data-toggle="collapse" data-target="#add_product"><i class="fa fa-plus"></i> Add
        New Product</button> <br> <br>
</div>

<!-- create product-->
<div id="add_product" class="collapse"><br>
    <div class="form-inputs">
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="fa fa-users"></i>
                        Add Product</div>
                    <div class="card-body">
                        <form action="#" method="post" id="add-branch-form">
                            @csrf
                            <div class="form-group">
                                <label for="role_name">Item Code:</label>
                                <input max="50" min="3" pattern="([A-Za-z\s0-9-]+)" autocomplete="off" type="text"
                                    class="form-control" id="centre" name="centre" value="{{ old('name') }}"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="role_name">Product Name:</label>
                                <input max="50" min="3" pattern="([A-Za-z\s0-9-]+)" autocomplete="off" type="text"
                                    class="form-control" id="centre" name="centre" value="{{ old('name') }}"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="role_name">Product Type:</label>
                                <select name="route_id" id="route_id" class="form-control" required
                                    autofocus>
                                    <option value="" selected disabled>Choose One</option>
                                    {{-- @foreach($routes as $route)
                                    <option value="{{$route->id}}">
                                        {{ ucwords($route->route_name) }}
                                    </option>
                                    @endforeach --}}
                                    <option value="1">Main Product</option>
                                    <option value="2">By Product</option>
                                    <option value="3">Intake</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="role_name"> Often used:</label>
                                <select name="route_id" id="route_id" class="form-control" required
                                    autofocus>
                                    <option value="" selected disabled>Choose One</option>
                                    {{-- @foreach($routes as $route)
                                    <option value="{{$route->id}}">
                                        {{ ucwords($route->route_name) }}
                                    </option>
                                    @endforeach --}}
                                    <option value="1">Yes </option>
                                    <option value="2">No </option>
                                </select>
                            </div>
                            <br>
                            <div>
                                <button type="submit" class="btn btn-success float-right"><i class="fa fa-paper-plane"
                                    aria-hidden="true"></i> Save
                                </button>
                            </div>

                        </form>
                        @if(count($errors))
                        <ol>
                            <h6><span class="label label-danger">Errors</span></h6>
                            @foreach($errors->all() as $error)
                            <li> <code>{{$error}}</code></li>
                            @endforeach
                        </ol>
                        @endif
                        <br>
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
                <h3 class="card-title"></h3>
                <h3 class="card-title"> Products Entries | <span id="subtext-h1-title"><small> view, add, edit products</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <table id="example1" class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Product Type</th>
                            <th>Barcode Id</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Product Type</th>
                            <th>Barcode Id</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td> G1229</td>
                            <td> Hocks (Lean Pork)</td>
                            <td> By Product</td>
                            <td> 2A011243</td>
                        </tr>
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
