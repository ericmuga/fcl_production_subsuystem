@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-12">
            <h1 class="card-title"> Dashboard | <span id="subtext-h1-title"><small> Today's numbers | Slaughter Date:
                        <code> {{ $helpers->dateToHumanFormat($helpers->getButcheryDate()) }}</code></small>
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
            <strong> Slaughtered: ({{ $helpers->dateToHumanFormat($helpers->getButcheryDate()) }})</strong>
        </p>
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ (int)($lined[0]->count ?? 0) + (int)($lined_ms[0]->count ?? 0) }}</h3>
                <p>No. of Baconers slaughtered</p>
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
                <h3>{{ $no_of_carcass[0]->total?? 0 }}</h3>
                <p>No. of Beheaded Baconers(sales count: {{ $sales_count }})</p>
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
                <h3>{{ number_format($weights[0]->total?? 0, 2) }} <sup style="font-size: 20px">kgs</sup></h3>

                <p>Beheaded Baconers Total Weight </p>
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
            if($weights[0]->total == 0.00){
                $weights[0]->total = 1.00;
            }
        @endphp
        <p class="text-center">
            <strong>Scale 2 Output</strong>
        </p>
        <div class="progress-group">
            legs
            <span class="float-right"><b>{{ number_format($parts_weights[0]->netweight?? 0, 2) }}</b>kgs</span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-primary" style="width: {{ $parts_weights[0]->netweight?? 0 / $weights[0]->total*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            Shoulders
            <span class="float-right"><b>{{ number_format($parts_weights[1]->netweight?? 0, 2) }}</b>kgs</span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-danger" style="width: {{ $parts_weights[1]->netweight?? 0/$weights[0]->total*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            <span class="progress-text">Middles</span>
            <span class="float-right"><b>{{ number_format($parts_weights[2]->netweight?? 0, 2) }}</b>kgs</span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-success" style="width: {{ $parts_weights[2]->netweight?? 0 / $weights[0]->total*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            Total Output
            <span class="float-right"><b>{{ number_format($three_parts_weights[0]->netweight?? 0, 2) }}</b>(kgs)</span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-warning"
                    style="width: {{ $three_parts_weights[0]->netweight?? 0 / $weights[0]->total*100 }}%"></div>
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
            <strong> Slaughtered: ({{ $helpers->dateToHumanFormat($helpers->getButcheryDate()) }})</strong>
        </p>
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ (int)($lined[1]->count ?? 0) + ($lined_ms[1]->count ?? 0) }}</h3>
                <p>No. of Sows Slaughtered</p>
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
                <h3>{{ $no_of_carcass[1]->total?? 0 }}</h3>
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
                <h3>{{ number_format($weights[1]->total?? 0, 2) }} <sup style="font-size: 20px">kgs</sup></h3>

                <p>Beheaded Sows Total Weight Output</p>
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
            if($weights[1]->total == 0.00){
                $weights[1]->total = 1.00;
            }
        @endphp
        <p class="text-center">
            <strong>Scale 2 Output</strong>
        </p>
        <div class="progress-group">
            legs
            <span class="float-right"><b>{{ number_format($parts_weights[3]->netweight?? 0, 2) }}</b>kgs</span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-primary" style="width: {{ $parts_weights[3]->netweight?? 0/$weights[1]->total*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            Shoulders
            <span class="float-right"><b>{{ number_format($parts_weights[4]->netweight?? 0, 2) }}</b>kgs</span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-danger" style="width: {{ $parts_weights[4]->netweight?? 0 / $weights[1]->total*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            <span class="progress-text">Middles</span>
            <span class="float-right"><b>{{ number_format($parts_weights[5]->netweight?? 0, 2) }}</b>kgs</span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-success" style="width: {{ $parts_weights[5]->netweight?? 0/$weights[1]->total*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            Total Output
            <span class="float-right"><b>{{ number_format($three_parts_weights[1]->netweight?? 0, 2) }}</b>(kgs)</span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-warning"
                    style="width: {{ $three_parts_weights[1]->netweight?? 0 / $weights[1]->total*100 }}%"></div>
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
