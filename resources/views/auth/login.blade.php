@extends('layouts.app')

@section('title', '用户登录')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">用户登录</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group">
                            <label>手机号</label>
                            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                value="{{ old('phone') }}" required autofocus>
                            @error('phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mt-3">
                            <label>密码</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-check mt-3">
                            <input type="checkbox" name="remember" id="remember" class="form-check-input" 
                                {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">记住我</label>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary w-100">登录</button>
                        </div>

                        <div class="mt-3 text-center">
                            <a href="{{ route('password.request') }}" class="text-muted">
                                忘记密码？
                            </a>
                            <span class="mx-2 text-muted">|</span>
                            <a href="{{ route('register') }}" class="text-muted">
                                注册新账号
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 