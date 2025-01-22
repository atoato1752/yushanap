@extends('admin.layouts.app')

@section('title', '代理商管理')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">代理商管理</h5>
                <div>
                    <button type="button" class="btn btn-primary" onclick="showCreateModal()">
                        <i class="fas fa-plus"></i> 新增代理商
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- 筛选表单 -->
            <form action="{{ route('admin.agents.index') }}" method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" name="keyword" class="form-control" 
                            placeholder="代理商名称/手机号" value="{{ request('keyword') }}">
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
                        <select name="level" class="form-control">
                            <option value="">所有等级</option>
                            @foreach($levels as $value => $label)
                                <option value="{{ $value }}" 
                                    {{ request('level') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="date_range" class="form-control daterange" 
                            placeholder="注册时间范围" value="{{ request('date_range') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">筛选</button>
                    </div>
                </div>
            </form>

            <!-- 代理商列表 -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>代理商名称</th>
                            <th>手机号</th>
                            <th>等级</th>
                            <th>余额</th>
                            <th>查询量</th>
                            <th>状态</th>
                            <th>注册时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agents as $agent)
                        <tr>
                            <td>{{ $agent->id }}</td>
                            <td>{{ $agent->name }}</td>
                            <td>{{ $agent->phone }}</td>
                            <td>{{ $agent->level_text }}</td>
                            <td>￥{{ number_format($agent->balance, 2) }}</td>
                            <td>{{ $agent->query_count }}</td>
                            <td>
                                <span class="badge badge-{{ $agent->status_color }}">
                                    {{ $agent->status_text }}
                                </span>
                            </td>
                            <td>{{ $agent->created_at }}</td>
                            <td>
                                <a href="{{ route('admin.agents.show', $agent) }}" 
                                    class="btn btn-sm btn-info">详情</a>
                                <button type="button" class="btn btn-sm btn-primary"
                                    onclick="showEditModal({{ $agent->id }})">编辑</button>
                                @if($agent->status === 'pending')
                                    <button type="button" class="btn btn-sm btn-success"
                                        onclick="approveAgent({{ $agent->id }})">审核</button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">暂无数据</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- 分页 -->
            <div class="mt-4">
                {{ $agents->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>
</div>

<!-- 新增代理商弹窗 -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">新增代理商</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="createForm">
                    <div class="form-group">
                        <label>代理商名称</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group mt-3">
                        <label>手机号</label>
                        <input type="tel" name="phone" class="form-control" required>
                    </div>
                    <div class="form-group mt-3">
                        <label>代理商等级</label>
                        <select name="level" class="form-control" required>
                            @foreach($levels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mt-3">
                        <label>分成比例</label>
                        <div class="input-group">
                            <input type="number" name="commission_rate" class="form-control" 
                                min="0" max="100" step="0.1" required>
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="submitCreate()">确认</button>
            </div>
        </div>
    </div>
</div>

<!-- 编辑代理商弹窗 -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">编辑代理商</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" name="id">
                    <div class="form-group">
                        <label>代理商名称</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group mt-3">
                        <label>手机号</label>
                        <input type="tel" name="phone" class="form-control" required>
                    </div>
                    <div class="form-group mt-3">
                        <label>代理商等级</label>
                        <select name="level" class="form-control" required>
                            @foreach($levels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mt-3">
                        <label>分成比例</label>
                        <div class="input-group">
                            <input type="number" name="commission_rate" class="form-control" 
                                min="0" max="100" step="0.1" required>
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <label>状态</label>
                        <select name="status" class="form-control" required>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="submitEdit()">保存</button>
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

// 显示新增弹窗
function showCreateModal() {
    $('#createForm')[0].reset();
    $('#createModal').modal('show');
}

// 提交新增
function submitCreate() {
    let formData = new FormData($('#createForm')[0]);
    formData.append('_token', '{{ csrf_token() }}');

    $.post('{{ route('admin.agents.store') }}', 
        Object.fromEntries(formData), 
        function(response) {
            if (response.success) {
                $('#createModal').modal('hide');
                toastr.success('代理商添加成功');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(response.message || '操作失败');
            }
        }
    );
}

// 显示编辑弹窗
function showEditModal(id) {
    $.get(`/admin/agents/${id}/edit`, function(response) {
        if (response.success) {
            let agent = response.data;
            let form = $('#editForm');
            form.find('input[name="id"]').val(agent.id);
            form.find('input[name="name"]').val(agent.name);
            form.find('input[name="phone"]').val(agent.phone);
            form.find('select[name="level"]').val(agent.level);
            form.find('input[name="commission_rate"]').val(agent.commission_rate);
            form.find('select[name="status"]').val(agent.status);
            $('#editModal').modal('show');
        } else {
            toastr.error(response.message || '获取数据失败');
        }
    });
}

// 提交编辑
function submitEdit() {
    let formData = new FormData($('#editForm')[0]);
    formData.append('_token', '{{ csrf_token() }}');
    let id = formData.get('id');

    $.post(`/admin/agents/${id}`, 
        Object.fromEntries(formData), 
        function(response) {
            if (response.success) {
                $('#editModal').modal('hide');
                toastr.success('代理商信息已更新');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(response.message || '操作失败');
            }
        }
    );
}

// 审核代理商
function approveAgent(id) {
    if (confirm('确定要通过该代理商的审核吗？')) {
        $.post(`/admin/agents/${id}/approve`, {
            _token: '{{ csrf_token() }}'
        }, function(response) {
            if (response.success) {
                toastr.success('审核已通过');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(response.message || '操作失败');
            }
        });
    }
}
</script>
@endpush 