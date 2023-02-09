@extends('layouts.highcare_master')

@section('content-header')
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <h1 class="card-title"> HighCare 1 | Dashboard | <span id="subtext-h1-title"><small> Showing today's
                        numbers</small>
                </span></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@php
    $issued_pieces = isset($transfers[0]->issued_pieces)? $transfers[0]->issued_pieces : 0;
    $received_pieces = isset($transfers[0]->received_pieces)? $transfers[0]->received_pieces : 0;
    $issued_weight = isset($transfers[0]->issued_weight)? $transfers[0]->issued_weight : 0;
    $received_weight = isset($transfers[0]->received_weight)? $transfers[0]->received_weight : 0;
@endphp

@section('content')
<!-- IDT Transfers  -->
<p class="row mb-2 ml-2">
    <strong> Idt Transfers: </strong>
</p>
    
<div class="row">
    <div class="col-md-4 col-6">
        <!-- small box -->
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ number_format($transfers[0]->issued_pieces ?? 0, 0) }} <sup style="font-size: 20px">pkts</sup> |
                    {{ number_format($transfers[0]->issued_weight ?? 0, 2) }} <sup style="font-size: 20px">Kgs</sup>
                </h3>

                <p>HighCare Total Issues</p>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
            <a href="{{ route('highcare1_idt_report', 'today') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-md-4 col-6">
        <!-- small box -->
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($transfers[0]->received_pieces ?? 0, 0) }}<sup style="font-size: 15px"> Pkts</sup>|
                    {{ number_format($transfers[0]->received_weight ?? 0, 2) }} <sup style="font-size: 15px">Kgs</sup>
                </h3>

                <p>Despatch Total Received</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <!-- small box -->
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ (int)$issued_pieces - (int)$received_pieces }}
                    <sup style="font-size: 15px"> Pkts</sup>|
                    {{ (float)$issued_weight - (float)$received_weight }}
                    <sup style="font-size: 15px">Kgs</sup></h3>

                <p>Variance of Highcare Issues Vs Despatch Receipts</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>
<!-- /.row -->

@endsection
