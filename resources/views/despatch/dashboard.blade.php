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

@section('content')

<p class="row mb-2 ml-2">
    <strong> Idt Transfers Sausage: </strong>
</p>
<div class="row">
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ number_format($transfers[0]->total_pieces, 0) }} <sup style="font-size: 20px">pkts</sup> | {{ number_format($transfers[0]->total_weight, 2) }} <sup style="font-size: 20px">Kgs</sup></h3>

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
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($transfers[0]->issued_pieces, 0) }}<sup style="font-size: 15px"> Pkts</sup>| {{ number_format($transfers[0]->issued_weight, 2) }} <sup
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
                <h3>{{ number_format($transfers[0]->total_pieces - $transfers[0]->issued_pieces, 0) }}<sup style="font-size: 15px"> Pkts</sup>| {{ number_format($transfers[0]->total_weight - $transfers[0]->issued_weight, 2) }} <sup
                        style="font-size: 15px">Kgs</sup></h3>

                <p>Variance of Despatch Against Sausage</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="{{ route('despatch_idt_variance') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ number_format($transfers[0]->total_pieces, 0) }}<sup style="font-size: 15px"> Pkts</sup>| {{ number_format($transfers[0]->total_weight, 2) }} <sup
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
    <strong> Idt Transfers High Care: </strong>
</p>
<div class="row">
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($transfers[1]->total_pieces, 0) }} <sup style="font-size: 20px">pkts</sup> | {{ number_format($transfers[1]->total_weight, 2) }} <sup style="font-size: 20px">Kgs</sup></h3>

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
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($transfers[1]->issued_pieces, 0) }}<sup style="font-size: 15px"> Pkts</sup>| {{ number_format($transfers[1]->issued_weight, 2) }} <sup
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
        <div class="small-box bg-dark">
            <div class="inner">
                <h3>{{ number_format($transfers[1]->total_pieces - $transfers[1]->issued_pieces, 0) }}<sup style="font-size: 15px"> Pkts</sup>| {{ number_format($transfers[1]->total_weight - $transfers[1]->issued_weight, 2) }} <sup
                        style="font-size: 15px">Kgs</sup></h3>

                <p>Variance of Despatch Against Highcare</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="{{ route('despatch_idt_variance') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ number_format($transfers[1]->total_pieces, 0) }}<sup style="font-size: 15px"> Pkts</sup>| {{ number_format($transfers[1]->total_weight, 2) }} <sup
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
