@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-12">
            <h1 class="card-title"> Dashboard | <span id="subtext-h1-title"><small> Today's numbers | Slaughter Date:
                        <code> {{ $helpers->dateToHumanFormat($butchery_date) }}</code></small>
                </span></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<!-- Small boxes (Stat box) -->
<div class="row">
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <p class="text-center">
            <strong>From Slaughter</strong>
        </p>
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $lined_baconers?? "0" }}</h3>
                <p>No. of Baconers</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <p class="text-center">
            <strong>Scale 1</strong>
        </p>
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $baconers?? "0" }}</h3>
                <p>Beheaded Baconers</p>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <p class="text-center">
            <strong>Scale 1 output</strong>
        </p>
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($baconers_weight, 2) }} <sup style="font-size: 20px">kgs</sup></h3>

                <p>Baconers Total Weight Output</p>
            </div>
            <div class="icon">
                <i class="ion ion-alert"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        @php
            if($baconers_weight == 0.00){
                $baconers_weight = 1.00;
            }
        @endphp
        <p class="text-center">
            <strong>Scale 3 Output</strong>
        </p>
        <div class="progress-group">
            legs
            <span class="float-right"><b>{{ number_format($b_legs, 2) }}</b>kgs</span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-primary" style="width: {{ $b_legs/$baconers_weight*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            Shoulders
            <span class="float-right"><b>{{ number_format($b_shoulders, 2) }}</b>kgs</span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-danger" style="width: {{ $b_shoulders/$baconers_weight*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            <span class="progress-text">Middles</span>
            <span class="float-right"><b>{{ number_format($b_middles, 2) }}</b>kgs</span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-success" style="width: {{ $b_middles/$baconers_weight*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            Total Output
            <span class="float-right"><b>{{ number_format($three_parts_baconers, 2) }}</b>(kgs)</span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-warning"
                    style="width: {{ $three_parts_baconers/$baconers_weight*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->

{{-- <div class="card-body">
    <div class="row">
        <!-- /.col -->
        <div class="col-md-4">

            <div class="progress-group">
                legs
                <span class="float-right"><b>160</b>/200</span>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-primary" style="width: 80%"></div>
                </div>
            </div>
            <!-- /.progress-group -->

            <div class="progress-group">
                Shoulders
                <span class="float-right"><b>310</b>/400</span>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-danger" style="width: 75%"></div>
                </div>
            </div>

            <!-- /.progress-group -->
            <div class="progress-group">
                <span class="progress-text">Middles</span>
                <span class="float-right"><b>480</b>/800</span>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-success" style="width: 60%"></div>
                </div>
            </div>

            <!-- /.progress-group -->
            <div class="progress-group">
                Total Output
                <span class="float-right"><b>250</b>/500</span>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-warning" style="width: {{ 50/100*100 }}%"></div>
</div>
</div>
<!-- /.progress-group -->
</div>
<!-- /.col -->
</div>
<!-- /.row -->
</div> --}}

@endsection
