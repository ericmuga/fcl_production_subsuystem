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
<hr>
@endsection

@section('content')
<!-- Small boxes (Stat box) -->
<div class="row">
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <p class="text-center">
            <strong> Slaughtered: ({{ $helpers->dateToHumanFormat($butchery_date) }})</strong>
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
                <p>No. of Beheaded Baconers</p>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
            <a href="{{ route('butchery_beheading_report') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
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
            <a href="{{ route('butchery_breaking_report') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
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
            <strong>Scale 2 Output</strong>
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
        <div class="footer">
            <a href="{{ route('butchery_breaking_report') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
        <!-- /.progress-group -->
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->
<hr>

<!-- ********************************************************************************************************* -->

<div class="row">
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <p class="text-center">
            <strong> Slaughtered: ({{ $helpers->dateToHumanFormat($butchery_date) }})</strong>
        </p>
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $lined_sows?? "0" }}</h3>
                <p>No. of Sows</p>
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
                <h3>{{ $sows?? "0" }}</h3>
                <p>No. of Beheaded Sows</p>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
            <a href="{{ route('butchery_beheading_report') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <p class="text-center">
            <strong>Scale 1 output</strong>
        </p>
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($sows_weight, 2) }} <sup style="font-size: 20px">kgs</sup></h3>

                <p>Sows Total Weight Output</p>
            </div>
            <div class="icon">
                <i class="ion ion-alert"></i>
            </div>
            <a href="{{ route('butchery_breaking_report') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        @php
            if($sows_weight == 0.00){
                $sows_weight = 1.00;
            }
        @endphp
        <p class="text-center">
            <strong>Scale 2 Output</strong>
        </p>
        <div class="progress-group">
            legs
            <span class="float-right"><b>{{ number_format($s_legs, 2) }}</b>kgs</span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-primary" style="width: {{ $s_legs/$sows_weight*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            Shoulders
            <span class="float-right"><b>{{ number_format($b_shoulders, 2) }}</b>kgs</span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-danger" style="width: {{ $s_shoulders/$sows_weight*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            <span class="progress-text">Middles</span>
            <span class="float-right"><b>{{ number_format($s_middles, 2) }}</b>kgs</span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-success" style="width: {{ $s_middles/$sows_weight*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            Total Output
            <span class="float-right"><b>{{ number_format($three_parts_sows, 2) }}</b>(kgs)</span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-warning"
                    style="width: {{ $three_parts_sows/$sows_weight*100 }}%"></div>
            </div>
        </div>
        <div class="footer">
            <a href="{{ route('butchery_breaking_report') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
        <!-- /.progress-group -->
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->

@endsection
