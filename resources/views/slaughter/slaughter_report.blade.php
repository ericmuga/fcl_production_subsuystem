@extends('layouts.slaughter_master')

@section('content')
<div class="col-md-12 text-left" style="margin-bottom: 1%">
    <button class="btn btn-success btn-lg" data-toggle="collapse" data-target="#export_data"><i class="fas fa-file-excel"></i> Generate Combined Report</button>
        <div id="export_data" class="collapse"><br>
            <div class="form-inputs">
                <div class="row">
                    <div class="col-lg-6" style="margin: 0 auto; float: none;">
                        <div class="card mb-3">
                            <div class="card-header">
                                <i class="fa fa-user-secret"></i>
                                Export data</div>
                            <div class="card-body">
                                <form action="{{ url('/export-slaughter-combined-report') }}" method="post" id="export-logs-form">
                                    {{ csrf_field() }}
    
                                    <div class="row">
                                        *Filter by date<br>
                                        <div class="form-group col-md-6">
                                            <label for="stemplate_date_created_from_flagged">Date:(dd/mm/yyyy)</label>
                                            <input type="date" class="form-control" name="date"
                                                    id="stemplate_date_created_from_flagged" autofocus required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <button type="submit" class="btn btn-primary "><i class="fa fa-paper-plane" aria-hidden="true"></i> Export now</button>
                                        </div>
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
    </div>
</div>

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
                            <th>Weight(kgs)</th>
                            <th>Net weight(kgs)</th>
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
                            <th>Weight(kgs)</th>
                            <th>Net weight(kgs)</th>
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
                            <td>{{ number_format($data->actual_weight, 2) }}</td>
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
