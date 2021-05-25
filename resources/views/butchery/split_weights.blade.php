@extends('layouts.butchery_master')

@section('content')

@php
$to_split = \Session::get('data');

@endphp

<div class="div">
    <button class="btn btn-primary " data-toggle="collapse" data-target="#split_date"><i class="fa fa-plus"></i>
        Split weights
    </button>
</div>

<!-- create product-->
<div id="split_date" class="collapse">
    <hr>
    <div class="form-inputs">
        <div class="row">
            <div class="col-lg-8" style="margin: 0 auto; float: none;">
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="fa fa-users"></i>
                        Enter date to split</div>
                    <div class="card-body">
                        <form action="{{ route('load_split_data') }}" method="post" id="add-branch-form">
                            @csrf
                            <div class="form-group">
                                <label>Date:format(mm/dd/yyyy)</label>
                                <div class="input-group date" id="reservationdate" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input" id="dateinput"
                                        name="dateinput" data-target="#reservationdate"
                                        value="{{ \Session::get('display_date') }}" required />
                                    <div class="input-group-append" data-target="#reservationdate"
                                        data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary btn-lg float-right"><i
                                        class="fa fa-paper-plane" aria-hidden="true"></i> Load
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--End create product-->

<div class="card collapse" id="splitting_tableshow">
    <div class="card-header">
        <h3 class="card-title"> Split weights | <span id="subtext-h1-title"><small> showing unsplitted deboned
                    weights filtered by date: <code
                        style="font-size: 15px"><strong>{{ Session::get('display_date') }}</strong></code></small>
            </span></h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
        <div class="hidden" hidden>{{ $i = 1 }}</div>
        <div class="table-responsive">
            <table id="example1" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code </th>
                        <th>product </th>
                        <th>Cummulative Weight(kgs)</th>
                        <th>Action</th>
                        {{-- <th>Date </th> --}}
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>Code </th>
                        <th>product </th>
                        <th>Cummulative Weight(kgs)</th>
                        <th>Action</th>
                        {{-- <th>Date </th> --}}
                    </tr>
                </tfoot>
                <tbody>
                    @if (isset($to_split))

                    @foreach($to_split as $data)
                    <tr>
                        <td>{{ $i++ }}</td>
                        <td> {{ $data->item_code }}</td>
                        <td>{{ $helpers->getProductName($data->item_code) }}</td>
                        <td> {{ number_format($data->total_weight, 2) }}</td>
                        <td>
                            <button type="button" data-item="{{ $helpers->getProductName($data->item_code) }}"
                                data-total_weight="{{ $data->total_weight }}" class="btn btn-warning btn-sm "
                                id="splittingModalShow"><i class="nav-icon fas fa-divide"></i>
                                Split</button>
                        </td>
                        {{-- <td> {{ $data->created_at }}</td> --}}
                    </tr>
                    @endforeach
                    @else
                    <td> No date selected</td>

                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <!-- /.card-body -->
</div>

<div id="splitted_output_show" class="">
    <hr>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"> Splitted Entries | <span id="subtext-h1-title"><small> All Output</small>
                        </span></h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="hidden" hidden>{{ $i = 1 }}</div>
                    <div class="table-responsive">
                        <table id="example2" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Parent Item</th>
                                    <th>New Item</th>
                                    <th>Weight(kgs)</th>
                                    <th>Process</th>
                                    <th>Percentage</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Parent Item</th>
                                    <th>New Item</th>
                                    <th>Weight(kgs)</th>
                                    <th>Process</th>
                                    <th>Percentage</th>
                                    <th>Date</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                @foreach($splitted_data as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->parent_item }}</td>
                                    <td>{{ $data->new_item }}</td>
                                    <td> {{ number_format($data->net_weight, 2) }}</td>
                                    <td>{{ $data->process_code }}</td>
                                    <td>{{ $data->percentage }}</td>
                                    <td>{{ $data->created_at }}</td>
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
    </div>
    <!-- /.col -->
</div>

<!-- Start splitting Modal -->
<div id="splittingModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <form id="form-split-weights" action="{{ route('butchery_split_save') }}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Parent Item: <code><strong><input style="border:none"
                                    type="text" id="item_name" name="item_name" value="" readonly></strong></code></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="div form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="baud">New Item 1:</label>
                                <select class="form-control select2" name="new_item1" id="new_item1"
                                    value="{{ old('new_item1') }}">
                                    <option value="" selected disabled>Select Item</option>
                                    @foreach($products as $p)
                                    <option value="{{ $p->code }}">{{ $p->description }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="baud">New Process:</label>
                                <select class="form-control select2" name="new_process1" id="new_process1"
                                    value="{{ old('new_process1') }}">
                                    <option value="" selected disabled>Select Item</option>
                                    @foreach($processes as $p)
                                    <option value="{{ $p->process_code }}">{{ $p->process }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="baud">%:</label>
                                <input type="number" class="form-control" id="percent1" name="percent1" value=""
                                    placeholder="" oninput="getPercent1()" required>

                            </div>
                            <div class="col-md-3">
                                <label for="baud">New weight:</label>
                                <input type="number" class="form-control" id="new_weight1" name="new_weight1" value=""
                                    placeholder="" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="div form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="baud">New Item 2:</label>
                                <select class="form-control select2" name="new_item2" id="new_item2"
                                    value="{{ old('new_item2') }}">
                                    <option value="" selected disabled>Select Item</option>
                                    @foreach($products as $p)
                                    <option value="{{ $p->code }}">{{ $p->description }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="baud">New Process:</label>
                                <select class="form-control select2" name="new_process2" id="new_process2"
                                    value="{{ old('new_process2') }}">
                                    <option value="" selected disabled>Select Item</option>
                                    @foreach($processes as $p)
                                    <option value="{{ $p->process_code }}">{{ $p->process }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="baud">%:</label>
                                <input type="number" class="form-control" oninput="getPercent2()" id="percent2"
                                    name="percent2" value="" placeholder="">

                            </div>
                            <div class="col-md-3">
                                <label for="baud">New weight:</label>
                                <input type="number" class="form-control" id="new_weight2" name="new_weight2" value=""
                                    placeholder="" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="div form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="baud">New Item 3:</label>
                                <select class="form-control select2" name="new_item3" id="new_item3"
                                    value="{{ old('new_item3') }}">
                                    <option value="" selected disabled>Select Item</option>
                                    @foreach($products as $p)
                                    <option value="{{ $p->code }}">{{ $p->description }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="baud">New Process:</label>
                                <select class="form-control select2" name="new_process3" id="new_process3"
                                    value="{{ old('new_process3') }}">
                                    <option value="" selected disabled>Select Item</option>
                                    @foreach($processes as $p)
                                    <option value="{{ $p->process_code }}">{{ $p->process }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="baud">%:</label>
                                <input type="number" class="form-control" oninput="getPercent3()" id="percent3"
                                    name="percent3" value="" placeholder="">

                            </div>
                            <div class="col-md-3">
                                <label for="baud">New weight:</label>
                                <input type="number" class="form-control" id="new_weight3" name="new_weight3" value=""
                                    placeholder="" readonly>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="total_weight" value="">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" onclick="return checkPercentOnSubmit()" type="submit">
                        <i class="fa fa-save"></i> Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- End splitting modal-->

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        // split weights
        $("body").on("click", "#splittingModalShow", function (a) {
            a.preventDefault();

            var name = $(this).data('item');
            var total_weight = $(this).data('total_weight');

            $('#item_name').val(name);
            $('#total_weight').val(total_weight);

            $('#splittingModal').modal('show');
        });

    });

    function checkPercentOnSubmit() {
        var p1 = $('#percent1').val();
        var p2 = $('#percent2').val();
        var p3 = $('#percent3').val();
        var valid = true;
        var sum = +p1 + +p2 + +p3;
        if (sum != 100) {
            valid = false;
            alert("Please ensure the percentage value adds up to 100.current value: " + sum)
        }
        return valid;
    }

    function getPercent1() {
        document.getElementById("new_item1").required = true;
        document.getElementById("new_process1").required = true;
        var percent1 = $('#percent1').val();
        var total_weight = $('#total_weight').val();
        var new_split = (percent1 * total_weight) / 100;
        // alert(percent1);
        $("#new_weight1").val(Math.round((new_split + Number.EPSILON) * 100) / 100);
    }

    function getPercent2() {
        document.getElementById("new_item2").required = true;
        document.getElementById("new_process2").required = true;
        var percent2 = $('#percent2').val();
        var total_weight = $('#total_weight').val();
        var new_split = (percent2 * total_weight) / 100;
        // alert(percent1);
        $("#new_weight2").val(Math.round((new_split + Number.EPSILON) * 100) / 100);
    }

    function getPercent3() {
        document.getElementById("new_item3").required = true;
        document.getElementById("new_process3").required = true;
        var percent3 = $('#percent3').val();
        var total_weight = $('#total_weight').val();
        var new_split = (percent3 * total_weight) / 100;
        // alert(percent1);
        $("#new_weight3").val(Math.round((new_split + Number.EPSILON) * 100) / 100);
    }

    jQuery(function ($) {
        var cloneCount = 1;
        var $button = $('#add-row'),
            $row = $('.timesheet-row').clone().prop('id', cloneCount + 1);

        $button.click(function () {
            $row.clone().insertBefore($button);
        });
    });

</script>

@if( Session::get('splitting_table') == 'show' )
<script>
    $(function () {
        // $('#myModal').modal('show');
        $('#splitting_tableshow').toggle('collapse');

    });

</script>
@endif

@endsection
