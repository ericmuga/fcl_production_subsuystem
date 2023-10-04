@extends('layouts.sausage_master')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sausage Production Per Batch Report | showing <strong>{{ $filter??  'Todays' }}</strong> Entries</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table display nowrap table-striped table-bordered table-hover" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Batch No</th>
                                <th>Total Pieces</th>
                                <th>Total Weight</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Batch No</th>
                                <th>Total Pieces</th>
                                <th>Total Weight</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($per_batch as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->batch_no }}</td>
                                <td>{{ $data->pieces }}</td>
                                <td>{{ $data->weight }}</td>
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

