@extends('layouts.slaughter_master')

@section('content')
<div class="row">
    <div class="col-md-3" style="margin-bottom: 1%">
        <button class="btn btn-success btn-lg" data-toggle="modal" data-target="#export_data"><i
                class="fas fa-file-excel"></i> Generate Combined Report</button>
    </div>
    <div class="col-md-3" style="margin-bottom: 1%">
        <button class="btn btn-warning btn-lg" data-toggle="modal" data-target="#export_for_nav"><i
                class="fas fa-file-excel"></i> Generate For Nav Import</button>
    </div>
</div>

<!-- Start Export combined Modal -->
<div class="modal fade" id="export_data" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <form id="form-produce-check" action="{{ url('/export-slaughter-combined-report') }}" method="post">
        @csrf
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Export for Nav Upload</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        *Filter by date<br>
                        <div class="form-group col-md-6">
                            <label for="stemplate_date_created_from_flagged">Date:(dd/mm/yyyy)</label>
                            <input type="date" class="form-control" name="date" id="stemplate_date_created_from_flagged"
                                autofocus required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-flat float-left" type="button"
                        data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-lg  float-right"><i class="fa fa-send"></i>
                        Export</button>
                </div>
            </div>
        </div>
    </form>
</div>
<!-- End Export combined Modal -->


<!-- Start Export for nav Modal -->
<div class="modal fade" id="export_for_nav" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <form id="form-produce-check" action="{{ url('/export-slaughter-for-nav') }}" method="post">
        @csrf
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Export for Nav Upload</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        *Filter by date<br>
                        <div class="form-group col-md-6">
                            <label for="stemplate_date_created_from_flagged">Date:(dd/mm/yyyy)</label>
                            <input type="date" class="form-control" name="date" id="stemplate_date_created_from_flagged"
                                autofocus required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-flat float-left" type="button"
                        data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-lg  float-right"><i class="fa fa-send"></i>
                        Export</button>
                </div>
            </div>
        </div>
    </form>
</div>
<!-- End export for Nav modal-->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Slaughter Data Report| <span id="subtext-h1-title"><small> showing all
                            entries</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
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
                                <th>Rounded weight(kgs)</th>
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
                                <th>Rounded weight(kgs)</th>
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
                                <td>{{ round($data->net_weight) }}</td>
                                <td>{{ $data->meat_percent }}</td>
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
