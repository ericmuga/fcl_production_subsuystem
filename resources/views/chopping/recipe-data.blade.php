@extends('layouts.spices_master')

@section('content')

@section('content-header')
<div class="container-fluid">
    <div class="row ml-2">
        <div class="col-md-6">
            <h3 class="m-0"> {{ $title }} |<small> Showing all entries </small></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('recipe_upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Upload Excel File</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="excelFile">Select Excel File</label>
                        <input type="file" name="excel_file" id="excelFile" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Items Table -->
<div class="card m-4 p-4">
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-toggle="modal" data-target="#uploadModal">Upload Excel</button>
    </div>
    <div class="table-responsive">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Process</th>
                    <th scope="col">Output Item</th>
                    <th scope="col">Recipe</th>
                    <th scope="col">Output Item Description</th>
                    <th scope="col">Output Item UOM</th>
                    <th scope="col">Batch Size</th>
                    <th scope="col">Output Item Location</th>
                    <th scope="col">Input Item</th>
                    <th scope="col">Input Item Description</th>
                    <th scope="col">Input Item UOM</th>
                    <th scope="col">Input Item Quantity Per</th>
                    <th scope="col">Input Item Location</th>
                    <th scope="col">Process Code</th>
                    <th scope="col">No Series</th>
                    <th scope="col">Routing</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lines as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->Process }}</td>
                        <td>{{ $item->output_item }}</td>
                        <td>{{ $item->recipe }}</td>
                        <td>{{ $item->output_item_dec }}</td>
                        <td>{{ $item->output_item_uom }}</td>
                        <td>{{ $item->batch_size }}</td>
                        <td>{{ $item->output_item_location }}</td>
                        <td>{{ $item->input_item }}</td>
                        <td>{{ $item->input_item_desc }}</td>
                        <td>{{ $item->input_item_uom }}</td>
                        <td>{{ $item->input_item_qt_per }}</td>
                        <td>{{ $item->input_item_location }}</td>
                        <td>{{ $item->process_code }}</td>
                        <td>{{ $item->no_series }}</td>
                        <td>{{ $item->routing }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Process</th>
                    <th scope="col">Output Item</th>
                    <th scope="col">Recipe</th>
                    <th scope="col">Output Item Description</th>
                    <th scope="col">Output Item UOM</th>
                    <th scope="col">Batch Size</th>
                    <th scope="col">Output Item Location</th>
                    <th scope="col">Input Item</th>
                    <th scope="col">Input Item Description</th>
                    <th scope="col">Input Item UOM</th>
                    <th scope="col">Input Item Quantity Per</th>
                    <th scope="col">Input Item Location</th>
                    <th scope="col">Process Code</th>
                    <th scope="col">No Series</th>
                    <th scope="col">Routing</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>


@endsection
