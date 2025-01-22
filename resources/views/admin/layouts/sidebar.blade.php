<nav class="sidebar">
    <div class="sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                   href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-home me-2"></i>
                    控制台
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.queries.*') ? 'active' : '' }}" 
                   href="{{ route('admin.queries.index') }}">
                    <i class="fas fa-search me-2"></i>
                    查询记录
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.agents.*') ? 'active' : '' }}" 
                   href="{{ route('admin.agents.index') }}">
                    <i class="fas fa-users me-2"></i>
                    代理商管理
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.complaints.*') ? 'active' : '' }}" 
                   href="{{ route('admin.complaints.index') }}">
                    <i class="fas fa-comment-alt me-2"></i>
                    投诉管理
                    @if($pending_complaints_count > 0)
                        <span class="badge bg-danger rounded-pill ms-2">
                            {{ $pending_complaints_count }}
                        </span>
                    @endif
                </a>
            </li>

            @if(Auth::guard('admin')->user()->isAdmin())
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.system.*') ? 'active' : '' }}" 
                   href="{{ route('admin.system.config') }}">
                    <i class="fas fa-cog me-2"></i>
                    系统设置
                </a>
            </li>
            @endif
        </ul>
    </div>
</nav>

<style>
.sidebar .nav-link {
    color: #333;
    padding: .5rem 1rem;
}

.sidebar .nav-link:hover {
    color: #0d6efd;
    background-color: rgba(13, 110, 253, .1);
}

.sidebar .nav-link.active {
    color: #0d6efd;
    background-color: rgba(13, 110, 253, .1);
    font-weight: 500;
}

.sidebar .nav-link .badge {
    font-size: .75rem;
}
</style> 