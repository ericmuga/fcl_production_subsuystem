@extends('layouts.router')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($users as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ ucwords($data->username) }}</td>
                                <td>{{ $data->email }}</td>
                                <td>{{ $data->role }}</td>
                                <td>{{ $helpers->amPmDate($data->created_at) }}</td>
                                <td>
                                    <button type="button" data-id="{{$data->id}}" data-name="{{$data->username}}"
                                        data-role="{{ $data->role }}" class="btn btn-info btn-xs" id="editUserModalShow"
                                        data-toggle="tooltip" title="edit"><i class="fas fa-edit"></i>
                                    </button>
                                </td>
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
    <!-- /.col -->
</div>
<!-- Start edit user Modal -->
<div id="editUserModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <form id="form-user-update" class="form-horizontal form-prevent-multiple-submits"
            action="" method="post">
            @csrf
            <div class="modal-content">
                <div class="card">
                    <div class="modal-header">
                        <h5 class="login-box-msg">Edit User: <strong><input style="border:none" type="text"
                                    id="item_name" name="item_name" value="" readonly></strong></h5>
                    </div>
                    <div class="card-body register-card-body">
                        <div class="modal-body">
                            <div class="form-group row">
                                <label for="email" class="col-form-label">Username</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control input_checks" name="editname" id="editname"
                                        required>
                                </div>
                            </div>
                            <input type="hidden" name="user_id" id="user_id" value="">
                        </div>
                        <div class="modal-footer">
                            <div class="form-group">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" id="btnupdateUser"
                                    class="btn btn-warning btn-lg btn-prevent-multiple-submits"><i
                                        class="fa fa-paper-plane" aria-hidden="true"></i>
                                    Update
                                    User
                                </button>
                            </div>
                        </div>
                        <!-- /.form-box -->
                    </div><!-- /.card -->
                </div>
            </div>
        </form>
    </div>
</div>
<!-- End edit user modal-->
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        // Edit
        $("body").on("click", "#editUserModalShow", function (a) {
            a.preventDefault();

            let id = $(this).data('id');
            let username = $(this).data('name');

            $('#user_id').val(id);
            $('#editname').val(username);

            // to enable selected, destroy select2 first
            $('#editrole').select2('destroy').select2();

            $('#editUserModal').modal('show');
        });

    });

</script>
@endsection
