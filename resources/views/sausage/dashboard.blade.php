@extends('layouts.sausage_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        {{-- <h1 class="m-0"> {{ $title }}<small></small></h1> --}}
        <h1 class="card-title"> Dashboard | <span id="subtext-h1-title"><small> showing today's numbers</small> </span></h1>
      </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<!-- Small boxes (Stat box) -->
<div class="row">
    <div class="col-md-3 col-6">
      <!-- small box -->
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $total_entries }} <sup style="font-size: 20px">pkts</sup> | <sup style="font-size: 20px"> {{ number_format($total_tonnage, 2) }} Kgs</sup></h3>

          <p>Total Entries Today</p>
        </div>
        <div class="icon">
          <i class="ion ion-bag"></i>
        </div>
        <a href="{{ route('sausage_entries') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
    <div class="col-md-3 col-6">
      <!-- small box -->
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ $highest_product[0]->total_count?? "0" }}<sup style="font-size: 15px"> Pkts</sup>| <sup style="font-size: 15px">{{ $highest_product[0]->description?? "not available" }}</sup></h3>
         
          <p>Highest Product per Entry</p>
        </div>
        <div class="icon">
          <i class="ion ion-stats-bars"></i>
        </div>
        <a href="{{ route('sausage_entries', 'highest-product') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <!-- small box -->
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ $lowest_product[0]->total_count?? "0" }}<sup style="font-size: 15px"> Pkts</sup>| <sup style="font-size: 15px">{{ $lowest_product[0]->description?? "not available" }}</sup></h3>

          <p>Lowest Product per Entry</p>
        </div>
        <div class="icon">
          <i class="ion ion-pie-graph"></i>
        </div>
        <a href="{{ route('sausage_entries', 'lowest-product') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
    <div class="col-md-3 col-6">
      <!-- small box -->
      <div class="small-box bg-danger">
        <div class="inner">
          <h3>{{ $wrong_entries }}</h3>

          <p>Probable wrong Entries</p>
        </div>
        <div class="icon">
          <i class="ion ion-pie-graph"></i>
        </div>
        <a href="{{ route('sausage_entries', 'probable-wrong-entries') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
  </div>
  <!-- /.row -->

@endsection
