@extends('layouts.sausage_master')

@section('content-header')
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            {{-- <h1 class="m-0"> {{ $title }}<small></small></h1> --}}
            <h1 class="card-title"> Sausage | Dashboard | <span id="subtext-h1-title"><small> Showing today's numbers</small>
                </span></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<!-- Scanners -->
<p class="row mb-2 ml-2">
    <strong> Automatic Scanners Readings: </strong>
</p>
<div class="row">
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $total_entries }} <sup style="font-size: 20px">pkts</sup> | {{ number_format($total_tonnage, 2) }} <sup style="font-size: 20px">Kgs</sup></h3>

                <p>Total Entries Today</p>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
            <a href="{{ route('sausage_entries') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $highest_product[0]->total_count?? "0" }}<sup style="font-size: 15px"> Pkts</sup>| <sup
                        style="font-size: 15px">{{ $highest_product[0]->description?? "not available" }}</sup></h3>

                <p>Highest Product per Entry</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            <a href="{{ route('sausage_entries', 'highest-product') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $lowest_product[0]->total_count?? "0" }}<sup style="font-size: 15px"> Pkts</sup>| <sup
                        style="font-size: 15px">{{ $lowest_product[0]->description?? "not available" }}</sup></h3>

                <p>Lowest Product per Entry</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="{{ route('sausage_entries', 'lowest-product') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $wrong_entries }}</h3>

                <p>Probable wrong Entries</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="{{ route('sausage_entries', 'probable-wrong-entries') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->
<hr>
<!-- IDT Issued Transfers  -->
<p class="row mb-2 ml-2">
    <strong> Issued Idt Transfers: </strong>
</p>
<div class="row">
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ number_format($transfers->total_pieces_2055, 0) }} <sup style="font-size: 20px">pkts</sup> | {{ number_format($transfers->total_weight_2055, 2) }} <sup style="font-size: 20px">Kgs</sup></h3>

                <p>Sausage Total Issues</p>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
            <a href="{{ route('sausage_idt_report', 'today') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($transfers->received_pieces_2055, 0) }}<sup style="font-size: 15px"> Pkts</sup>| {{ number_format($transfers->received_weight_2055, 2) }} <sup
                        style="font-size: 15px">Kgs</sup></h3>

                <p>Despatch Total Received</p>
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
                <h3>{{ number_format($transfers->total_pieces_2055 - $transfers->received_pieces_2055, 0) }}<sup style="font-size: 15px"> Pkts</sup>| {{ number_format($transfers->total_weight_2055 - $transfers->received_weight_2055, 2) }} <sup
                        style="font-size: 15px">Kgs</sup></h3>

                <p>Variance of Sausage Issues Vs Despatch Receipts</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ number_format($transfers->received_pieces_2055, 0) }}<sup style="font-size: 15px"> Pkts</sup>| {{ number_format($transfers->received_weight_2055, 2) }} <sup
                        style="font-size: 15px">Kgs</sup></h3>

                <p>Received Stocks Per Chiller</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->
<hr>
<!-- IDT Received Transfers  -->
<p class="row mb-2 ml-2">
    <strong> Received Idt Transfers: </strong>
</p>
<div class="row">
    <div class="col-md-4 col-6">
        <!-- small box -->
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ number_format($transfers->total_pieces_1570, 0) }} <sup style="font-size: 20px">pkts</sup> | {{ number_format($transfers->total_weight_1570, 2) }} <sup style="font-size: 20px">Kgs</sup></h3>

                <p>Butchery to Sausage Total Issues</p>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
            <a href="{{ route('sausage_idt_report', 'today') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-md-4 col-6">
        <!-- small box -->
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($transfers->received_pieces_1570, 0) }}<sup style="font-size: 15px"> Pkts</sup>| {{ number_format($transfers->received_weight_1570, 2) }} <sup
                        style="font-size: 15px">Kgs</sup></h3>

                <p>Butchery To Sausage Total Received</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <!-- small box -->
        <div class="small-box bg-dark">
            <div class="inner">
                <h3>{{ number_format($transfers->total_pieces_1570 - $transfers->received_pieces_1570, 0) }}<sup style="font-size: 15px"> Pkts</sup>| {{ number_format($transfers->total_weight_1570 - $transfers->received_weight_1570, 2) }} <sup
                        style="font-size: 15px">Kgs</sup></h3>

                <p>Variance of Butchery Issues Vs Sausage Receipts</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>
<!-- /.row -->

@endsection
