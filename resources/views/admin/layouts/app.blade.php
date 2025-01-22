<!DOCTYPE html>
<html>
<head>
    <title>@yield('title') - 后台管理</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
    @stack('styles')
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: .8rem 1rem;
        }
        .sidebar .nav-link:hover {
            color: #fff;
        }
        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,.1);
        }
        .main-content {
            padding: 20px;
        }
        .card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- 侧边栏 -->
            <div class="col-md-2 px-0 sidebar">
                <div class="p-3">
                    <h5 class="text-white">信用查询系统</h5>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                       href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-tachometer-alt me-2"></i>控制台
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.queries.*') ? 'active' : '' }}"
                       href="{{ route('admin.queries.index') }}">
                        <i class="fas fa-search me-2"></i>查询记录
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.agents.*') ? 'active' : '' }}"
                       href="{{ route('admin.agents.index') }}">
                        <i class="fas fa-users me-2"></i>代理商管理
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.complaints.*') ? 'active' : '' }}"
                       href="{{ route('admin.complaints.index') }}">
                        <i class="fas fa-exclamation-circle me-2"></i>投诉管理
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.system.*') ? 'active' : '' }}"
                       href="{{ route('admin.system.config') }}">
                        <i class="fas fa-cog me-2"></i>系统设置
                    </a>
                </nav>
            </div>

            <!-- 主内容区 -->
            <div class="col-md-10 main-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    @stack('scripts')
</body>
</html> 