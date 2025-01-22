<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>管理员登录 - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            width: 100%;
            max-width: 400px;
            padding: 15px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,.1);
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="text-center mb-4">
            <h4>{{ config('app.name') }}</h4>
            <p class="text-muted">管理员登录</p>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('admin.login') }}" method="POST">
                    @csrf

                    @if($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="form-group">
                        <label>用户名</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" name="username" class="form-control" 
                                value="{{ old('username') }}" required autofocus>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label>密码</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-check mt-3">
                        <input type="checkbox" name="remember" class="form-check-input" 
                            id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            记住我
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-4">
                        登录
                    </button>
                </form>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ url('/') }}" class="text-muted">
                <i class="fas fa-arrow-left me-2"></i>返回首页
            </a>
        </div>
    </div>
</body>
</html> 