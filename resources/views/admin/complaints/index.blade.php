@extends('admin.layouts.app')

@section('title', '投诉管理')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">投诉管理</h5>
        </div>
        <div class="card-body">
            <!-- 筛选表单 -->
            <form action="{{ route('admin.complaints.index') }}" method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" name="keyword" class="form-control" 
                            placeholder="查询对象/手机号" value="{{ request('keyword') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-control">
                            <option value="">投诉类型</option>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" 
                                    {{ request('type') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="">处理状态</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" 
                                    {{ request('status') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="date_range" class="form-control daterange" 
                            placeholder="投诉时间范围" value="{{ request('date_range') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">筛选</button>
                    </div>
                </div>
            </form>

            <!-- 投诉列表 -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>查询对象</th>
                            <th>投诉类型</th>
                            <th>投诉内容</th>
                            <th>联系电话</th>
                            <th>投诉时间</th>
                            <th>处理状态</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($complaints as $complaint)
                        <tr>
                            <td>{{ $complaint->id }}</td>
                            <td>{{ $complaint->query->name }}</td>
                            <td>{{ $complaint->type_text }}</td>
                            <td>{{ Str::limit($complaint->content, 30) }}</td>
                            <td>{{ $complaint->contact_phone }}</td>
                            <td>{{ $complaint->created_at }}</td>
                            <td>
                                <span class="badge badge-{{ $complaint->status_color }}">
                                    {{ $complaint->status_text }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.complaints.show', $complaint) }}" 
                                    class="btn btn-sm btn-info">详情</a>
                                @if($complaint->status === 'pending')
                                    <button type="button" class="btn btn-sm btn-primary"
                                        onclick="showReplyModal({{ $complaint->id }})">回复</button>
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
                {{ $complaints->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>
</div>

<!-- 回复弹窗 -->
<div class="modal fade" id="replyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">回复投诉</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="replyForm">
                    <input type="hidden" name="complaint_id">
                    <div class="form-group">
                        <label>回复内容</label>
                        <textarea name="reply" class="form-control" rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="submitReply()">提交回复</button>
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

// 显示回复弹窗
function showReplyModal(id) {
    $('#replyForm')[0].reset();
    $('#replyForm input[name="complaint_id"]').val(id);
    $('#replyModal').modal('show');
}

// 提交回复
function submitReply() {
    let formData = new FormData($('#replyForm')[0]);
    formData.append('_token', '{{ csrf_token() }}');
    let id = formData.get('complaint_id');

    $.post(`/admin/complaints/${id}/reply`, 
        Object.fromEntries(formData), 
        function(response) {
            if (response.success) {
                $('#replyModal').modal('hide');
                toastr.success('回复已提交');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(response.message || '操作失败');
            }
        }
    );
}
</script>
@endpush 