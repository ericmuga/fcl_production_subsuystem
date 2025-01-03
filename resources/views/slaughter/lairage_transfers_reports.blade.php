@extends('layouts.slaughter_master')


@section('styles')
    <link rel="stylesheet" href="/css/transfers.css">
@endsection

@section('content')

<div class="container-fluid">
    <button data-toggle="modal" data-target="#export_data" class="btn btn-success d-block mb-2" >
        <i class="fa fa-download" aria-hidden="true"></i> Download Lairage Transfer Summary
    </button>
     
    <hr/>

    <div id="transfer_entries" class="card">
        <h3 class="card-header">Transfer to Slaughter Entries Recorded Today</h3>
        <div class="card-body">
            <div class="table-responsive">
                <table id="example1" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Animal Code</th>
                            <th scope="col">Animal Type</th>
                            <th scope="col">Count</th>
                            <th scope="col">Edited</th>
                            <th scope="col">Date Time Posted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transfers as $index => $transfer)
                            <tr>
                                <th scope="row">{{ $loop->iteration }}</th>
                                <td>{{ $transfer->product_code }}</td>
                                <td>
                                    {{ $animalTypes[$transfer->product_code] }}
                                </td>
                                <td>{{ $transfer->count }}</td>
                                @if($transfer->created_at == $transfer->updated_at)
                                    <td>
                                        <span class="badge badge-success">No</span>
                                    </td>
                                @else
                                    <td>
                                        <span class="badge badge-warning">Yes</span>
                                    </td>
                                @endif
                                <td>{{ $helpers->amPmDate($transfer->created_at) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Edit Transfer Modal -->
    <div class="modal fade" id="editTransferModal" tabindex="-1" role="dialog" aria-labelledby="editTransferModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTransferModalLabel">Edit Transfer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="edit_transfer_form" class="form-prevent-multiple-submits" action="{{ route('update_idt_lairage') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="transfer_id" id="transfer_id">
                        <div class="form-group">
                            <label for="edit_product_code">Animal Type</label>
                            <select id="edit_product_code" name="edit_product_code" class="form-control" required>
                                <option value="" disabled selected>Select Animal Type</option>
                                <option value="G0101">G0101 Baconer</option>
                                <option value="G0102">G0102 Sow</option>
                                <option value="G0104">G0104 Suckling</option>
                            </select>
                        </div>
                        <p>
                            <span class="font-weight-bold">Count</span>
                            <span id="editing_count"></span>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-prevent-multiple-submits">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Start Export combined Modal -->
    <div class="modal fade" id="export_data" tabindex="-1" role="dialog"
    aria-hidden="true">
    <form id="form-lairage-transfer-summary" action="{{ route('lairage_transfer_summary') }}" method="get">
        @csrf
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Export</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        *Filter by date<br>
                        <div class="form-group col-md-6">
                            <label for="date-filter">Date:(dd/mm/yyyy)</label>
                            <input type="date" class="form-control" name="date" id="date-filter"
                                autofocus required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button"
                        data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fa fa-send"></i>
                        Export</button>
                </div>
            </div>
        </div>
    </form>
    </div>
    <!-- End Export combined Modal -->

</div>

@endsection


@section('scripts')
<script>
    var today = new Date();
    var dateFilter = document.getElementById('date-filter');
    dateFilter.max = today.toISOString().substr(0, 10);

    $('.form-prevent-multiple-submits').on('submit', function () {
        $(".btn-prevent-multiple-submits").attr('disabled', true);
    });

    const number_input = document.querySelector('#count');
    const minus_button = document.querySelector('#count-reducer');
    const plus_button = document.querySelector('#count-increaser');

    function updateTransferId(event) {;
        let button = event.target;
        let transferId = button.getAttribute('data-transfer-id');
        let editingCount = button.getAttribute('data-editing-count');
        transferIdInput.value = transferId;
        editingCountSpan.innerHTML = editingCount;
    }

    minus_button.addEventListener('click', () => {

        if (number_input.value > 1) {
            number_input.value = parseInt(number_input.value) - 1;
        }
       
    });

    plus_button.addEventListener('click', () => {
        number_input.value = parseInt(number_input.value) + 1;
    });

    $('#editTransferModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var editingTransferId = button.data('transfer-id'); // Extract info from data-* attributes
        var editingCount = this.getAttribute('data-editing-count')
        var modal = $(this);
        modal.find('#transfer_id').val(editingTransferId);
        modal.find('#editing_count').text(editingCount);
    });

    document.querySelectorAll('button[data-target="#editTransferModal"]').forEach(button => {
        button.addEventListener('click', function () {
            var transferId = this.getAttribute('data-transfer-id');
            var productCode = this.getAttribute('data-product-code');
            var editingCount = this.getAttribute('data-editing-count');
            document.getElementById('edit_transfer_id').value = transferId;
            document.getElementById('editing_count').text = 'hello';
        });
    });

    $('#editTransferModal').on('hidden.bs.modal', function () {
        document.getElementById('edit_transfer_form').reset();
        document.getElementById('editing_count').innerHTML = '';
    });

updateDate(event) {
    console.log('updating date');
    let url = window.location.href;
    let params = new URLSearchParams(url.search);
    params.append("date", event.target.value);
    window.location = url + '?' + params.toString();
}

</script>

@endsection
