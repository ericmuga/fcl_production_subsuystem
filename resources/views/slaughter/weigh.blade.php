@extends('layouts.slaughter_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"> {{ $title }}<small></small></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<form>
    <div class="card-group">
        <div class="card">
            <div class="card-body text-center" style="padding-top: 50%">
                <button type="submit" class="btn btn-primary btn-lg">Weigh</button>
            </div>
        </div>
        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <label for="exampleInputEmail1">Reading</label>
                    <input type="text" class="form-control" id="reading" name="reading" placeholder="">
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Tare-Weight</label>
                    <input type="text" class="form-control" id="tareweight" placeholder="">
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Net</label>
                    <input type="text" class="form-control" id="tareweight" placeholder="">
                </div>
            </div>
        </div>
        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <label for="exampleInputPassword1">Receipt No.</label>
                    <select class="form-control" name="receipt_no" id="receipt_no" required>
                        <option value="" selected disabled>select</option>
                        <option>option 1</option>
                        <option>option 2</option>
                        <option>option 3</option>
                        <option>option 4</option>
                        <option>option 5</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Slapmark</label>
                    <select class="form-control" name="slapmark" id="slapmark" required>
                        <option value="" selected disabled>select</option>
                        <option>option 1</option>
                        <option>option 2</option>
                        <option>option 3</option>
                        <option>option 4</option>
                        <option>option 5</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Carcass Type</label>
                    <select class="form-control" name="carcass_type" id="carcass_type" required>
                        <option value="" selected disabled>select</option>
                        <option>option 1</option>
                        <option>option 2</option>
                        <option>option 3</option>
                        <option>option 4</option>
                        <option>option 5</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Vendor Number</label>
                    <input type="text" class="form-control" name="vendor_no" id="vendor_no" placeholder="" readonly>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Vendor Name</label>
                    <input type="text" class="form-control" name="vendor_name" id="vendor_name"  placeholder="" readonly>
                </div>
            </div>
        </div>
        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <label for="exampleInputPassword1">Meat %</label>
                    <input type="text" class="form-control" id="meat_percent" name="meat_percent" placeholder="" disabled>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Classification Code</label>
                    <input type="text" class="form-control" id="tareweight" placeholder="" readonly>
                </div>
                <div class="form-group" style="padding-top: 20%">
                    <button type="submit" class="btn btn-primary btn-lg">Post</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
    <script>
        $('#carcass_type').change(function(){
            var carcass_type = $(this).val();
            var meat_percent = document.getElementById('meat_percent');
            // alert(carcass_type);
            if (carcass_type.value == ''){
                meat_percent.disabled = true;
            }else {
                meat_percent.disabled = false;
            }
        });
    </script>
@endsection
