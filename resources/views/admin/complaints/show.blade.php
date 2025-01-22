@extends('admin.layouts.app')

@section('title', '投诉详情')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <!-- 投诉信息 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">投诉信息</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>投诉编号：</strong>{{ $complaint->id }}</p>
                            <p><strong>投诉类型：</strong>{{ $complaint->type_text }}</p>
                            <p><strong>联系电话：</strong>{{ $complaint->contact_phone }}</p>
                            <p><strong>投诉时间：</strong>{{ $complaint->created_at }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>处理状态：</strong>
                                <span class="badge badge-{{ $complaint->status_color }}">
                                    {{ $complaint->status_text }}
                                </span>
                            </p>
                            <p><strong>处理时间：</strong>{{ $complaint->processed_at ?: '-' }}</p>
                            <p><strong>处理人员：</strong>{{ $complaint->processor->name ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6>投诉内容：</h6>
                        <p class="mb-0">{{ $complaint->content }}</p>
                    </div>

                    @if($complaint->images)
                    <div class="mt-4">
                        <h6>相关图片：</h6>
                        <div class="row">
                            @foreach($complaint->images as $image)
                            <div class="col-md-4">
                                <a href="{{ Storage::url($image) }}" target="_blank">
                                    <img src="{{ Storage::url($image) }}" class="img-fluid img-thumbnail">
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($complaint->reply)
                    <div class="mt-4">
                        <h6>处理回复：</h6>
                        <div class="alert alert-info mb-0">
                            {{ $complaint->reply }}
                        </div>
                    </div>
                    @endif

                    <!-- 操作按钮 -->
                    <div class="mt-4">
                        <a href="{{ route('admin.complaints.index') }}" class="btn btn-secondary">
                            返回列表
                        </a>
                        @if($complaint->status === 'pending')
                            <button type="button" class="btn btn-primary" 
                                onclick="showReplyModal()">回复投诉</button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 处理记录 -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">处理记录</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($complaint->process_records as $record)
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
            <!-- 查询信息 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">查询信息</h5>
                </div>
                <div class="card-body">
                    <p><strong>查询编号：</strong>{{ $complaint->query->id }}</p>
                    <p><strong>查询对象：</strong>{{ $complaint->query->name }}</p>
                    <p><strong>身份证号：</strong>{{ $complaint->query->id_card }}</p>
                    <p><strong>查询状态：</strong>
                        <span class="badge badge-{{ $complaint->query->status_color }}">
                            {{ $complaint->query->status_text }}
                        </span>
                    </p>
                    <p><strong>支付状态：</strong>
                        <span class="badge badge-{{ $complaint->query->payment_status_color }}">
                            {{ $complaint->query->payment_status_text }}
                        </span>
                    </p>
                    <p class="mb-0"><strong>查询时间：</strong>{{ $complaint->query->created_at }}</p>

                    <div class="mt-3">
                        <a href="{{ route('admin.queries.show', $complaint->query) }}" 
                            class="btn btn-info btn-sm btn-block">查看查询详情</a>
                    </div>
                </div>
            </div>

            <!-- 用户信息 -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">用户信息</h5>
                </div>
                <div class="card-body">
                    <p><strong>用户ID：</strong>{{ $complaint->user->id }}</p>
                    <p><strong>用户名：</strong>{{ $complaint->user->name }}</p>
                    <p><strong>手机号：</strong>{{ $complaint->user->phone }}</p>
                    <p><strong>注册时间：</strong>{{ $complaint->user->created_at }}</p>
                    <p class="mb-0"><strong>查询次数：</strong>{{ $complaint->user->query_count }}</p>
                </div>
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
// 显示回复弹窗
function showReplyModal() {
    $('#replyForm')[0].reset();
    $('#replyModal').modal('show');
}

// 提交回复
function submitReply() {
    let formData = new FormData($('#replyForm')[0]);
    formData.append('_token', '{{ csrf_token() }}');

    $.post('{{ route('admin.complaints.reply', $complaint) }}', 
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