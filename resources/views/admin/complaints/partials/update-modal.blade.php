<div class="modal fade" id="updateModal{{ $complaint->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.complaints.update', $complaint) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="modal-header">
                    <h5 class="modal-title">更新投诉状态</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">投诉内容</label>
                        <div class="form-control-plaintext">{{ $complaint->content }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">状态</label>
                        <select class="form-select" name="status" required>
                            <option value="pending" {{ $complaint->status === 'pending' ? 'selected' : '' }}>待处理</option>
                            <option value="processing" {{ $complaint->status === 'processing' ? 'selected' : '' }}>处理中</option>
                            <option value="resolved" {{ $complaint->status === 'resolved' ? 'selected' : '' }}>已解决</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">处理备注</label>
                        <textarea class="form-control" name="admin_remark" rows="3">{{ $complaint->admin_remark }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>
        </div>
    </div>
</div> 