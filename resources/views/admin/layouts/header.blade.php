<header class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
            {{ config('app.name') }} 管理后台
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- 左侧菜单 -->
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                       href="{{ route('admin.dashboard') }}">控制台</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">查询管理</a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('admin.queries.index') }}">查询列表</a>
                        <a class="dropdown-item" href="{{ route('admin.queries.statistics') }}">查询统计</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">代理商管理</a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('admin.agents.index') }}">代理商列表</a>
                        <a class="dropdown-item" href="{{ route('admin.agents.earnings') }}">收益管理</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">系统管理</a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('admin.system.config') }}">系统配置</a>
                        <a class="dropdown-item" href="{{ route('admin.system.logs') }}">系统日志</a>
                    </div>
                </li>
            </ul>

            <!-- 右侧菜单 -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                        {{ Auth::guard('admin')->user()->name }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="{{ route('admin.profile') }}">个人资料</a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item">退出登录</button>
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>

<nav class="navbar navbar-expand navbar-light bg-white">
    <div class="container-fluid">
        <!-- 切换侧边栏按钮 -->
        <button class="btn" type="button" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <!-- 右侧菜单 -->
        <ul class="navbar-nav ms-auto">
            <!-- 通知 -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    @if($unread_notifications_count > 0)
                        <span class="badge bg-danger rounded-pill">
                            {{ $unread_notifications_count }}
                        </span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <div class="dropdown-header">通知</div>
                    @forelse($notifications as $notification)
                        <a class="dropdown-item" href="{{ $notification->data['url'] ?? '#' }}">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    {{ $notification->data['message'] }}
                                    <div class="text-muted small">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </div>
                                </div>
                                @unless($notification->read_at)
                                    <div class="flex-shrink-0">
                                        <span class="badge bg-primary">新</span>
                                    </div>
                                @endunless
                            </div>
                        </a>
                    @empty
                        <div class="dropdown-item text-center text-muted">
                            暂无通知
                        </div>
                    @endforelse
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-center" href="{{ route('admin.notifications.index') }}">
                        查看全部
                    </a>
                </div>
            </li>

            <!-- 用户菜单 -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle"></i>
                    {{ Auth::guard('admin')->user()->name }}
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="{{ route('admin.profile.edit') }}">
                        <i class="fas fa-user fa-fw me-2"></i>个人资料
                    </a>
                    <a class="dropdown-item" href="{{ route('admin.profile.password') }}">
                        <i class="fas fa-key fa-fw me-2"></i>修改密码
                    </a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('admin.logout') }}" method="POST" class="d-none" id="logoutForm">
                        @csrf
                    </form>
                    <a class="dropdown-item" href="#" onclick="event.preventDefault();document.getElementById('logoutForm').submit();">
                        <i class="fas fa-sign-out-alt fa-fw me-2"></i>退出登录
                    </a>
                </div>
            </li>
        </ul>
    </div>
</nav>

@push('scripts')
<script>
// 切换侧边栏
$('#sidebarToggle').click(function(e) {
    e.preventDefault();
    $('body').toggleClass('sb-sidenav-toggled');
});

// 标记通知为已读
$('.dropdown-item').click(function() {
    let notification = $(this);
    if(notification.find('.badge').length) {
        $.post('{{ route('admin.notifications.read') }}', {
            _token: '{{ csrf_token() }}',
            id: notification.data('id')
        });
    }
});
</script>
@endpush 