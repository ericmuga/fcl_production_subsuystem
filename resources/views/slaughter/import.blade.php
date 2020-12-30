@extends('layouts.slaughter_master')

@section('content')
<div class="row">
    <div class="col-md-10" style="padding-left: 20%">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Import receipts</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form>
                <div class="card-body">
                    <div class="form-group">
                        <label for="exampleInputFile">File Upload</label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="exampleInputFile">
                                <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary float-right"><i class="fa fa-paper-plane"
                            aria-hidden="true"></i> Upload</button>
                    </div>
                </div>
                <!-- /.card-body -->
            </form> <br>
            @if(count($errors))
                <ol>
                    <h6><span class="label label-danger">Errors</span></h6>
                    @foreach($errors->all() as $error)
                        <li> <code>{{ $error }}</code></li>
                    @endforeach
                </ol>
            @endif
        </div>
    </div>
</div>
@endsection
