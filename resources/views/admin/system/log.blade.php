@extends('admin.layouts.app')

@section('title', '系统日志')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">系统日志</h5>
                <div>
                    <button type="button" class="btn btn-danger" onclick="clearLogs()">
                        <i class="fas fa-trash"></i> 清空日志
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- 筛选表单 -->
            <form action="{{ route('admin.system.log') }}" method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" name="keyword" class="form-control" 
                            placeholder="搜索关键词" value="{{ request('keyword') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="level" class="form-control">
                            <option value="">日志等级</option>
                            @foreach($levels as $value => $label)
                                <option value="{{ $value }}" 
                                    {{ request('level') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-control">
                            <option value="">日志类型</option>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" 
                                    {{ request('type') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="date_range" class="form-control daterange" 
                            placeholder="时间范围" value="{{ request('date_range') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">筛选</button>
                    </div>
                </div>
            </form>

            <!-- 日志列表 -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>等级</th>
                            <th>类型</th>
                            <th>内容</th>
                            <th>IP地址</th>
                            <th>操作人</th>
                            <th>时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>
                                <span class="badge badge-{{ $log->level_color }}">
                                    {{ $log->level_text }}
                                </span>
                            </td>
                            <td>{{ $log->type_text }}</td>
                            <td>{{ Str::limit($log->content, 50) }}</td>
                            <td>{{ $log->ip }}</td>
                            <td>{{ $log->operator->name ?? '-' }}</td>
                            <td>{{ $log->created_at }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" 
                                    onclick="showDetail({{ $log->id }})">详情</button>
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
                {{ $logs->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>
</div>

<!-- 日志详情弹窗 -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">日志详情</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>日志ID：</strong><span id="detail-id"></span></p>
                        <p><strong>日志等级：</strong><span id="detail-level"></span></p>
                        <p><strong>日志类型：</strong><span id="detail-type"></span></p>
                        <p><strong>IP地址：</strong><span id="detail-ip"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>操作人：</strong><span id="detail-operator"></span></p>
                        <p><strong>操作时间：</strong><span id="detail-time"></span></p>
                        <p><strong>User Agent：</strong><span id="detail-ua"></span></p>
                    </div>
                </div>
                <div class="mt-4">
                    <h6>日志内容：</h6>
                    <pre class="bg-light p-3 mb-0" id="detail-content"></pre>
                </div>
                @if(config('app.debug'))
                <div class="mt-4">
                    <h6>调试信息：</h6>
                    <pre class="bg-light p-3 mb-0" id="detail-debug"></pre>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
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

// 显示日志详情
function showDetail(id) {
    $.get(`/admin/system/log/${id}`, function(response) {
        if (response.success) {
            let log = response.data;
            $('#detail-id').text(log.id);
            $('#detail-level').html(`
                <span class="badge badge-${log.level_color}">${log.level_text}</span>
            `);
            $('#detail-type').text(log.type_text);
            $('#detail-ip').text(log.ip);
            $('#detail-operator').text(log.operator ? log.operator.name : '-');
            $('#detail-time').text(log.created_at);
            $('#detail-ua').text(log.user_agent);
            $('#detail-content').text(log.content);
            if (log.debug) {
                $('#detail-debug').text(JSON.stringify(log.debug, null, 2));
            }
            $('#detailModal').modal('show');
        } else {
            toastr.error(response.message || '获取数据失败');
        }
    });
}

// 清空日志
function clearLogs() {
    if (confirm('确定要清空所有日志吗？此操作不可恢复！')) {
        $.post('{{ route('admin.system.log.clear') }}', {
            _token: '{{ csrf_token() }}'
        }, function(response) {
            if (response.success) {
                toastr.success('日志已清空');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(response.message || '操作失败');
            }
        });
    }
}
</script>
@endpush 