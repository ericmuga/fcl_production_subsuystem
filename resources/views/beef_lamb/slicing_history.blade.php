@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"> {{ $title }} </h1>
        </div>
    </div>
</div>

@endsection

@section('content')

<div class="card p-4 m-4">
    <table id="example1" class="table table-bordered table-striped table-responsive">
        <thead>
          <tr>
            <th scope="col">SNo</th>
            <th>Code </th>
            <th>product </th>
            <th>Product Type</th>
            <th>Production Process</th>
            <th>Total Crates</th>
            <th>Black Crates</th>
            <th>Scale Weight(kgs)</th>
            <th>Total Tare</th>
            <th>Net Weight(kgs)</th>
            <th>Total Pieces</th>
            <th>Prod Date</th>
            <th>Created Date</th>
          </tr>
        </thead>
        <tbody>
            @foreach ($entries as  $entry)
            <tr>
                <td>{{ $entry->id }}</td>
                <td>{{ $entry->item_code }}</td>
                <td>{{ $entry->description }}</td>
                @if ($entry->product_type == 1)
                    <td>Main</td>
                @elseif ($entry->product_type == 2)
                    <td>By-Product</td>
                @else
                    <td>Intake</td>
                @endif
                <td>{{ $entry->process }}</td>
                <td>{{ $entry->no_of_crates }}</td>
                <td>{{ $entry->black_crates }}</td>
                <td>{{ $entry->scale_reading }}</td>
                <td>{{ number_format(($entry->no_of_crates * 1.8) + ($entry->black_crates * 0.2), 2) }}</td>
                <td>{{ number_format($entry->net_weight, 2) }}</td>
                <td>{{ $entry->no_of_pieces }}</td>
                <td>{{ \Carbon\Carbon::parse($entry->production_date)->format('d/m/Y') }}
                </td>
                <td>{{ \Carbon\Carbon::parse($entry->created_at)->format('d/m/Y H:i') }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
              <th scope="col">SNo</th>
              <th>Code </th>
              <th>product </th>
              <th>Product Type</th>
              <th>Production Process</th>
              <th>Total Crates</th>
              <th>Black Crates</th>
              <th>Scale Weight(kgs)</th>
              <th>Total Tare</th>
              <th>Net Weight(kgs)</th>
              <th>Total Pieces</th>
              <th>Prod Date</th>
              <th>Created Date</th>
            </tr>
          </tfoot>
      </table>
</div>

@endsection