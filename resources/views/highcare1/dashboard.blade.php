@extends('layouts.highcare_master')

@section('content-header')
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <h1 class="card-title"> HighCare 1 | Dashboard | <span id="subtext-h1-title"><small> Showing today's numbers</small>
                </span></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<!-- IDT Transfers  -->
<p class="row mb-2 ml-2">
    <strong> Idt Transfers: </strong>
</p>
<div class="row">
    <div class="col-md-3 col-6">
        <!-- small box -->
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>0993 <sup style="font-size: 20px">pkts</sup> | 894 <sup style="font-size: 20px">Kgs</sup></h3>

                <p>HighCare 1 Total Transfers</p>
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
                <h3>234<sup style="font-size: 15px"> Pkts</sup>| 544 <sup
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
                <h3>343<sup style="font-size: 15px"> Pkts</sup>| 322 <sup
                        style="font-size: 15px">Kgs</sup></h3>

                <p>Variance</p>
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
                <h3>32<sup style="font-size: 15px"> Pkts</sup>| 554 <sup
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

@endsection
