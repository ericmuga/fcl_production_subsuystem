@extends('layouts.slaughter_master')

@section('content')

<div class="div">
    <button class="btn btn-primary " data-toggle="collapse" data-target="#import_receipts"><i class="fas fa-file-excel"></i> Import
        New Receipts</button> <br> <br>
</div>

<!--End create product-->
<div id="import_receipts" class="row collapse">
    <div class="col-md-10" style="padding-left: 20%">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Import receipts</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form>
                <div class="card-body">
                    <div class="form-group">
                        <label for="exampleInputFile">File Upload</label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="exampleInputFile">
                                <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary float-right"><i class="fa fa-paper-plane"
                            aria-hidden="true"></i> Upload</button>
                    </div>
                </div>
                <!-- /.card-body -->
            </form> <br>
            @if(count($errors))
                <ol>
                    <h6><span class="label label-danger">Errors</span></h6>
                    @foreach($errors->all() as $error)
                        <li> <code>{{ $error }}</code></li>
                    @endforeach
                </ol>
            @endif
        </div>
    </div>
</div>
<!--End create product-->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"></h3>
                <h3 class="card-title"> Imported Receipts Entries | <span id="subtext-h1-title"><small> view, filter, print/download</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <table id="example1" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Receipt No</th>
                            <th>Vendor tag</th>
                            <th>Vendor Name</th>
                            <th> Code </th>
                            <th>Description </th>
                            <th>Qty Received</th>
                            <th>Slaughter Date</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Receipt No</th>
                            <th>Vendor tag</th>
                            <th>Vendor Name</th>
                            <th> Code </th>
                            <th>Description </th>
                            <th>Qty Received</th>
                            <th>Slaughter Date</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach($receipts as $data)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>{{ $data->receipt_no }}</td>
                            <td>{{ $data->vendor_tag }}</td>
                            <td>{{ $data->vendor_name }}</td>
                            <td>{{ $data->item_code}}</td>
                            <td>{{ $data->description }}</td>
                            <td>{{ $data->received_qty }}</td>
                            <td>{{ $helpers->dateToHumanFormat($data->slaughter_date) }}</td>
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
