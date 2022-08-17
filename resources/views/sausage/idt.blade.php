@extends('layouts.sausage_master')

@section('content-header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-5">
            <h1 class="m-0"> {{ $title }} |<small>Create & View <strong></strong> Transfers Lines </small></h1>
        </div><!-- /.col -->
        <div class="col-sm-7">
            <button class="btn btn-primary " data-toggle="collapse" data-target="#toggle_collapse"><i
                    class="fas fa-plus"></i>
                Create
                New IDT</button>
        </div>
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<div id="toggle_collapse" class="collapse">
    <form id="form-save-batch" class="form-prevent-multiple-submits" action="{{ route('batches_create') }}"
        method="post">
        @csrf
        <div class="card-group">
            <div class="card">
                <div class="card-body" style="">
                    <div class="form-group">
                        <div class="row">
                            <label for="inputEmail3" class="col-sm-3 col-form-label">Product Name </label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="product" id="product" required>
                                    <option value="">Select Product</option>
                                    @foreach($items as $tm)
                                    <option value="{{ $tm->code }}">
                                        {{ $tm->barcode.' '.$tm->description }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div><br>
                        <div class="row">
                            <label for="inputEmail3" class="col-sm-3 col-form-label">Unit Count Per Crate </label>
                            <div class="col-sm-9">
                                <input type="email" readonly class="form-control" value="0" id="create_count"
                                    name="status" placeholder="" name="create_count">
                            </div>
                        </div>
                        <div class="row">
                            <label for="inputEmail3" class="col-sm-3 col-form-label">Item Unit Measure </label>
                            <div class="col-sm-9">
                                <input type="email" readonly class="form-control" value="open" id="unit_measure"
                                    name="status" placeholder="" name="unit_measure">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body form-group">
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">From </label>
                        <div class="col-sm-9">
                            <select class="form-control select2" name="from" id="from" required>
                                <option value="">Select Section</option>
                                {{-- @foreach($templates as $tm)
                                    <option value="{{ $tm->template_no.'-'.$tm->template_output }}">
                                {{ $tm->template_name }}
                                </option>
                                @endforeach --}}
                            </select>
                        </div>
                    </div><br>
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">To </label>
                        <div class="col-sm-9">
                            <select class="form-control select2" name="temp_no" id="temp_no" required>
                                <option value="">Select chiller</option>
                                {{-- @foreach($templates as $tm)
                                    <option value="{{ $tm->template_no.'-'.$tm->template_output }}">
                                {{ $tm->template_name }}
                                </option>
                                @endforeach --}}
                            </select>
                        </div>
                    </div><br>
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">Total Crates</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="output_product" name="output_product" value=""
                                placeholder="" readonly>
                        </div>
                    </div><br>
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">No. of full Crates</label>
                        <div class="col-sm-9">
                            <input type="email" readonly class="form-control" value="open" id="status" name="status"
                                placeholder="" readonly>
                        </div>
                    </div><br>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center form-group">
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">Pieces</label>
                        <div class="col-sm-9">
                            <input type="email" readonly class="form-control" value="open" id="pieces" name="status"
                                placeholder="" readonly>
                        </div>
                    </div><br>
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">Weight</label>
                        <div class="col-sm-9">
                            <input type="email" readonly class="form-control" value="open" id="weight" name="status"
                                placeholder="" readonly>
                        </div>
                    </div>
                    <hr><br>
                    <div class="row">
                        <label for="inputEmail3" class="col-sm-3 col-form-label">Dispatch Receiver</label>
                        <div class="col-sm-9">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="email" readonly class="form-control" value="open" id="username"
                                        name="status" placeholder="" readonly>
                                </div>
                                <div class="col-md-4">
                                    <input type="email" readonly class="form-control" value="open" id="password"
                                        name="status" placeholder="" readonly>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-success"><i class="fa fa-paper-plane"
                                            aria-hidden="true"></i>
                                        Validate</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="div" style="padding-top: ">
                        <button type="submit" class="btn btn-primary btn-lg btn-prevent-multiple-submits"><i
                                class="fa fa-paper-plane single-click" aria-hidden="true"></i> Post</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div id="loading" class="collapse">
        <div class="row d-flex justify-content-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>
</div><br>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"> Transfer Lines Entries | <span id="subtext-h1-title"><small> showing all
                            <strong></strong> entries
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
                                @if ($filter == 'open' || $filter == '')
                                <th>created By</th>

                                @elseif ($filter == 'closed')
                                <th>closed By</th>
                                @elseif ($filter == 'posted')
                                <th>posted By</th>
                                @endif

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
                                @if ($filter == 'open' || $filter == '')
                                <th>created By</th>

                                @elseif ($filter == 'closed')
                                <th>closed By</th>
                                @elseif ($filter == 'posted')
                                <th>posted By</th>
                                @endif
                                <th>Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            {{-- @foreach($transfer_lines as $data)
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

        $('#product').change(function () {
            let product_code = $(this).val()
            loadProductDetails(product_code);
        });

        const loadProductDetails = (prod_code) => {
            $('#loading').collapse('show');

            const url = '/item/details-axios'

            const request_data = {
                product_code: prod_code
            }

            return axios.post(url, request_data)
                .then((response) => {
                    $('#loading').collapse('hide');

                    console.log(response.data)
                    $('#crate_count').val(response.data.unit_count_per_crate)
                    $('#unit_measure').val(response.data.qty_per_unit_of_measure)
                })
                .catch((error) => {
                    console.log(error);
                })
        }

    });

</script>
@endsection
