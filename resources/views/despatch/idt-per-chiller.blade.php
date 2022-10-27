@extends('layouts.despatch_master')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">IDT Per Chiller Location Report | showing <strong>All</strong> Entries</h3> 
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table table-striped table-bordered table-hover" style="scroll-behavior: smooth;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Code</th>
                                <th>Product</th>
                                <th>Total Pieces</th>
                                <th>Total Weight</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Product Code</th>
                                <th>Product</th>
                                <th>Total Pieces</th>
                                <th>Total Weight</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($items as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->product_code }}</td>
                                <td>{{ $data->product }}</td>
                                <td>{{ number_format($data->received_pieces, 1) }}</td>
                                <td>{{ number_format($data->received_weight, 1) }}</td>
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

