@extends('layouts.petfood_master')

@section('content-header')
<div class="container">
    <h1 class="card-title"> Petfood | Dashboard | <span id="subtext-h1-title"><small> Showing today's numbers</small></span></h1>
</div>
@endsection

@section('content')

<!-- IDT Issue Transfers  -->
<br />
<p class="row mb-2 ml-2">
    <strong> IDT Summary: </strong>
</p>

<div class="row">
    <div class="col-md-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $total_issued_pieces }} <sup style="font-size: 20px">pieces</sup></h3>
                <p>Total Issued Pieces</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($total_issued_weight, 2) }} <sup style="font-size: 15px">Kgs</sup></h3>
                <p>Total Issued Weight</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($total_received_weight, 2) }} <sup style="font-size: 15px">Kgs</sup></h3>
                <p>Total Received Weight</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $todays_transaction_count }} <sup style="font-size: 15px">Transactions</sup></h3>
                <p>Today's IDT Transactions</p>
            </div>
        </div>
    </div>
</div>

@endsection