@extends('layouts.admin_master')

@section('content')

<div class="div" >
    <button class="btn btn-primary " data-toggle="collapse" data-target="#add_user"><i class="fa fa-plus"></i> Add
        New User</button> <br> <br>
</div>

<!-- create user-->
<div id="add_user" class="collapse"><br>
    <div class="form-inputs"  style="padding-left: 20%">
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="fa fa-users"></i>
                        Add User</div>
                    <div class="card-body">
                        <form action="{{ route('admin_add_user') }}" method="post" id="add-branch-form">
                            @csrf
                            <div class="form-group">
                                <label for="role_name">Username:</label>
                                <input max="50" min="3" autocomplete="off" type="text"
                                    class="form-control" id="username" name="username" value="{{ old('username') }}"
                                    required>

                                @error('username')
                                    <div class="error alert alert-danger alert-dismissible fade show">{{ $message }}</div>
                                @enderror

                            </div>
                            <div class="form-group">
                                <label for="role_name">Email:</label>
                                <input max="50" min="3" autocomplete="off" type="email"
                                    class="form-control" id="email" name="email" value="{{ old('email') }}"
                                    required>

                                @error('email')
                                    {{-- <div class="error alert alert-danger alert-dismissible fade show">{{ $message }}</div> --}}
                                    <div class="alert alert-warning alert-dismissible fade show">
                                        {{ $message }}
                                      </div>
                                @enderror

                            </div>
                            <div class="form-group">
                                <label for="role_name">Section:</label>
                                <select name="section" id="section" class="form-control" required
                                    autofocus>
                                    <option value="" selected disabled>Choose One</option>
                                    <option value="slaughter"> Slaughter</option>
                                    <option value="butchery"> Butchery</option>
                                    <option value="admin"> IT/Admin</option>
                                </select>

                                @error('section')
                                    <div class="error alert alert-danger alert-dismissible fade show">{{ $message }}</div>
                                @enderror

                            </div> <br>
                            <div>
                                <button type="submit" class="btn btn-success float-right"><i class="fa fa-paper-plane"
                                    aria-hidden="true"></i> Save
                                </button>
                            </div>

                        </form>
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--End create user-->

<!-- users Table-->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"></h3>
                <h3 class="card-title"> User Registry | <span id="subtext-h1-title"><small> view, add, reset users</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <table id="example1" class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Email </th>
                            <th>Section </th>
                            <th>Edit</th>
                            <th>Toggle</th>
                            <th>Date Created</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Email </th>
                            <th>Section </th>
                            <th>Edit</th>
                            <th>Toggle</th>
                            <th>Date Created</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->section }}</td>
                            <td>
                                <button type="button" data-id="{{$user->id}}" data-username="{{$user->username}}"
                                class="btn btn-primary fa fa-pencil btn-sm " id="edituserModalShow"> Edit
                                </button>
                            </td>
                            <td>
                                <button type="button" data-id="{{$user->id}}" data-username="{{$user->username}}"
                                    class="btn btn-info fa fa-toggle-on btn-sm " id="toggleModalShow"> Toggle
                                </button>
                            </td>
                            <td>
                                {{ $user->created_at }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>
<!--End users Table-->
@endsection

@section('scripts')
@if(Session::get('input_errors') == 'add_user' )
<script>
    $(function() {
        // $('#myModal').modal('show');
        $('#add_user').toggle('collapse');

    });
</script>
@endif
@endsection
