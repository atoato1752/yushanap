@extends('admin.layouts.app')

@section('title', '通知列表')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">通知列表</h5>
                <div>
                    <button type="button" class="btn btn-primary" onclick="markAllAsRead()">
                        全部标记为已读
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="list-group">
                @forelse($notifications as $notification)
                    <div class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $notification->data['title'] }}</h6>
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1">{{ $notification->data['message'] }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ $notification->data['url'] ?? '#' }}" class="btn btn-sm btn-link px-0">
                                查看详情
                            </a>
                            @unless($notification->read_at)
                                <span class="badge bg-primary">未读</span>
                            @endunless
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-3">暂无通知</div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function markAllAsRead() {
    $.post('{{ route('admin.notifications.readAll') }}', {
        _token: '{{ csrf_token() }}'
    }, function(response) {
        if (response.success) {
            toastr.success('已全部标记为已读');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            toastr.error(response.message || '操作失败');
        }
    });
}
</script>
@endpush
@endsection 