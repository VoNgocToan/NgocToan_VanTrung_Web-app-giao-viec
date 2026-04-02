<?php $isEdit = !empty($project); ?>
<div class="section-header">
    <div>
        <div class="section-title"><?= $isEdit ? 'Cập nhật dự án' : 'Tạo dự án mới' ?></div>
        <p class="section-desc">Nhập thông tin quản lý dự án để trưởng nhóm theo dõi thành viên và phân công công việc.</p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(route_url('du_an/index')) ?>">Quay lại</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="post" enctype="multipart/form-data" action="<?= e($isEdit ? route_url('du_an/update', ['id' => $project['id']]) : route_url('du_an/store')) ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Mã dự án</label>
                    <input class="form-control" name="code" required value="<?= e($project['code'] ?? '') ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Tên dự án</label>
                    <input class="form-control" name="name" required value="<?= e($project['name'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Mô tả</label>
                    <textarea class="form-control" rows="4" name="description"><?= e($project['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Ngày bắt đầu</label>
                    <input type="date" class="form-control" name="start_date" value="<?= e($project['start_date'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Ngày kết thúc</label>
                    <input type="date" class="form-control" name="end_date" value="<?= e($project['end_date'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Ưu tiên</label>
                    <select class="form-select" name="priority">
                        <option value="low" <?= (($project['priority'] ?? 'medium') === 'low') ? 'selected' : '' ?>>Thấp</option>
                        <option value="medium" <?= (($project['priority'] ?? 'medium') === 'medium') ? 'selected' : '' ?>>Trung bình</option>
                        <option value="high" <?= (($project['priority'] ?? 'medium') === 'high') ? 'selected' : '' ?>>Cao</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select class="form-select" name="status">
                        <option value="planning" <?= (($project['status'] ?? 'active') === 'planning') ? 'selected' : '' ?>>Lên kế hoạch</option>
                        <option value="active" <?= (($project['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Đang hoạt động</option>
                        <option value="completed" <?= (($project['status'] ?? 'active') === 'completed') ? 'selected' : '' ?>>Hoàn thành</option>
                    </select>
                </div>

                <!-- Upload file tài liệu dự án (Manager) -->
                <div class="col-12">
                    <hr>
                    <h5 class="mb-3">📤 Upload file tài liệu dự án (Tuỳ chọn)</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Chọn file</label>
                            <input type="file" class="form-control" id="project_attachment" name="project_attachment">
                            <div id="project_error" class="alert alert-danger alert-sm mt-2" style="display: none;"></div>
                            <div class="small text-secondary mt-2">
                                File sẽ được mã hóa. Hỗ trợ: pdf, doc, docx, xls, xlsx, png, jpg, jpeg, txt. Max 20MB.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lý do/Mô tả file</label>
                            <input type="text" class="form-control" name="upload_reason" placeholder="VD: Tài liệu yêu cầu, kế hoạch dự án, tài liệu tham khảo, ...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary" id="project_submit_btn"><?= $isEdit ? 'Lưu cập nhật' : 'Tạo dự án' ?></button>
                <a class="btn btn-outline-secondary" href="<?= e(route_url('du_an/index')) ?>">Hủy</a>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const allowedExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'txt'];
    const maxBytes = 20 * 1024 * 1024; // 20MB
    const fileInput = document.getElementById('project_attachment');
    const errorDiv = document.getElementById('project_error');
    const submitBtn = document.getElementById('project_submit_btn');

    if (!fileInput) return;

    fileInput.addEventListener('change', function() {
        errorDiv.style.display = 'none';
        errorDiv.textContent = '';

        if (!this.files || this.files.length === 0) {
            return; // Không chọn file là được phép
        }

        const file = this.files[0];
        const errors = [];

        // Kiểm tra kích thước file
        if (file.size > maxBytes) {
            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            errors.push(`❌ File vượt quá dung lượng cho phép (${sizeMB}MB > 20MB)`);
        }

        // Kiểm tra định dạng file
        const ext = file.name.split('.').pop().toLowerCase();
        if (!allowedExt.includes(ext)) {
            errors.push(`❌ Định dạng file không được hỗ trợ (.${ext}). Chỉ hỗ trợ: pdf, doc, docx, xls, xlsx, png, jpg, jpeg, txt`);
        }

        if (errors.length > 0) {
            errorDiv.innerHTML = errors.join('<br>');
            errorDiv.style.display = 'block';
            submitBtn.disabled = true;
        } else {
            submitBtn.disabled = false;
        }
    });
})();
</script>
