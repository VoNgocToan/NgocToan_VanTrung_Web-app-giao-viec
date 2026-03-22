<div class="section-header">
    <div>
        <div class="section-title">Duyệt và đánh giá công việc</div>
        <p class="section-desc"><?= e($task['title']) ?> • Nhân viên phụ trách: <?= e($task['assignee_name'] ?? 'Chưa phân công') ?></p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(route_url('cong_viec/show', ['id' => $task['id']])) ?>">Quay lại</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <?php if ($tep_dinh_kem): ?>
            <div class="alert alert-light border">
                <div class="fw-semibold mb-2">File nộp kèm</div>
                <?php foreach ($tep_dinh_kem as $file): ?>
                    <div class="mt-2">
                        <?= e($file['original_name']) ?> -
                        <a href="<?= e(route_url('files/download', ['id' => $file['id']])) ?>">Tải về để kiểm tra</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= e(route_url('cong_viec/saveReview', ['id' => $task['id']])) ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Kết quả duyệt</label>
                    <select class="form-select" name="review_status">
                        <option value="approved">Duyệt đạt</option>
                        <option value="redo">Yêu cầu làm lại</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Điểm đánh giá</label>
                    <input type="number" class="form-control" name="review_score" value="<?= e((string) ($task['review_score'] ?? 0)) ?>" min="0" max="20">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Nhận xét</label>
                    <textarea class="form-control" name="review_comment" rows="4" placeholder="Nhập nhận xét, mức độ hoàn thành hoặc lý do yêu cầu chỉnh sửa"></textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Lưu kết quả duyệt</button>
                <a class="btn btn-outline-secondary" href="<?= e(route_url('cong_viec/show', ['id' => $task['id']])) ?>">Hủy</a>
            </div>
        </form>
    </div>
</div>
