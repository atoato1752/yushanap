@extends('admin.layouts.app')

@section('title', '修改密码')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">修改密码</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.password.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label>当前密码</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>

                        <div class="form-group mt-3">
                            <label>新密码</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="form-group mt-3">
                            <label>确认新密码</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary mt-4">修改密码</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 