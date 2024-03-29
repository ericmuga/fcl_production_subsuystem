@extends('layouts.slaughter_master')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"></h3>
                <h3 class="card-title"> Missing slapmarks Entries | <span id="subtext-h1-title"><small> view, filter,
                            print/download</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Slapmark</th>
                                <th>Carcass Code </th>
                                <th>Carcass Type</th>
                                <th>Net Weight(kgs)</th>
                                <th>Meat % </th>
                                <th>Classification </th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Slapmark</th>
                                <th>Carcass Code </th>
                                <th>Carcass Type</th>
                                <th>Net Weight(kgs)</th>
                                <th>Meat % </th>
                                <th>Classification </th>
                                <th>Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($slaps as $data)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $data->slapmark }}</td>
                                <td>{{ $data->item_code }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ number_format($data->net_weight, 2) }}</td>
                                <td>{{ $data->meat_percent}}</td>
                                <td>{{ $data->classification_code }}</td>
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
