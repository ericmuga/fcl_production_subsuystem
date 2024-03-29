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
    <div class="col-md-4">
        <h4>Breaking Legs</h4>
        <div class="progress-group">
            <span class="float-center"><b>{{ number_format($scale2_data[0] ?? 0.00, 2) }}</b><sup style="font-size: 15px"> kgs</sup></span>
        </div>
    </div>
    <div class="col-md-4">
        <h4>Breaking Middles</h4>
        <div class="progress-group">
            <span class="float-center"><b>{{ number_format($scale2_data[1] ?? 0.00, 2) }}</b><sup style="font-size: 15px"> kgs</sup></span>
        </div>
    </div>
    <div class="col-md-4">
        <h4>Breaking Shoulders</h4>
        <div class="progress-group">
            <span class="float-center"><b>{{ number_format($scale2_data[2] ?? 0.00, 2) }} </b><sup style="font-size: 15px"> kgs</sup></span>
        </div>
    </div>    
</div><hr>
<div class="row">
    @php

        $mainItems = [];
        $ByProductItems = [];

        foreach ($main_items as $data) {
            if ($data->product_type == '1') {
                $mainItems[] = $data;
            } else {
                $ByProductItems[] = $data;
            }
        }

        // Sort the by product items by total_net in descending order
        usort($ByProductItems, function ($a, $b) {
            return $b->total_net - $a->total_net;
        });

        // Merge the main items and the sorted other items
        $sortedItems = array_merge($mainItems, $ByProductItems);

        $legs = [];
        $middles = [];
        $shoulders = [];

        foreach ($sortedItems as $data) {
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

        $totalLegsNet = collect($legs)->sum('total_net');
        $totalMiddlesNet = collect($middles)->sum('total_net');
        $totalShouldersNet = collect($shoulders)->sum('total_net');
    @endphp

    <div class="col-md-4">
        <h4>Deboning Legs</h4>
        @foreach ($legs as $data)
            <div class="progress-group">
                {{ $data->item_code.' '.$data->description.'-'.$data->product_type_name }}
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
        <h4>Deboning Middles</h4>
        @foreach ($middles as $data)
            <div class="progress-group">
                {{ $data->item_code.' '.$data->description.'-'.$data->product_type_name }}
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
        <h4>Deboning Shoulders</h4>
        @foreach ($shoulders as $data)
            <div class="progress-group">
                {{ $data->item_code.' '.$data->description.'-'.$data->product_type_name }}
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
    <div class="col-md-4">
        <h4>Legs (Total: {{ number_format($totalLegsNet, 2) }})</h4>
    </div>

    <div class="col-md-4">
        <h4>Middles (Total: {{ number_format($totalMiddlesNet, 2) }})</h4>
    </div>

    <div class="col-md-4">
        <h4>Shoulders (Total: {{ number_format($totalShouldersNet, 2) }})</h4>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-md-12">
        <div class="progress-group">
            Total Weights(Inclusive fat Stripping Weights)
            @php
            $t1 = $cumm[0]->total_net?: 0;
            $t2 = $fat_stripping_total;
            $total = (float)$t1 + (float)$t2;
            @endphp
            <span class="float-right"><b>{{ $cumm[0]->total_pieces }} </b>|
                {{ number_format($total, 2) }}<sup style="font-size: 15px">kgs</sup></span>
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

@section('scripts')
<script>
    $(document).ready(function () {
        // Set the interval to refresh every 5 minutes (300,000 milliseconds)
        const refreshInterval = 300000; // 5 minutes in milliseconds
        setInterval(refreshPage, refreshInterval);
    });

    // Function to refresh the page
    const refreshPage =() => {
        location.reload(); // Reloads the current page
    }
</script>
@endsection
