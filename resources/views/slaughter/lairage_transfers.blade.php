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
                            @foreach ($animalTypes as $key => $value)
                                <option value={{ $key }}>{{ $key }} {{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
            
                    <div class="form-group">
                        <label for="count">count</label>
                        <div class="row my-3 transfer-number-input justify-content-center">
                            <div class="col col-3 col-lg-4 col-xl-2">
                                <button id="count-reducer" type="button" class="btn btn-block btn-primary text-center">-</button>
                            </div>
                            <div class="col col-6 col-lg-4 ">
                                <input id="count" name="count" type="number" class="form-control text-center" min="1" value="2">
                            </div>
                            <div class="col col-3 col-lg-4 col-xl-2">
                                <button id="count-increaser" type="button" class="btn btn-block btn-primary text-center">+</button>
                            </div>
                        </div>
                    </div>
                        
                <button type="submit" style="padding: 2%" class="btn btn-success btn-lg d-block mx-auto btn-prevent-multiple-submits"><i class="fa fa-paper-plane" aria-hidden="true"></i> Transfer To Slaughter</button>
                </form>
            </div>
         </div>

        <div class="col-12 col-lg-4">
            <div class="card">
                <h3 class="card-header">Todays Transfers Summary</h3>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Baconers: {{ $transfers->where('product_code', 'G0101')->sum('count') }}</li>
                    <li class="list-group-item">Sow: {{ $transfers->where('product_code', 'G0102')->sum('count') }}</li>
                    <li class="list-group-item">Suckling: {{ $transfers->where('product_code', 'G0104')->sum('count') }}</li>
                    <li class="list-group-item">Total Transferred: {{ $transfers->sum('count') }}</li>
                </ul>
            </div>
        </div>

    </div>

    <hr/>

    <button class="btn btn-primary " data-toggle="collapse" data-target="#transfer_entries">
        <i class="fa fa-plus"></i>
        Entries
    </button>
     
    <hr/>

    <div id="transfer_entries" class="card collapse">
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
                            <th scope="col">User</th>
                            <th scope="col" class="no-export">Edit</th>
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
                                <td>{{ $transfer->username }}</td>
                                <td class="no-export">
                                    <button class="btn btn-primary" >
                                        <i
                                            class="fa fa-pencil-alt"
                                            data-toggle="modal"
                                            data-target="#editTransferModal"
                                            data-transfer-id={{ $transfer->id }}
                                            data-count={{ $transfer->count }}
                                            onclick="updateTransferId(event)"
                                        ></i>
                                    </button>
                                </td>
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

</div>

@endsection


@section('scripts')

<script>

let transferIdInput = document.getElementById('transfer_id');
let editingCountSpan = document.getElementById('editing_count');

    function updateTransferId(event) {
        let button = event.target;
        let transferId = button.getAttribute('data-transfer-id');
        let editingCount = button.getAttribute('data-editing-count');
        transferIdInput.value = transferId;
        editingCountSpan.innerHTML = editingCount;
    }

    $('.form-prevent-multiple-submits').on('submit', function () {
        $(".btn-prevent-multiple-submits").attr('disabled', true);
    });

    const number_input = document.querySelector('#count');
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

    $('#editTransferModal').on('hidden.bs.modal', function () {
        document.getElementById('edit_transfer_form').reset();
        document.getElementById('editing_count').innerHTML = '';
    });

</script>

@endsection
