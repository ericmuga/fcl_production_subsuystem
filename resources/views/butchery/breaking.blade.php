@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="">
            <h1 class="m-0"> Reports |<small> Pork Breaking Report</small></h1>
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
                            <form action="{{ url('/export-breaking-combined-report') }}" method="post"
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
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"
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
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"> Scale 2 output data | <span id="subtext-h1-title"><small> entries ordered by
                    latest</small> </span></h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
        <div class="table-responsive">
            <table id="example2" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product Code</th>
                        <th>Product</th>
                        <th>Weight (kgs)</th>
                        <th>Net Weight(kgs)</th>
                        <th>No. of Pieces</th>
                        <th>Production Process</th>
                        <th>Date </th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>Product Code</th>
                        <th>Product</th>
                        <th>Weight (kgs)</th>
                        <th>Net Weight(kgs)</th>
                        <th>No. of Pieces</th>
                        <th>Production Process</th>
                        <th>Date </th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach($butchery_data as $data)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td> {{ $data->item_code }} </td>
                        <td> {{ $data->product_type }}</td>
                        <td> {{ number_format($data->actual_weight, 2) }}</td>
                        <td> {{ number_format($data->net_weight, 2) }}</td>
                        <td> {{ $data->no_of_items }}</td>
                        <td> {{ $data->process }}</td>
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

<div id="export_lines" class="modal">
    <div class="modal-dialog">
        <form class="modal-content form-prevent-multiple-submits" action="{{ route('export-breaking-lines-report') }}" method="post" id="export-logs-form">
            {{ csrf_field() }}
            <div class="modal-header">
                <h3 class="modal-title">
                    Export data
                </h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
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
                <button type="submit" class="btn btn-primary btn-prevent-multiple-submits">
                    <i class="fa fa-paper-plane" aria-hidden="true"></i> Export now
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


@section('scripts')
<script>
     $('.form-prevent-multiple-submits').on('submit', function () {
        $(".btn-prevent-multiple-submits").attr('disabled', true);
    });
</script>
@endsection