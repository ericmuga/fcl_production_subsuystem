@extends('layouts.admin_master')

@section('content')

<!-- /.card -->
<!-- Horizontal Form -->
<div class="row">
    <div class="card col-md-8" style="margin: 0 auto; float: none;">
        <div class="card-header">
            <h3 class="card-title"> Password | <span id="subtext-h1-title"><small> change current password</small> </span></h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form class="form-horizontal">
            <div class="card-body">
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-2 col-form-label">Current Password</label>
                    <div class="col-sm-10">
                        <input type="password" class="form-control" id="baud_rate" name="baud_rate" placeholder="" autofocus>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-2 col-form-label">New Password</label>
                    <div class="col-sm-10">
                        <input type="password" class="form-control" id="baud_rate" name="baud_rate" placeholder="">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputPassword3" class="col-sm-2 col-form-label">Confirm New Password</label>
                    <div class="col-sm-10">
                        <input type="password" class="form-control" id="comport" name="comport" placeholder="">
                    </div>
                </div>
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
                <button type="submit" class="btn btn-primary float-right"><i class="fa fa-paper-plane"
                        aria-hidden="true"></i> Save</button>
            </div>
            <!-- /.card-footer -->
        </form>
    </div>
    <!-- /.card -->
</div>
@endsection
