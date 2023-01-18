@extends('layouts.spices_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-md-12">
            <h1 class="card-title">Spices| Dashboard | <span id="subtext-h1-title"><small> showing today's numbers | <strong>{{ today()->format('l jS \\of F Y') }}</strong></small>
                </span></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><hr><!-- /.container-fluid -->

@endsection

@section('content')
<!-- Small boxes (Stat box) -->
<h5 class="mb-2">Received Items from DGS</h5>
<div class="row">
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>22,150<sup style="font-size: 20px">Kgs</sup></h3>

                <p>Today's Total Received</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-info">
            <div class="inner">
                <h3>200 |<sup style="font-size: 20px"> 20,456 Kgs</sup></h3>

                <p>Total Products Received</p>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>    
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-success">
            <div class="inner">
                <h3>5,323<sup style="font-size: 20px">Kgs</sup></h3>

                <p>Total Consumption</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>23,150<sup style="font-size: 20px">Kgs</sup></h3>

                <p>Stock Levels</p>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->    
</div>
<!-- /.row -->

@endsection
