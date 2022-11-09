@extends('layouts.master')

@section('content')
<div class="row">
    <div class="container-fluid">
        <div class="card-deck-wrapper">
            <div class="card-deck">
                <div class="card p-2 bg-info" style="height: 200px">
                    <a class="card-body text-center card-block stretched-link text-decoration-none"
                        href="{{ route('slaughter_dashboard') }}">
                        <h4 class="card-title">Slaughter</h4>
                        <p class="card-text">Select this option to switch to slaughter.
                        </p>
                    </a>
                    <div class="icon text-center">
                        <i class="fa fa-shopping-basket fa-4x" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="card p-2 bg-warning" style="height: 200px">
                    <a class="card-body text-center card-block stretched-link text-decoration-none"
                        href="{{ route('butchery_dashboard') }}">
                        <h4 class="card-title">Butchery</h4>
                        <p class="card-text">Select this option to switch to butchery.
                        </p>
                    </a>
                    <div class="icon text-center">
                        <i class="fa fa-cut fa-4x" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="card p-2 bg-secondary" style="height: 200px">
                    <a class="card-body text-center card-block stretched-link text-decoration-none card-link"
                        href="{{ route('spices_dashboard') }}">
                        <h4 class="card-title">Spices Section</h4>
                        <p class="card-text">Select this option to switch to Spices interface.
                        </p>
                    </a>
                    <div class="icon text-center">
                        <i class="fab fa-mix fa-4x" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<hr>
<div class="row">
    <div class="container-fluid">
        <div class="card-deck-wrapper">
            <div class="card-deck">
                <div class="card p-2 bg-success" style="height: 200px">
                    <a class="card-body text-center card-block stretched-link text-decoration-none card-link"
                        href="{{ route('stock_dashboard') }}">
                        <h4 class="card-title">Butchery Stocks</h4>
                        <p class="card-text">Select this option to switch to Butchery Stocks section.
                        </p>
                    </a>
                    <div class="icon text-center">
                        <i class="fa fa-share-alt fa-4x" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="card p-2 bg-dark" style="height: 200px">
                    <a class="card-body text-center card-block stretched-link text-decoration-none"
                        href="{{ route('sausage_dashboard') }}">
                        <h4 class="card-title">Sausage</h4>
                        <p class="card-text">Select this option to switch to Sausage Section.
                        </p>
                    </a>
                    <div class="icon text-center">
                        <i class="fa fa-barcode fa-4x" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="card p-2 bg-primary" style="height: 200px">
                    <a class="card-body text-center card-block stretched-link text-decoration-none card-link" href="#">
                        <h4 class="card-title">High Care</h4>
                        <p class="card-text">Select this option to switch to High Care interface.
                        </p>
                    </a>
                    <div class="icon text-center">
                        <i class="fa fa-check-circle fa-4x" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="card p-2 bg-danger" style="height: 200px">
                    <a class="card-body text-center card-block stretched-link text-decoration-none card-link"
                        href="{{ route('despatch_dashboard') }}">
                        <h4 class="card-title">Despatch Stocks</h4>
                        <p class="card-text">Select this option to switch to Despatch Stocks section.
                        </p>
                    </a>
                    <div class="icon text-center">
                        <i class="fa fa-truck fa-4x" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
