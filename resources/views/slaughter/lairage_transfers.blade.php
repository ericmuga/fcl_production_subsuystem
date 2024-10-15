@extends('layouts.slaughter_master')


@section('styles')
    <link rel="stylesheet" href="/css/transfers.css">
@endsection



@section('content')

<div class="container">
    <div class="row mb-2">

        <div class="col-12 col-md-8">
            <div class="card">
                <h3 class="card-header">
                Record Transfer
                </h3>
                <form class="card-body" action="{{ route('save_idt_lairage') }}" method="POST">
                    @csrf
                    <div class="form-group flex-grow-0">
                        <label for="product_code">Animal Type</label>
                        <select id="product_code" name="product_code" class="form-control select2" aria-label="Default select example">
                            <option value="G0101">G0101 Baconer</option>
                            <option value="G0102">G0102 Sow</option>
                            <option value="G0104">G0104 Suckling</option>
                        </select>
                    </div>
            
                    <div class="container">
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
            
                    <input type="hidden" name="transfer_from" id="transfer_from" value="1000" required>
                    <input type="hidden" name="location_code" id="location_code" value="1010" required>
                    <input type="hidden" name="crates_valid" id="crates_valid" value="1" required>
                    <input type="hidden" name="total_crates" id="total_crates" value="0" required>
                    <input type="hidden" name="full_crates" id="full_crates" value="0" required>
                    <input type="hidden" name="incomplete_crate_pieces" id="incomplete_crate_pieces" value="0" required>
                    
            
                <button type="submit" class="btn btn-primary btn-lg d-block mx-auto">Transfer</button>
                </form>
            </div>
         </div>

        <div class="col-12 col-md-4">
            <div class="card">
                <h3 class="card-header">Todays Transfers Summary</h3>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Baconners: {{ $transfers->where('product_code', 'G0101')->sum('total_pieces') }}</li>
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
                                    <th scope="col">Edit Flag</th>
                                    <th scope="col">Time Posted</th>
                                    <th scope="col">User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transfers as $index => $transfer)
                                    <tr>
                                        <th scope="row">{{ $index + 1 }}</th>
                                        <td>{{ $transfer->product_code }}</td>
                                        <td>{{ $transfer->total_pieces }}</td>
                                        <td>{{ $transfer->edited }}</td>
                                        <td>{{ $transfer->created_at }}</td>
                                        <td>{{ $transfer->id }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>





@endsection


@section('scripts')

<script>

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

</script>

@endsection
