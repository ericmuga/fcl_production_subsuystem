@extends('layouts.spices_master')

@section('content')

@section('content-header')
<div class="container-fluid">
    <div class="row ml-2">
        <div class="col-md-6">
            <h3 class="m-0"> {{ $title }} |<small> Showing entries for last <strong>{{ $filter }}</strong> days </small>
                </h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

<!-- Items Table -->
<div class="card m-4 p-4">
    <div class="table-responsive">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th scope="col">Sno</th>
                    <th scope="col">Production Order No</th>
                    <th scope="col">Line No</th>
                    <th scope="col">Item No</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">UOM</th>
                    <th scope="col">Location Code</th>
                    <th scope="col">Bin Code</th>
                    <th scope="col">User Name</th>
                    <th scope="col">Routing</th>
                    <th scope="col">Date Time</th>
                    <th scope="col">Status</th>
                    <th scope="col">Finished Production Order No</th>
                    <th scope="col">External Document No</th>
                    <th scope="col">Transaction Date</th>
                    <th scope="col">Published</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lines as $item)
                    <tr>
                        <td>{{ $item->ID }}</td>
                        <td>{{ $item->ProductionOrderNo }}</td>
                        <td>{{ $item->LineNo }}</td>
                        <td>{{ $item->ItemNo }}</td>
                        <td>{{ $item->Quantity }}</td>
                        <td>{{ $item->UOM }}</td>
                        <td>{{ $item->LocationCode }}</td>
                        <td>{{ $item->BinCode }}</td>
                        <td>{{ $item->UserName }}</td>
                        <td>{{ $item->Routing }}</td>
                        <td>{{ $item->DateTime }}</td>
                        <td>{{ $item->Status }}</td>
                        <td>{{ $item->FinishedProductionOrderNo }}</td>
                        <td>{{ $item->ExternalDocumentNo }}</td>
                        <td>{{ $item->TransactionDate }}</td>
                        <td>{{ $item->Published }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th scope="col">Sno</th>
                    <th scope="col">Production Order No</th>
                    <th scope="col">Line No</th>
                    <th scope="col">Item No</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">UOM</th>
                    <th scope="col">Location Code</th>
                    <th scope="col">Bin Code</th>
                    <th scope="col">User Name</th>
                    <th scope="col">Routing</th>
                    <th scope="col">Date Time</th>
                    <th scope="col">Status</th>
                    <th scope="col">Finished Production Order No</th>
                    <th scope="col">External Document No</th>
                    <th scope="col">Transaction Date</th>
                    <th scope="col">Published</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>


@endsection
