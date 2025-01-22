@extends('admin.layouts.app')

@section('title', '个人资料')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">修改资料</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label>用户名</label>
                            <input type="text" name="name" class="form-control" 
                                value="{{ old('name', Auth::guard('admin')->user()->name) }}" required>
                        </div>

                        <div class="form-group mt-3">
                            <label>邮箱</label>
                            <input type="email" name="email" class="form-control" 
                                value="{{ old('email', Auth::guard('admin')->user()->email) }}" required>
                        </div>

                        <div class="form-group mt-3">
                            <label>手机号</label>
                            <input type="tel" name="phone" class="form-control" 
                                value="{{ old('phone', Auth::guard('admin')->user()->phone) }}" required>
                        </div>

                        <button type="submit" class="btn btn-primary mt-4">保存修改</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 