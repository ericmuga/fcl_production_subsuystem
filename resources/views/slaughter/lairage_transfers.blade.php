@extends('layouts.slaughter_master')


@section('styles')
    <link rel="stylesheet" href="/css/transfers.css">
@endsection

@section('content')

<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-12 col-lg-8">
            <div class="card">
                <h3 class="card-header">
                Record Transfer
                </h3>
                <form id="record_transfer_form" class="card-body form-prevent-multiple-submits" action="{{ route('save_idt_lairage') }}" method="POST">
                    @csrf
                    <div class="form-group flex-grow-0">
                        <label for="product_code">Animal Type</label>
                        <select id="product_code" name="product_code" class="form-control select2" aria-label="Default select example">
                            <option value="G0101">G0101 Baconer</option>
                            <option value="G0102">G0102 Sow</option>
                            <option value="G0104">G0104 Suckling</option>
                        </select>
                    </div>
            
                    <div class="form-group">
                        <label for="total_pieces">count</label>
                        <div class="row my-3 transfer-number-input justify-content-center">
                            <div class="col col-3 col-lg-4 col-xl-2">
                                <button id="count-reducer" type="button" class="btn btn-block btn-primary text-center">-</button>
                            </div>
                            <div class="col col-6 col-lg-4 ">
                                <input id="total_pieces" name="total_pieces" type="number" class="form-control text-center" min="1" value="2">
                            </div>
                            <div class="col col-3 col-lg-4 col-xl-2">
                                <button id="count-increaser" type="button" class="btn btn-block btn-primary text-center">+</button>
                            </div>
                        </div>
                    </div>
                        
                <button type="submit" style="padding: 2%" class="btn btn-success btn-lg d-block mx-auto btn-prevent-multiple-submits"><i class="fa fa-paper-plane" aria-hidden="true"></i> Slaughter To Transfer</button>
                </form>
            </div>
         </div>

        <div class="col-12 col-lg-4">
            <div class="card">
                <h3 class="card-header">Todays Transfers Summary</h3>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Baconers: {{ $transfers->where('product_code', 'G0101')->sum('total_pieces') }}</li>
                    <li class="list-group-item">Sow: {{ $transfers->where('product_code', 'G0102')->sum('total_pieces') }}</li>
                    <li class="list-group-item">Suckling: {{ $transfers->where('product_code', 'G0104')->sum('total_pieces') }}</li>
                    <li class="list-group-item">Total Transferred: {{ $transfers->sum('total_pieces') }}</li>
                </ul>
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <h3 class="card-header">Transfer to Slaughter Entries Recorded Today</h3>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example1" class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Animal Type</th>
                                    <th scope="col">Count</th>
                                    <th scope="col">Edited</th>
                                    <th scope="col">Date Posted</th>
                                    <th scope="col">User</th>
                                    <th scope="col" class="no-export">Edit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transfers as $index => $transfer)
                                    <tr>
                                        <th scope="row">{{ $index + 1 }}</th>
                                        <td>{{ $transfer->product_code }}</td>
                                        <td>{{ $transfer->total_pieces }}</td>
                                        @if($transfer->edited == 0)
                                            <td>
                                                <span class="badge badge-success">No</span>
                                            </td>
                                        @else
                                            <td>
                                                <span class="badge badge-warning">Yes</span>
                                            </td>
                                        @endif
                                        <td>{{ $helpers->dateToHumanFormat($transfer->created_at) }}</td>
                                        <td>{{ $transfer->username }}</td>
                                        <td class="no-export">
                                            <i
                                                class="fa fa-pencil-alt"
                                                data-toggle="modal"
                                                data-target="#editTransferModal"
                                                data-transfer-id={{ $transfer->id }}
                                                data-product-code={{ $transfer->product_code }}
                                                data-editing-count={{ $transfer->total_pieces }}
                                            >
                                            </i>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
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

</div>





@endsection


@section('scripts')

<script>

    $('.form-prevent-multiple-submits').on('submit', function () {
        $(".btn-prevent-multiple-submits").attr('disabled', true);
    });

    const number_input = document.querySelector('#total_pieces');
    const minus_button = document.querySelector('#count-reducer');
    const plus_button = document.querySelector('#count-increaser');

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
        editingTransferId = button.data('transfer-id'); // Extract info from data-* attributes
        var modal = $(this);
        modal.find('#transfer_id').val(editingTransferId);
    });

    document.querySelectorAll('button[data-target="#editTransferModal"]').forEach(button => {
        button.addEventListener('click', function () {
            var transferId = this.getAttribute('data-transfer-id');
            var productCode = this.getAttribute('data-product-code');
            var editingCount = this.getAttribute('data-editing-count');
            document.getElementById('edit_transfer_id').value = transferId;
            document.getElementById('editing_count').innerHTML = String(editingCount);
        });
    });

    $('#editTransferModal').on('hidden.bs.modal', function () {
        document.getElementById('edit_transfer_form').reset();
        document.getElementById('editing_count').innerHTML = '';
    });



</script>

@endsection
