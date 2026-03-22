<div class="section-header">
    <div>
        <div class="section-title">Quản lý công việc</div>
        <p class="section-desc">Module cốt lõi của đề tài: tạo việc, phân công, theo dõi tiến độ, upload file và duyệt đánh giá.</p>
    </div>
    <?php if (in_array(current_user()['role'], ['manager', 'admin'], true)): ?>
        <a href="<?= e(route_url('cong_viec/create')) ?>" class="btn btn-primary">+ Tạo công việc</a>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form class="row g-3">
            <input type="hidden" name="route" value="cong_viec/index">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Lọc theo dự án</label>
                <select class="form-select" name="project_id">
                    <option value="">Tất cả dự án</option>
                    <?php foreach ($du_an as $project): ?>
                        <option value="<?= e((string) $project['id']) ?>" <?= ((string) ($filters['project_id'] ?? '') === (string) $project['id']) ? 'selected' : '' ?>><?= e($project['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Lọc theo trạng thái</label>
                <select class="form-select" name="status">
                    <?php foreach (['' => 'Tất cả trạng thái', 'new' => 'Mới tạo', 'assigned' => 'Đã phân công', 'in_progress' => 'Đang thực hiện', 'blocked' => 'Bị chặn', 'submitted' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'redo' => 'Yêu cầu làm lại'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= (($filters['status'] ?? '') === $value) ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button class="btn btn-outline-primary">Áp dụng bộ lọc</button>
                <a class="btn btn-outline-secondary" href="<?= e(route_url('cong_viec/index')) ?>">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Công việc</th>
                    <th>Dự án</th>
                    <th>Người phụ trách</th>
                    <th>Trạng thái</th>
                    <th>Hạn chót</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($cong_viec as $task): ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= e($task['title']) ?></div>
                        <div class="small text-secondary"><?= e(priority_label($task['priority'])) ?> • KPI kỳ vọng <?= e((string) $task['expected_score']) ?></div>
                    </td>
                    <td><?= e($task['project_name']) ?></td>
                    <td><?= e($task['assignee_name'] ?? 'Chưa phân công') ?></td>
                    <td><span class="badge text-bg-<?= e(status_badge_class($task['status'])) ?>"><?= e(status_label($task['status'])) ?></span></td>
                    <td><?= e(format_date($task['deadline'])) ?></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-dark" href="<?= e(route_url('cong_viec/show', ['id' => $task['id']])) ?>">Chi tiết</a>
                        <?php if (in_array(current_user()['role'], ['manager', 'admin'], true)): ?>
                            <a class="btn btn-sm btn-outline-primary" href="<?= e(route_url('cong_viec/assign', ['id' => $task['id']])) ?>">Phân công</a>
                            <a class="btn btn-sm btn-outline-success" href="<?= e(route_url('cong_viec/review', ['id' => $task['id']])) ?>">Duyệt</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$cong_viec): ?>
                <tr><td colspan="6" class="text-muted">Không có dữ liệu công việc phù hợp.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
