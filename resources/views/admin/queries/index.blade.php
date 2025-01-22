@extends('admin.layouts.app')

@section('title', '查询管理')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">查询管理</h5>
                <div>
                    <a href="{{ route('admin.queries.export') }}" class="btn btn-success">
                        <i class="fas fa-download"></i> 导出数据
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- 筛选表单 -->
            <form action="{{ route('admin.queries.index') }}" method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-2">
                        <input type="text" name="keyword" class="form-control" 
                            placeholder="姓名/身份证号" value="{{ request('keyword') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="">所有状态</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" 
                                    {{ request('status') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="payment_status" class="form-control">
                            <option value="">支付状态</option>
                            @foreach($paymentStatuses as $value => $label)
                                <option value="{{ $value }}" 
                                    {{ request('payment_status') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="date_range" class="form-control daterange" 
                            placeholder="查询时间范围" value="{{ request('date_range') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="agent_id" class="form-control">
                            <option value="">所有代理商</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" 
                                    {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-block">筛选</button>
                    </div>
                </div>
            </form>

            <!-- 查询列表 -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>姓名</th>
                            <th>身份证号</th>
                            <th>代理商</th>
                            <th>查询状态</th>
                            <th>支付状态</th>
                            <th>查询时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($queries as $query)
                        <tr>
                            <td>{{ $query->id }}</td>
                            <td>{{ $query->name }}</td>
                            <td>{{ substr_replace($query->id_card, '********', 6, 8) }}</td>
                            <td>{{ $query->agent->name ?? '-' }}</td>
                            <td>
                                <span class="badge badge-{{ $query->status_color }}">
                                    {{ $query->status_text }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $query->payment_status_color }}">
                                    {{ $query->payment_status_text }}
                                </span>
                            </td>
                            <td>{{ $query->created_at }}</td>
                            <td>
                                <a href="{{ route('admin.queries.show', $query) }}" 
                                    class="btn btn-sm btn-info">详情</a>
                                @if($query->status === 'completed')
                                    <a href="{{ route('admin.queries.download', $query) }}" 
                                        class="btn btn-sm btn-success">下载</a>
                                @endif
                                @if($query->status === 'processing')
                                    <button type="button" class="btn btn-sm btn-warning" 
                                        onclick="retryQuery({{ $query->id }})">重试</button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">暂无数据</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- 分页 -->
            <div class="mt-4">
                {{ $queries->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // 日期范围选择器
    $('.daterange').daterangepicker({
        locale: {
            format: 'YYYY-MM-DD',
            applyLabel: '确定',
            cancelLabel: '取消',
            fromLabel: '从',
            toLabel: '至'
        }
    });
});

// 重试查询
function retryQuery(id) {
    if (confirm('确定要重试该查询吗？')) {
        $.post(`/admin/queries/${id}/retry`, {
            _token: '{{ csrf_token() }}'
        }, function(response) {
            if (response.success) {
                toastr.success('已重新发起查询');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(response.message || '操作失败');
            }
        });
    }
}
</script>
@endpush 