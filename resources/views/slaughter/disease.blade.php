@extends('layouts.slaughter_master')

@section('content')

<div class="div">
    <button class="btn btn-primary " data-toggle="modal" data-target="#recordDiseaseModal">
        Record Disease/Death
    </button>
</div>
<hr>

<!-- Record Disease Modal -->
<div class="modal fade" id="recordDiseaseModal" tabindex="-1" aria-labelledby="recordDiseaseModalLabel" aria-hidden="true">
    <form class="modal-dialog" action="{{ route('record_disease') }}" method="post">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="recordDiseaseModalLabel">Record Disease/Death</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="item_code">Animal Type</label>
                <select class="form-control select2" name="item_code" id="item_code" required>
                    @foreach($itemCodes as $itemCode)
                        <option value="{{ $itemCode->code }}">{{ $itemCode->code }} {{ $itemCode->description }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="slapmark">Slapmark</label>
                <select class="form-control select2" name="slapmark" id="slapmark" required>
                    @foreach($receipts as $receipt)
                    @if (old('slapmark') == $receipt->vendor_tag)
                    <option value="{{ $receipt->vendor_tag }}" selected>{{ ucwords($receipt->vendor_tag) }}
                    </option>
                    @else
                    <option value="{{ $receipt->vendor_tag }}">{{ ucwords($receipt->vendor_tag) }}</option>
                    @endif
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="disease_code">Disease Code</label>
                <select class="form-control select2" name="disease_code" id="disease_code" required>
                    @foreach($diseaseCodes as $diseaseCode)
                        <option value="{{ $diseaseCode->disease_code }}">{{ $diseaseCode->disease_code }} {{ $diseaseCode->description }}</option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="receipt_no" id="receipt_no" value="" required> 
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>

<!-- Disease Entries Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"></h3>
                <h3 class="card-title">Recorded Disease for last <strong>2 days</strong> | <span><small> view, filter,
                            print/download</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Slapmark</th>
                                <th>Disease code</th>
                                <th>Date</th>
                                <th>Recorded by</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($diseaseEntries as $diseaseEntry)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $diseaseEntry->slapmark }}</td>
                                <td>{{ $diseaseEntry->disease_code }}</td>
                                <td>{{ $helpers->dateToHumanFormat($diseaseEntry->created_at) }}</td>
                                <td>{{ $diseaseEntry->user_name }}</td>
                            </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>
@endsection


@section('scripts')
<script>
    $(document).ready(function() {
        // Create a JavaScript object that maps items to their corresponding numbers
        var receiptMap = @json($receipts->pluck('receipt_no', 'vendor_tag'));

        // When the select input changes
        $('#slapmark').change(function() {
            // Get the selected item
            var selectedItem = $(this).val();

            // Find the corresponding number using the receiptMap
            var correspondingNumber = receiptMap[selectedItem];

            // Update the hidden input with the corresponding number
            $('#receipt_no').val(correspondingNumber);
        });

        // Trigger change event on page load to set the initial hidden input value
        $('#slapmark').trigger('change');
    });
</script>
@endsection
