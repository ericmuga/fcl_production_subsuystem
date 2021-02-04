@extends('layouts.slaughter_master')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Slaughter Data Report| <span id="subtext-h1-title"><small> showing all entries</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <table id="example1" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Receipt No.</th>
                            <th>Slapmark </th>
                            <th>Carcass Code </th>
                            <th>Carcass Type</th>
                            <th>Net weight (kgs)</th>
                            <th>Meat %</th>
                            <th>Classification</th>
                            <th>Slaughter Date</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Receipt No.</th>
                            <th>Slapmark </th>
                            <th>Carcass Code </th>
                            <th>Carcass Type</th>
                            <th>Net weight (kgs)</th>
                            <th>Meat %</th>
                            <th>Classification</th>
                            <th>Slaughter Date</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach($slaughter_data as $data)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>{{ $data->receipt_no }}</td>
                            <td>{{ $data->slapmark }}</td>
                            <td>{{ $data->item_code }}</td>
                            <td>{{ $data->description }}</td>
                            <td>{{ number_format($data->net_weight, 2) }}</td>
                            <td>{{ $data->meat_percent }}</td>
                            <td>{{ $data->classification_code }}</td>
                            <td>{{ $data->created_at }}</td>
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
