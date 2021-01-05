@extends('layouts.slaughter_master')

@section('content')

<div class="div">
    <button class="btn btn-primary " data-toggle="collapse" data-target="#import_receipts"><i class="fas fa-file-excel"></i> Import
        New Receipts</button> <br> <br>
</div>

<!--End create product-->
<div id="import_receipts" class="row collapse">
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
<!--End create product-->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"></h3>
                <h3 class="card-title"> Imported Receipts Entries | <span id="subtext-h1-title"><small> view, filter, print/download</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Rendering engine</th>
                            <th>Browser</th>
                            <th>Platform(s)</th>
                            <th>Engine version</th>
                            <th>CSS grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Trident</td>
                            <td>Internet
                                Explorer 4.0
                            </td>
                            <td>Win 95+</td>
                            <td> 4</td>
                            <td>X</td>
                        </tr>
                        <tr>
                            <td>Misc</td>
                            <td>Dillo 0.8</td>
                            <td>Embedded devices</td>
                            <td>-</td>
                            <td>X</td>
                        </tr>
                        <tr>
                            <td>Misc</td>
                            <td>Links</td>
                            <td>Text only</td>
                            <td>-</td>
                            <td>X</td>
                        </tr>
                        <tr>
                            <td>Misc</td>
                            <td>Links</td>
                            <td>Text only</td>
                            <td>-</td>
                            <td>X</td>
                        </tr>
                        <tr>
                            <td>Misc</td>
                            <td>Links</td>
                            <td>Text only</td>
                            <td>-</td>
                            <td>X</td>
                        </tr>
                        <tr>
                            <td>Misc</td>
                            <td>Links</td>
                            <td>Text only</td>
                            <td>-</td>
                            <td>X</td>
                        </tr>
                        <tr>
                            <td>Misc</td>
                            <td>Links</td>
                            <td>Text only</td>
                            <td>-</td>
                            <td>X</td>
                        </tr>
                        <tr>
                            <td>Misc</td>
                            <td>Links</td>
                            <td>Text only</td>
                            <td>-</td>
                            <td>X</td>
                        </tr>
                        <tr>
                            <td>Misc</td>
                            <td>Links</td>
                            <td>Text only</td>
                            <td>-</td>
                            <td>X</td>
                        </tr>
                        <tr>
                            <td>Misc</td>
                            <td>Links</td>
                            <td>Text only</td>
                            <td>-</td>
                            <td>X</td>
                        </tr>
                        <tr>
                            <td>Misc</td>
                            <td>Links</td>
                            <td>Text only</td>
                            <td>-</td>
                            <td>X</td>
                        </tr>
                        <tr>
                            <td>Misc</td>
                            <td>Links</td>
                            <td>Text only</td>
                            <td>-</td>
                            <td>X</td>
                        </tr>
                        <tr>
                            <td>Misc</td>
                            <td>Links</td>
                            <td>Text only</td>
                            <td>-</td>
                            <td>X</td>
                        </tr>
                        <tr>
                            <td>Misc</td>
                            <td>Lynx</td>
                            <td>Text only</td>
                            <td>-</td>
                            <td>X</td>
                        </tr>
                        <tr>
                            <td>Misc</td>
                            <td>IE Mobile</td>
                            <td>Windows Mobile 6</td>
                            <td>-</td>
                            <td>C</td>
                        </tr>
                        <tr>
                            <td>Misc</td>
                            <td>PSP browser</td>
                            <td>PSP</td>
                            <td>-</td>
                            <td>C</td>
                        </tr>
                        <tr>
                            <td>Other browsers</td>
                            <td>All others</td>
                            <td>-</td>
                            <td>-</td>
                            <td>U</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Rendering engine</th>
                            <th>Browser</th>
                            <th>Platform(s)</th>
                            <th>Engine version</th>
                            <th>CSS grade</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>
@endsection
