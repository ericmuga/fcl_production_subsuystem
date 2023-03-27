@extends('layouts.spices_master')

@section('content-header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-7">
            <h1 class="m-0"> {{ $title }} |<small>Showing all Chopping Batches </small> from date <strong>
                    {{ $helpers->formatTodateOnly($date_filter) }}</strong></h1>
        </div><!-- /.col -->
        <div class="col-sm-5">
            <button class="btn btn-primary " data-toggle="collapse" data-target="#toggle_collapse"><i
                    class="fas fa-plus"></i>
                Create
                New Batch</button>
        </div>
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<div id="toggle_collapse" class="collapse">
    <form id="form-save-batch" class="form-prevent-multiple-submits" action="{{ route('chopping_batch_save') }}"
        method="post">
        @csrf
        <div class="card-group">
            <div class="card">
                <div class="card-body" style="">
                    <div class="form-group">
                        <div class="row">
                            <label for="inputEmail3" class="col-sm-3 col-form-label">Template No</label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="temp_no" id="temp_no" required>
                                    <option value="">Select template</option>
                                    @foreach($templates as $tm)
                                    <option value="{{ $tm->template_no.'-'.$tm->template_output }}">
                                        {{ $tm->template_no.'-'.$tm->template_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div><br>
                        <div class="row form-group">
                            <div class="col-md-4">
                                <label for="exampleInputPassword1">From Batch No:</label>
                                <input type="number" class="form-control" id="total_tare" name="total_tare" value="0.00"
                                    step=".01" placeholder="" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="exampleInputPassword1">To Batch No:</label>
                                <input type="number" class="form-control" id="net" name="net" value="0.00" step=".01"
                                    placeholder="" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="exampleInputPassword1">Batch Size</label>
                                <input type="number" class="form-control" id="net" name="net" value="0.00" step=".01"
                                    placeholder="" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body form-group">
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">Output Product</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="output_product" name="output_product" value=""
                                placeholder="" readonly>
                        </div>
                    </div><br>
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">Status</label>
                        <div class="col-sm-9">
                            <input type="email" readonly class="form-control" value="open" id="status" name="status"
                                placeholder="" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body text-center form-group">
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">Output Qty</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="output_qty" name="output_qty" value=""
                                placeholder="" step="0.01" required>
                        </div>
                    </div><br>
                    <div class="div" style="padding-top: ">
                        <button type="submit" class="btn btn-primary btn-lg btn-prevent-multiple-submits"><i
                                class="fa fa-paper-plane single-click" aria-hidden="true"></i> Run</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div><br>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"> Production Lines Entries | <span id="subtext-h1-title"><small> showing all
                            entries
                            ordered by
                            latest</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example1" class="table table-striped table-bordered table-hover" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Batch No</th>
                                <th>Template No</th>
                                <th>Template</th>
                                <th>Status</th>
                                <th>Output Product</th>
                                <th>Output Quantity</th>
                                {{-- @if ($filter == 'open' || $filter == '')
                                    <th>created By</th>
                                    
                                @elseif ($filter == 'closed')
                                    <th>closed By</th>
                                @elseif ($filter == 'posted')
                                    <th>posted By</th>
                                @endif --}}

                                <th>Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Batch No</th>
                                <th>Template No</th>
                                <th>Template</th>
                                <th>Status</th>
                                <th>Output Product</th>
                                <th>Output Quantity</th>
                                {{-- @if ($filter == 'open' || $filter == '')
                                    <th>created By</th>
                                    
                                @elseif ($filter == 'closed')
                                    <th>closed By</th>
                                @elseif ($filter == 'posted')
                                    <th>posted By</th>
                                @endif --}}
                                <th>Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            {{-- @foreach($batches as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                            <td><a href="{{ route('production_lines', $data->batch_no) }}">{{ $data->batch_no }}</a>
                            </td>
                            <td>{{ $data->template_no }}</td>
                            <td>{{ $data->template_name }}</td>

                            @if ($data->status == 'open')
                            <td><span class="badge badge-success">Open</span></td>
                            @elseif ($data->status == 'closed')
                            <td><span class="badge badge-warning">Closed</span></td>
                            @else
                            <td><span class="badge badge-danger">Posted</span></td>
                            @endif

                            <td>{{ $data->template_output }}</td>
                            <td>{{ $data->output_quantity }}</td>
                            <td>{{ $data->username }}</td>
                            <td>{{ $helpers->amPmDate($data->created_at) }}</td>
                            </tr>
                            @endforeach --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
        <!-- /.col -->
    </div>
</div>
<!-- slicing ouput data show -->

@endsection

@section('scripts')
<script>
    $(document).ready(function () {

        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        $('#temp_no').change(function () {
            var temp_no = $(this).val();
            var output = $('#output_product').val(temp_no.substring(temp_no.indexOf('-') + 1));
            $('#temp_no').select2('destroy').select2();
            $('#output_qty').focus()
        });
    });

</script>
@endsection
