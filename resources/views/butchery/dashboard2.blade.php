@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-12">
            <h1 class="card-title"> Butchery | Dashboard | <span id="subtext-h1-title"><small> Today's numbers |
                        Slaughter Date:
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
    <div class="col-lg-6 col-6">

        <p class="text-center">
            <strong>Today's Deboned Numbers </strong>
        </p>
        @foreach( $main_items as $data)
            <div class="progress-group">
                {{ $data->item_code.' '. $data->description }}
                <span class="float-right"><b> {{ $data->total_pieces }} </b>|
                    {{ number_format($data->total_net, 2) }}<sup style="font-size: 15px">kgs</sup></span>
                <div class="progress progress-sm">
                    <div class="progress-bar {{ $helpers->randomBootstrap() }}" @if ($cumm[0]->total_net > 0)
                        style="width: {{ $data->total_net/ $cumm[0]->total_net  * 100 }}%" @endif>
                    </div>
                </div>
            </div>
            <!-- /.progress-group -->
        @endforeach

        <div class="progress-group">
            Total Weights
            <span class="float-right"><b>{{ $cumm[0]->total_pieces }} </b>|
                {{ number_format($cumm[0]->total_net, 2) }}<sup style="font-size: 15px">kgs</sup></span>
            <div class="progress progress-sm">
                <div class="progress-bar {{ $helpers->randomBootstrap() }}" style="width: 100%"></div>
            </div>
        </div>
        <div class="footer">
            <a href="{{ route('butchery_deboning_report') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
        <!-- /.progress-group -->
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->

@endsection
