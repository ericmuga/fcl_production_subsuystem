@extends('layouts.template_master')

@section('navbar')

<!-- Navbar -->
@if (!isset($layout))
@include('layouts.headers.butchery_header') 
@else
@switch($layout)
    @case('beef')
        @include('layouts.headers.beef_header')
        @break
    @case('assets')
        @include('layouts.headers.assets_header')
        @break

    @default
        @include('layouts.headers.butchery_header')
@endswitch
@endif

<!-- /.navbar -->

@endsection('navbar')
