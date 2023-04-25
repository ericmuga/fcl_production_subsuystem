@extends('layouts.despatch_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-md-12">
            <h1 class="card-title"> Despatch | Dashboard | <span id="subtext-h1-title"><small> showing today's numbers | <strong>{{ today()->format('l jS \\of F Y') }}</strong></small>
                </span></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@php
    // highcare 2595
    $total_pieces_h = isset($transfers['2595'][0]->total_pieces)? $transfers['2595'][0]->total_pieces : 0;
    $issued_pieces_h = isset($transfers['2595'][0]->issued_pieces)? $transfers['2595'][0]->issued_pieces : 0;
    $total_weight_h = isset($transfers['2595'][0]->total_weight)? $transfers['2595'][0]->total_weight : 0;
    $issued_weight_h = isset($transfers['2595'][0]->issued_weight)? $transfers['2595'][0]->issued_weight : 0;

    // highcare bulk 2500
    $total_pieces_b = isset($transfers['2500'][0]->total_pieces)? $transfers['2500'][0]->total_pieces : 0;
    $issued_pieces_b = isset($transfers['2500'][0]->issued_pieces)? $transfers['2500'][0]->issued_pieces : 0;
    $total_weight_b = isset($transfers['2500'][0]->total_weight)? $transfers['2500'][0]->total_weight : 0;
    $issued_weight_b = isset($transfers['2500'][0]->issued_weight)? $transfers['2500'][0]->issued_weight : 0;

    //issued 
    $total_h_pieces_issued = (int)$issued_pieces_h + (int)$issued_pieces_b;
    $total_h_weight_issued = (float)$issued_weight_h + (float)$issued_weight_b;

    //received
    $total_h_pieces_recv = (int)$total_pieces_h + (int)$total_pieces_b;
    $total_h_weight_recv = (float)$total_weight_h + (float)$total_weight_b;

    // sausage 2055
    $total_pieces = isset($transfers['2055'][0]->total_pieces)? $transfers['2055'][0]->total_pieces : 0;
    $issued_pieces = isset($transfers['2055'][0]->issued_pieces)? $transfers['2055'][0]->issued_pieces : 0;
    $total_weight = isset($transfers['2055'][0]->total_weight)? $transfers['2055'][0]->total_weight : 0;
    $issued_weight = isset($transfers['2055'][0]->issued_weight)? $transfers['2055'][0]->issued_weight : 0;

    // freshcuts 1570
    $total_weight_f = isset($transfers['1570'][0]->total_weight)? $transfers['1570'][0]->total_weight : 0;
    $issued_weight_f = isset($transfers['1570'][0]->issued_weight)? $transfers['1570'][0]->issued_weight : 0;
@endphp

@section('content')

<p class="row mb-2 ml-2">
    <strong> Idt Transfers Sausage: </strong>
</p>
<div class="row">
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($total_pieces, 0) }} <sup style="font-size: 20px">pkts</sup> | {{ number_format($total_weight, 2) }} <sup style="font-size: 20px">Kgs</sup></h3>

                <p>Received Total Transfers</p>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
            <a href="{{ route('despatch_idt_report', 'today') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ number_format($issued_pieces, 0) }}<sup style="font-size: 15px"> Pkts</sup>| {{ number_format($issued_weight, 2) }} <sup
                        style="font-size: 15px">Kgs</sup></h3>

                <p>Issued Total Transfers</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ (int)$total_pieces - (int)$issued_pieces }}<sup style="font-size: 15px"> Pkts</sup>| {{ number_format((float)$total_weight - (float)$issued_weight, 2) }} <sup
                        style="font-size: 15px">Kgs</sup></h3>

                <p>Variance of Despatch Against Sausage</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="{{ route('despatch_idt_variance', 'sausage') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($total_pieces, 0) }}<sup style="font-size: 15px"> Pkts</sup>| {{ number_format($total_weight, 2) }} <sup
                        style="font-size: 15px">Kgs</sup></h3>

                <p>Received Stocks Per Chiller</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="{{ route('despatch_idt_per_chiller') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->
<p class="row mb-2 ml-2">
    <strong> Idt Transfers High Care + Bulk : </strong>
</p>
<div class="row">
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($total_h_pieces_recv, 0) }} <sup style="font-size: 20px">pkts</sup> | {{ number_format($total_h_weight_recv, 2) }} <sup style="font-size: 20px">Kgs</sup></h3>

                <p>Received Total Transfers</p>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
            <a href="{{ route('despatch_idt_report', 'today') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ number_format($total_h_pieces_issued, 0) }}<sup style="font-size: 15px"> Pkts</sup>| {{ number_format($total_h_weight_issued, 2) }} <sup
                        style="font-size: 15px">Kgs</sup></h3>

                <p>Issued Total Transfers</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $total_h_pieces_recv - $total_h_pieces_issued }}<sup style="font-size: 15px"> Pkts</sup>| {{ number_format((float)$total_h_weight_recv - (float)$total_h_weight_issued, 2) }} <sup
                        style="font-size: 15px">Kgs</sup></h3>

                <p>Variance of Despatch Against Highcare</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="{{ route('despatch_idt', 'highcare') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($total_h_pieces_recv, 0) }}<sup style="font-size: 15px"> Pkts</sup>| {{ number_format($total_h_weight_recv, 2) }} <sup
                        style="font-size: 15px">Kgs</sup></h3>

                <p>Received Stocks Per Chiller</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="{{ route('despatch_idt_per_chiller') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
</div>
<p class="row mb-2 ml-2">
    <strong> Idt Transfers FreshCuts: </strong>
</p>
<div class="row">
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($total_weight_f, 2) }} <sup style="font-size: 20px">Kgs</sup></h3>

                <p>Received Total Weight</p>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
            <a href="{{ route('despatch_idt_report', 'today') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-primary">
            <div class="inner">
                <h3> {{ number_format($issued_weight_f, 2) }} <sup
                        style="font-size: 15px">Kgs</sup></h3>

                <p>Issued Total Weight</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-danger">
            <div class="inner">
                <h3> {{ number_format((float)$total_weight_f - (float)$issued_weight_f, 2) }} <sup
                        style="font-size: 15px">Kgs</sup></h3>

                <p>Variance of Despatch Against Freshcuts</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="{{ route('despatch_idt', 'highcare') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3> {{ number_format($total_weight_f, 2) }} <sup
                        style="font-size: 15px">Kgs</sup></h3>

                <p>Received Stocks Per Chiller</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="{{ route('despatch_idt_per_chiller') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->

@endsection
