@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-12">
            <h1 class="card-title"> Butchery | Dashboard | <span id="subtext-h1-title"><small> Today's numbers | Slaughter Date:
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
            <strong> Baconers: </strong>({{ $helpers->dateToHumanFormat($helpers->getButcheryDate()) }})
        </p>
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $lined_baconers?? "0" }} | <sup style="font-size: 20px">{{ number_format($slaughtered_baconers_weight, 2) }} kgs</sup></h3> 
                <p>No. of Baconers & netweight</p>
            </div>
            <div class="icon">
                {{-- <i class="ion ion-stats-bars"></i> --}}
                <i class="fa fa-shopping-bag" aria-hidden="true"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <p class="text-center">
            <strong>Scale 1 </strong>(slaughter chiller - butchery)
        </p>
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $baconers?? "0" }} | <sup style="font-size: 20px">{{ number_format($baconers_weight, 2) }} kgs</sup></h3>
                <p>No. of Beheaded Baconers & netweight</p>
            </div>
            <div class="icon">                
                <i class="fa fa-balance-scale"></i>
            </div>
            <a href="{{ route('butchery_beheading_report') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <p class="text-center">
            <strong>Sales </strong>(Slaughter chiller - Despatch)
        </p>
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $sales[0]->count?? "0" }} | <sup style="font-size: 20px">{{ number_format($sales[0]->total_net, 2) }} kgs</sup></h3>

                <p>Sales Baconers & Total Weight </p>
            </div>
            <div class="icon">                
                <i class="fa fa-shopping-cart" aria-hidden="true"></i>
            </div>
            <a href="{{ route('butchery_sales_report') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
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
            <strong>Scale 2 Output </strong>(Breaking Pig)
        </p>
        <div class="progress-group">
            legs
            <span class="float-right"><b>{{ number_format($b_legs, 2) }}</b><sup style="font-size: 15px">kgs</sup></span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-primary" style="width: {{ $b_legs/$baconers_weight*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            Shoulders
            <span class="float-right"><b>{{ number_format($b_shoulders, 2) }}</b><sup style="font-size: 15px">kgs</sup></span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-danger" style="width: {{ $b_shoulders/$baconers_weight*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            <span class="progress-text">Middles</span>
            <span class="float-right"><b>{{ number_format($b_middles, 2) }}</b><sup style="font-size: 15px">kgs</sup></span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-success" style="width: {{ $b_middles/$baconers_weight*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            Total Output
            <span class="float-right"><b>{{ number_format($three_parts_baconers, 2) }}</b><sup style="font-size: 15px">kgs</sup></span>
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
    <div class="col-lg-4 col-6">
        <!-- small box -->
        <p class="text-center">
            <strong> Sows: </strong>({{ $helpers->dateToHumanFormat($helpers->getButcheryDate()) }})
        </p>
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $lined_sows?? "0" }} | <sup style="font-size: 20px">{{ number_format($slaughtered_sows_weight, 2) }} kgs</sup></h3>
                <p>No. of Sows & netweight</p>
            </div>
            <div class="icon">
                <i class="fa fa-shopping-bag" aria-hidden="true"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-4 col-6">
        <!-- small box -->
        <p class="text-center">
            <strong>Scale 1 </strong>(slaughter chiller - butchery)
        </p>
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $sows?? "0" }} | <sup style="font-size: 20px">{{ number_format($sows_weight, 2) }} kgs</sup></h3>
                <p>No. of Beheaded Sows & netweight</p>
            </div>
            <div class="icon">                                
                <i class="fa fa-balance-scale"></i>
            </div>
            <a href="{{ route('butchery_beheading_report') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        @php
            if($sows_weight == 0.00){
                $sows_weight = 1.00;
            }
        @endphp
        <p class="text-center">
            <strong>Scale 2 Output </strong>(Breaking Sow)
        </p>
        <div class="progress-group">
            legs
            <span class="float-right"><b>{{ number_format($s_legs, 2) }}</b><sup style="font-size: 15px">kgs</sup></span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-primary" style="width: {{ $s_legs/$sows_weight*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            Shoulders
            <span class="float-right"><b>{{ number_format($s_shoulders, 2) }}</b><sup style="font-size: 15px">kgs</sup></span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-danger" style="width: {{ $s_shoulders/$sows_weight*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            <span class="progress-text">Middles</span>
            <span class="float-right"><b>{{ number_format($s_middles, 2) }}</b><sup style="font-size: 15px">kgs</sup></span>
            <div class="progress progress-sm">
                <div class="progress-bar bg-success" style="width: {{ $s_middles/$sows_weight*100 }}%"></div>
            </div>
        </div>
        <!-- /.progress-group -->
        <div class="progress-group">
            Total Output
            <span class="float-right"><b>{{ number_format($three_parts_sows, 2) }}</b><sup style="font-size: 15px">kgs</sup></span>
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
