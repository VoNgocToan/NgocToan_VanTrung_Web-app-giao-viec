<div class="section-header">
    <div>
        <div class="section-title">Tạo công việc mới</div>
        <p class="section-desc">Nhập đầy đủ thông tin để gắn công việc vào dự án, làm cơ sở cho bước phân công và tính KPI.</p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(route_url('cong_viec/index')) ?>">Quay lại</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="post" action="<?= e(route_url('cong_viec/store')) ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Dự án</label>
                    <select class="form-select" name="project_id" required>
                        <option value="">-- Chọn dự án --</option>
                        <?php foreach ($du_an as $project): ?>
                            <option value="<?= e((string) $project['id']) ?>"><?= e($project['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tiêu đề công việc</label>
                    <input class="form-control" name="title" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Mô tả công việc</label>
                    <textarea class="form-control" rows="4" name="description" placeholder="Mô tả phạm vi, đầu ra và ghi chú cần thiết"></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Mức ưu tiên</label>
                    <select class="form-select" name="priority">
                        <option value="low">Thấp</option>
                        <option value="medium" selected>Trung bình</option>
                        <option value="high">Cao</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Hạn chót</label>
                    <input type="date" class="form-control" name="deadline" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Điểm KPI kỳ vọng</label>
                    <input type="number" class="form-control" name="expected_score" value="10" min="0">
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Lưu công việc</button>
                <a class="btn btn-outline-secondary" href="<?= e(route_url('cong_viec/index')) ?>">Hủy</a>
            </div>
        </form>
    </div>
</div>
