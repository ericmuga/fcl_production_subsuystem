@extends('layouts.qa_master')

<!-- @section('navbar')
	@include('layouts.headers.qa_header')
@endsection -->

@section('content-header')
<div class="container">
	<div class="row">
		<div class="col-sm-12">
			<h1 class="card-title"> QA | Dashboard | <span id="subtext-h1-title"><small> Showing today's numbers</small>
				</span></h1>
		</div>
	</div>
</div>
@endsection

@section('content')
<p class="row mb-2 ml-2">
	<strong> IDT Summary: </strong>
</p>
<div class="row">
	<div class="col-md-4 col-6">
		<div class="small-box bg-info">
			<div class="inner">
				<h3>{{ $todays_sent->total_transfers ?? 0 }} <sup style="font-size: 15px">IDTs</sup></h3>
				<p>Today's Sent (QA → others)</p>
				<p>
					{{ number_format($todays_sent->total_pieces ?? 0, 0) }} pkts |
					{{ number_format($todays_sent->total_weight ?? 0, 2) }} Kgs
				</p>
			</div>
			<div class="icon">
				<i class="ion ion-arrow-up-a"></i>
			</div>
			<a href="{{ route('qa_idt_report', 'today') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
		</div>
	</div>
	<div class="col-md-4 col-6">
		<div class="small-box bg-success">
			<div class="inner">
				<h3>{{ $todays_received->total_transfers ?? 0 }} <sup style="font-size: 15px">IDTs</sup></h3>
				<p>Today's Received (others → QA)</p>
				<p>
					{{ number_format($todays_received->total_pieces ?? 0, 0) }} pkts |
					{{ number_format($todays_received->total_weight ?? 0, 2) }} Kgs
				</p>
			</div>
			<div class="icon">
				<i class="ion ion-arrow-down-a"></i>
			</div>
			<a href="{{ route('qa_idt_report', 'today') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
		</div>
	</div>
	<div class="col-md-4 col-6">
		<div class="small-box bg-warning">
			<div class="inner">
				<h3>{{ $pending_approvals->total_transfers ?? 0 }}</h3>
				<p>Pending Approvals (QA related)</p>
				<p>
					{{ number_format($pending_approvals->total_pieces ?? 0, 0) }} pkts |
					{{ number_format($pending_approvals->total_weight ?? 0, 2) }} Kgs
				</p>

			</div>
			<div class="icon">
				<i class="ion ion-alert"></i>
			</div>
			<a href="{{ route('qa_idt_report', 'history') }}" class="small-box-footer">Review <i class="fas fa-arrow-circle-right"></i></a>
		</div>
	</div>
</div>

<hr>
<div class="row mb-3">
	<div class="col-md-6">
		<a href="{{ route('qa_issue_idt') }}" class="btn btn-primary btn-lg btn-block">
			<i class="fa fa-paper-plane"></i> Issue IDT from QA
		</a>
	</div>
	<div class="col-md-6">
		<a href="{{ route('qa_receive_idt') }}" class="btn btn-success btn-lg btn-block">
			<i class="fa fa-download"></i> Receive IDT to QA
		</a>
	</div>
</div>
@endsection

