@extends('layouts.butchery_stocks_master')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Butchery Stocks Registry | <small>showing todays Transactions</small></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-hover" style="scroll-behavior: smooth;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item Code</th>
                                <th>Item Name </th>
                                <th>Chiller </th>
                                <th>Location </th>
                                <th>Entry Type</th>
                                <th>Net Weight</th>
                                <th>Creater </th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Item Code</th>
                                <th>Item Name </th>
                                <th>Chiller </th>
                                <th>Location </th>
                                <th>Entry Type</th>
                                <th>Net Weight</th>
                                <th>Creater </th>
                                <th>Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($transactions as $data)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $data->item_code }}</td>
                                <td></td>
                                <td>{{ $data->chiller_code }}</td>
                                <td>{{ $data->location_code }}</td>
                                <td>{{ $data->entry_code }}</td>                                
                                <td>{{ number_format($data->net_weight, 2) }}</td>
                                <td>{{ $data->user_id }}</td>
                                <td>{{ $data->created_at }}</td>
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
