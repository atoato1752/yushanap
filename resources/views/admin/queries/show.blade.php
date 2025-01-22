@extends('admin.layouts.app')

@section('title', '查询详情')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <!-- 基本信息 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">基本信息</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>查询编号：</strong>{{ $query->id }}</p>
                            <p><strong>姓名：</strong>{{ $query->name }}</p>
                            <p><strong>身份证号：</strong>{{ $query->id_card }}</p>
                            <p><strong>查询时间：</strong>{{ $query->created_at }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>代理商：</strong>{{ $query->agent->name ?? '-' }}</p>
                            <p><strong>查询状态：</strong>
                                <span class="badge badge-{{ $query->status_color }}">
                                    {{ $query->status_text }}
                                </span>
                            </p>
                            <p><strong>支付状态：</strong>
                                <span class="badge badge-{{ $query->payment_status_color }}">
                                    {{ $query->payment_status_text }}
                                </span>
                            </p>
                            <p><strong>支付金额：</strong>￥{{ number_format($query->amount, 2) }}</p>
                        </div>
                    </div>

                    <!-- 操作按钮 -->
                    <div class="mt-4">
                        <a href="{{ route('admin.queries.index') }}" class="btn btn-secondary">
                            返回列表
                        </a>
                        @if($query->status === 'completed')
                            <a href="{{ route('admin.queries.download', $query) }}" 
                                class="btn btn-success">下载报告</a>
                        @endif
                        @if($query->status === 'processing')
                            <button type="button" class="btn btn-warning" 
                                onclick="retryQuery({{ $query->id }})">重试查询</button>
                        @endif
                        @if($query->can_refund)
                            <button type="button" class="btn btn-danger" 
                                onclick="showRefundModal()">退款</button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 查询结果 -->
            @if($query->status === 'completed')
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">查询结果</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>信用评分</h6>
                            <div class="d-flex align-items-center">
                                <div class="h2 mb-0 me-3">{{ $query->result->credit_score }}</div>
                                <span class="badge badge-{{ $query->result->score_level_color }}">
                                    {{ $query->result->score_level_text }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>风险等级</h6>
                            <div class="d-flex align-items-center">
                                <div class="h2 mb-0 me-3">{{ $query->result->risk_level }}</div>
                                <span class="badge badge-{{ $query->result->risk_level_color }}">
                                    {{ $query->result->risk_level_text }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 详细数据 -->
                    <div class="mt-4">
                        <h6>贷款记录</h6>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>机构</th>
                                        <th>金额</th>
                                        <th>日期</th>
                                        <th>状态</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($query->result->loans as $loan)
                                    <tr>
                                        <td>{{ $loan['institution'] }}</td>
                                        <td>￥{{ number_format($loan['amount'], 2) }}</td>
                                        <td>{{ $loan['date'] }}</td>
                                        <td>
                                            <span class="badge badge-{{ $loan['status_color'] }}">
                                                {{ $loan['status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- 处理记录 -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">处理记录</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($query->process_records as $record)
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">{{ $record['status'] }}</h6>
                                <small class="text-muted">{{ $record['time'] }}</small>
                                @if(!empty($record['remark']))
                                    <p class="mb-0 mt-1">{{ $record['remark'] }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- 支付信息 -->
            @if($query->payment)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">支付信息</h5>
                </div>
                <div class="card-body">
                    <p><strong>支付单号：</strong>{{ $query->payment->payment_no }}</p>
                    <p><strong>支付方式：</strong>{{ $query->payment->payment_type_text }}</p>
                    <p><strong>支付时间：</strong>{{ $query->payment->paid_at ?: '-' }}</p>
                    <p><strong>支付金额：</strong>￥{{ number_format($query->payment->amount, 2) }}</p>
                    @if($query->payment->refund)
                        <div class="alert alert-warning mt-3 mb-0">
                            <h6 class="alert-heading">退款信息</h6>
                            <p class="mb-1">退款金额：￥{{ number_format($query->payment->refund->amount, 2) }}</p>
                            <p class="mb-1">退款原因：{{ $query->payment->refund->reason_text }}</p>
                            <p class="mb-0">退款时间：{{ $query->payment->refund->refunded_at ?: '-' }}</p>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- 投诉记录 -->
            @if($query->complaint)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">投诉记录</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>投诉类型：{{ $query->complaint->type_text }}</span>
                        <span class="badge badge-{{ $query->complaint->status_color }}">
                            {{ $query->complaint->status_text }}
                        </span>
                    </div>
                    <p class="mb-3">投诉内容：{{ $query->complaint->content }}</p>
                    @if($query->complaint->images)
                        <div class="mb-3">
                            <p class="mb-2">相关图片：</p>
                            <div class="row">
                                @foreach($query->complaint->images as $image)
                                <div class="col-4">
                                    <a href="{{ Storage::url($image) }}" target="_blank">
                                        <img src="{{ Storage::url($image) }}" class="img-thumbnail">
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if($query->complaint->reply)
                        <div class="alert alert-info mb-0">
                            <h6 class="alert-heading">处理回复</h6>
                            <p class="mb-0">{{ $query->complaint->reply }}</p>
                        </div>
                    @else
                        <button type="button" class="btn btn-primary btn-sm" 
                            onclick="showReplyModal()">回复投诉</button>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- 退款弹窗 -->
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">退款处理</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="refundForm">
                    <div class="form-group">
                        <label>退款金额</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">￥</span>
                            </div>
                            <input type="number" name="amount" class="form-control" 
                                max="{{ $query->payment->amount }}" 
                                value="{{ $query->payment->amount }}" step="0.01" required>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <label>退款原因</label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="submitRefund()">确认退款</button>
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

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}
.timeline-marker {
    position: absolute;
    left: -30px;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background: #e9ecef;
    border: 3px solid #fff;
    box-shadow: 0 0 0 1px #dee2e6;
}
.timeline-content {
    position: relative;
    padding-left: 1rem;
}
.timeline-item:not(:last-child):before {
    content: '';
    position: absolute;
    left: -23px;
    top: 15px;
    height: calc(100% - 15px);
    width: 2px;
    background: #dee2e6;
}
</style>
@endpush

@push('scripts')
<script>
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

// 显示退款弹窗
function showRefundModal() {
    $('#refundModal').modal('show');
}

// 提交退款
function submitRefund() {
    let formData = new FormData($('#refundForm')[0]);
    formData.append('_token', '{{ csrf_token() }}');

    $.post('{{ route('admin.queries.refund', $query) }}', 
        Object.fromEntries(formData), 
        function(response) {
            if (response.success) {
                $('#refundModal').modal('hide');
                toastr.success('退款申请已提交');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(response.message || '操作失败');
            }
        }
    );
}

// 显示回复弹窗
function showReplyModal() {
    $('#replyModal').modal('show');
}

// 提交回复
function submitReply() {
    let formData = new FormData($('#replyForm')[0]);
    formData.append('_token', '{{ csrf_token() }}');

    $.post('{{ route('admin.complaints.reply', $query->complaint) }}', 
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