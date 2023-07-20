@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-12">
            <h1 class="card-title"> Butchery | Dashboard | <span id="subtext-h1-title"><small> Today's numbers |
                        Deboning Date:
                        <code> {{ $helpers->dateToHumanFormat(today()) }}</code></small>
                </span></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->
<hr>
@endsection

@section('content')
<!-- Small boxes (Stat box) -->
<div class="row">
    @php
        $legs = [];
        $middles = [];
        $shoulders = [];

        foreach ($main_items as $data) {
            switch ($data->process_code) {
                case '4':
                case '7':
                case '11':
                    $legs[] = $data;
                    break;
                case '5':
                case '16':
                case '17':
                    $middles[] = $data;
                    break;
                case '6':
                case '18':
                case '12':
                    $shoulders[] = $data;
                    break;
                default:
                    // Handle other process codes if necessary
                    break;
            }
        }
    @endphp

    <div class="col-md-4">
        <h4>Legs</h4>
        @foreach ($legs as $data)
            <div class="progress-group">
                {{ $data->item_code.' '.$data->description }}
                <span class="float-right"><b>{{ $data->total_pieces }}</b> | {{ number_format($data->total_net, 2) }}<sup style="font-size: 15px">kgs</sup></span>
                <div class="progress progress-sm">
                    <div class="progress-bar {{ $helpers->randomBootstrap() }}" @if ($cumm[0]->total_net > 0)
                        style="width: {{ $data->total_net / $cumm[0]->total_net * 100 }}%" @endif>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="col-md-4">
        <h4>Middles</h4>
        @foreach ($middles as $data)
            <div class="progress-group">
                {{ $data->item_code.' '.$data->description }}
                <span class="float-right"><b>{{ $data->total_pieces }}</b> | {{ number_format($data->total_net, 2) }}<sup style="font-size: 15px">kgs</sup></span>
                <div class="progress progress-sm">
                    <div class="progress-bar {{ $helpers->randomBootstrap() }}" @if ($cumm[0]->total_net > 0)
                        style="width: {{ $data->total_net / $cumm[0]->total_net * 100 }}%" @endif>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="col-md-4">
        <h4>Shoulders</h4>
        @foreach ($shoulders as $data)
            <div class="progress-group">
                {{ $data->item_code.' '.$data->description }}
                <span class="float-right"><b>{{ $data->total_pieces }}</b> | {{ number_format($data->total_net, 2) }}<sup style="font-size: 15px">kgs</sup></span>
                <div class="progress progress-sm">
                    <div class="progress-bar {{ $helpers->randomBootstrap() }}" @if ($cumm[0]->total_net > 0)
                        style="width: {{ $data->total_net / $cumm[0]->total_net * 100 }}%" @endif>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<hr>
<div class="row">
    <div class="col-md-12">
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
    </div>
</div>

@endsection
