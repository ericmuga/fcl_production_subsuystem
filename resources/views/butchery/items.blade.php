@extends('layouts.butchery_master')

@section('content')

<button class="btn btn-primary" data-toggle="modal" data-target="#createItemModal">
    Create Item
</button>

<!-- Create Item Modal -->
<div class="modal fade" id="createItemModal" tabindex="-1" role="dialog" aria-labelledby="createItemModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <form action="{{ route('create_item') }}"  class="modal-content" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="createItemModalLabel">Create Item</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="code">Item Code:</label>
            <input type="text" class="form-control" id="code" name="code" required>
          </div>
          <div class="form-group">
            <label for="barcode">Bar Code:</label>
            <input type="number" class="form-control" id="barcode" name="barcode" required>
          </div>
          <div class="form-group">
            <label for="description">Description:</label>
            <input type="text" class="form-control" id="description" name="description" required>
          </div>
          <div class="form-group">
            <label for="unit_of_measure">Unit of Measure:</label>
            <select class="form-control" id="unit_of_measure" name="unit_of_measure" required>
                <option value="" disabled selected>Select Unit of Measure</option>
                <option value="CARTON">Carton</option>>
                <option value="KG">Kilogram</option>
              <option value="PC">Piece</option>
            </select>
          </div>
          <div class="form-group">
            <label for="qty_per_unit_of_measure">Quantity per unit of measure:</label>
            <input type="number" class="form-control" id="qty_per_unit_of_measure" name="qty_per_unit_of_measure" step="0.01" required>
          </div>
          <div class="form-group">
            <label for="unit_count_per_crate">Unit Count per crate:</label>
            <input type="number" class="form-control" id="unit_count_per_crate" name="unit_count_per_crate" required>
          </div>
          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="despatch_combo" name="despatch_combo">
            <label class="form-label" for="despatch_combo">Transferrable to Despatch:</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
    </div>
  </div>

<!-- Items Table -->
<div class="card m-4 p-4">
  <table id="example1" class="table table-bordered">
    <thead>
      <tr>
        <th scope="col">Code</th>
        <th scope="col">Bar Code</th>
        <th scope="col">Description</th>
        <th scope="col">Unit of Measure</th>
        <th scope="col">Quantity per unit of measure</th>
        <th scope="col">Unit Count per crate</th>
      </tr>
    </thead>
    <tbody>
        @foreach ($items as $item)
        <tr>
            <th>{{ $item->code }}</th>
            <td>{{ $item->barcode }}</td>
            <td>{{ $item->description }}</td>
            <td>{{ $item->unit_of_measure }}</td>
            <td>{{ $item->qty_per_unit_of_measure }}</td>
            <td>{{ $item->unit_count_per_crate }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
          <th scope="col">Code</th>
          <th scope="col">Bar Code</th>
          <th scope="col">Description</th>
          <th scope="col">Unit of Measure</th>
          <th scope="col">Quantity per unit of measure</th>
          <th scope="col">Unit Count per crate</th>
        </tr>
      </tfoot>
  </table>
</div>


@endsection