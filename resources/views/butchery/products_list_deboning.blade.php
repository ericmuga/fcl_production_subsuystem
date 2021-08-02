@extends('layouts.butchery_master')

@section('content')

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
