<div class="section-header">
    <div>
        <div class="section-title">Phân công công việc</div>
        <p class="section-desc"><?= e($task['title']) ?> • Dự án: <?= e($project['name']) ?></p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(route_url('cong_viec/show', ['id' => $task['id']])) ?>">Quay lại</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="post" action="<?= e(route_url('cong_viec/saveAssignment', ['id' => $task['id']])) ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Thành viên phụ trách</label>
                    <select class="form-select" name="assignee_id" required>
                        <option value="">-- Chọn nhân viên --</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?= e((string) $member['user_id']) ?>" <?= ((int) ($task['assignee_id'] ?? 0) === (int) $member['user_id']) ? 'selected' : '' ?>>
                                <?= e($member['name']) ?> - <?= e(role_label($member['role'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Ngày giao việc</label>
                    <input type="date" class="form-control" name="start_date" value="<?= e($task['start_date'] ?? date('Y-m-d')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Hạn chót thực hiện</label>
                    <input type="date" class="form-control" name="deadline" value="<?= e($task['deadline']) ?>">
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Xác nhận phân công</button>
                <a class="btn btn-outline-secondary" href="<?= e(route_url('cong_viec/show', ['id' => $task['id']])) ?>">Hủy</a>
            </div>
        </form>
    </div>
</div>
