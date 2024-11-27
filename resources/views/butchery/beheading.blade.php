@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="">
            <h1 class="m-0"> Reports |<small> Pork Beheading Report</small></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<div class="col-md-12 text-left" style="margin-bottom: 1%">
    <button class="btn btn-success btn-lg" data-toggle="collapse" data-target="#export_data"><i
            class="fas fa-file-excel"></i> Generate Combined Report</button>
    <button class="btn btn-success btn-lg" data-toggle="modal" data-target="#export_lines">
        <i class="fas fa-file-excel"></i> Generate Lines Report
    </button>
    <div id="export_data" class="collapse"><br>
        <div class="form-inputs">
            <div class="row">
                <div class="col-lg-6" style="margin: 0 auto; float: none;">
                    <div class="card mb-3">
                        <div class="card-header">
                            <i class="fa fa-user-secret"></i>
                            Export data</div>
                        <div class="card-body">
                            <form action="{{ url('/export-beheading-combined-report') }}" method="post"
                                id="export-logs-form">
                                {{ csrf_field() }}

                                <h6>*Filter by date range</h6>
                                <div class="row form-group">
                                    <div class="col-md-6">
                                        <label for="stemplate_date_created_from_flagged">From: (dd/mm/yyyy)</label>
                                        <input type="date" class="form-control" name="from_date"
                                            id="stemplate_date_created_from_flagged" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="stemplate_date_created_from_flagged">To: (dd/mm/yyyy)</label>
                                        <input type="date" class="form-control" name="to_date"
                                            id="stemplate_date_created_from_flagged" required>
                                    </div>
                                </div> <br>
                                <div class="div" align="center">
                                    <button type="submit" class="btn btn-primary "><i class="fa fa-paper-plane"
                                            aria-hidden="true"></i> Export now</button>
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
    <div id="export_lines" class="modal">
        <div class="modal-dialog">
            <form class="modal-content" action="{{ route('export-beheading-lines-report') }}" method="post" id="export-logs-form">
                {{ csrf_field() }}
                <h3 class="modal-header">Export data</h3>
                <div class="modal-body">
                    <h6>*Filter by date range</h6>
                    <div class="row form-group">
                        <div class="col-md-6">
                            <label for="stemplate_date_created_from_flagged">From: (dd/mm/yyyy)</label>
                            <input type="date" class="form-control" name="from_date"
                                id="stemplate_date_created_from_flagged" required>
                        </div>
                        <div class="col-md-6">
                            <label for="stemplate_date_created_from_flagged">To: (dd/mm/yyyy)</label>
                            <input type="date" class="form-control" name="to_date"
                                id="stemplate_date_created_from_flagged" required>
                        </div>
                    </div>
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
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary ">
                        <i class="fa fa-paper-plane" aria-hidden="true"></i> Export now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"> showing Entries for last 7 days| <span id="subtext-h1-title"><small> filter, view, export</small>
            </span></h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
        <div class="hidden" hidden>{{ $i = 1 }}</div>
        <div class="table-responsive">
            <table id="example1" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code </th>
                        <th>No. of Carcass</th>
                        <th>Carcass </th>
                        <th>Production Process</th>
                        <th>Weight(kgs)</th>
                        <th>Net Weight(kgs)</th>
                        <th>Returned Entry</th>
                        <th>Date </th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>Code </th>
                        <th>No. of Carcass</th>
                        <th>Carcass </th>
                        <th>Production Process</th>
                        <th>Weight(kgs)</th>
                        <th>Net Weight(kgs)</th>
                        <th>Returned Entry</th>
                        <th>Date </th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach($beheading_data as $data)
                    <tr>
                        <td>{{ $i++ }}</td>
                        <td> {{ $data->item_code }}</td>
                        <td> {{ $data->no_of_carcass }}</td>
                        @if($data->item_code == 'G1035')
                            <td> Pig, Carcass-Side with Tail</td>
                        @else
                            <td> {{ $data->product_type }}</td>
                        @endif
                        <td> {{ $data->process }}</td>
                        <td> {{ number_format($data->actual_weight, 2) }}</td>
                        <td> {{ number_format($data->net_weight, 2) }}</td>
                        @if ($data->return_entry == 0)
                            <td>
                                <span class="badge badge-success">no</span>
                            </td>
                        @else
                            <td>
                                <span class="badge badge-warning">yes</span>
                            </td>
                        @endif
                        <td> {{ $data->created_at }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->
<!-- /.col -->
@endsection
