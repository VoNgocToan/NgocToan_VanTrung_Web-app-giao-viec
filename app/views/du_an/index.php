<div class="section-header">
    <div>
        <div class="section-title">Quản lý dự án</div>
        <p class="section-desc">Menu phục vụ use case Tạo/Cập nhật dự án và Thêm/Xóa thành viên dự án.</p>
    </div>
    <a href="<?= e(route_url('du_an/create')) ?>" class="btn btn-primary">+ Tạo dự án</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Mã dự án</th>
                    <th>Tên dự án</th>
                    <th>Ưu tiên</th>
                    <th>Trạng thái</th>
                    <th>Thời gian</th>
                    <th>Người tạo</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($du_an as $project): ?>
                <tr>
                    <td><?= e($project['code']) ?></td>
                    <td>
                        <div class="fw-semibold"><?= e($project['name']) ?></div>
                        <div class="small text-secondary"><?= e($project['description']) ?></div>
                    </td>
                    <td><?= e(priority_label($project['priority'])) ?></td>
                    <td><span class="badge text-bg-<?= e(status_badge_class($project['status'])) ?>"><?= e(status_label($project['status'])) ?></span></td>
                    <td><?= e(format_date($project['start_date'])) ?> → <?= e(format_date($project['end_date'])) ?></td>
                    <td><?= e($project['creator_name']) ?></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="<?= e(route_url('du_an/edit', ['id' => $project['id']])) ?>">Cập nhật</a>
                        <a class="btn btn-sm btn-outline-dark" href="<?= e(route_url('du_an/members', ['id' => $project['id']])) ?>">Thành viên</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$du_an): ?>
                <tr><td colspan="7" class="text-muted">Chưa có dự án nào.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
