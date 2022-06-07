@extends('layouts.slaughter_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-12">
            <h1 class="card-title"> Dashboard | <span id="subtext-h1-title"><small> showing today's numbers | Date:
                        <code> {{ $helpers->dateToHumanFormat($date) }}</code></small>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->
<hr>
@endsection

@section('content')
<!-- Small boxes (Stat box) -->
<div class="row">
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $lined_up }}</h3>
                <p>No. of Animals from lairage</p>
            </div>
            <div class="icon">                
                <i class="fa fa-share-alt"></i>
            </div>
            <a href="{{ route('slaughter_receipts') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $slaughtered }}<sup style="font-size: 20px"></sup></h3>
                <p>Slaughtered & Weighed</p>
            </div>
            <div class="icon">
                <i class="fa fa-balance-scale"></i>
            </div>
            <a href="{{ route('slaughter_data_report') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $missing_slaps }}</h3>
                <p>Missing Slapmarks</p>
            </div>
            <div class="icon">
                <i class="fa fa-eye-slash" aria-hidden="true"></i>
            </div>
            <a href="{{ route('missing_slap_data') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $lined_up - ($slaughtered + $missing_slaps) }} </h3>
                <p>Remaining count</p>
            </div>
            <div class="icon">
                <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->
<hr>

<div class="row">
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($total_weight, 2) }} <sup style="font-size: 20px">kgs</sup></h3>

                <p> Total Weight Output</p>
            </div>
            <div class="icon">
                <i class="fa fa-bars" aria-hidden="true"></i>
            </div>
            <a href="{{ route('slaughter_data_report') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
   <div class="col-lg-3 col-6">
        @php
            if($total_weight == 0.00){
                $total_weight = 1.00;
            }
        @endphp
        <div class="progress-group">
            Baconers
            <span class="float-right"><b>{{ number_format($slaughtered_baconers, 2) }}</b>kgs</span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-primary" style="width: {{ $slaughtered_baconers/$total_weight*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            Sows
            <span class="float-right"><b>{{ number_format($slaughtered_sows, 2) }}</b>kgs</span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-danger" style="width: {{ $slaughtered_sows/$total_weight*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            <span class="progress-text">Suckling</span>
            <span class="float-right"><b>{{ number_format($slaughtered_suckling, 2) }}</b>kgs</span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-success" style="width: {{ $slaughtered_suckling/$total_weight*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="footer">
            <a href="{{ route('slaughter_data_report') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
        <!-- /.progress-group -->
    </div>
</div>

@endsection
