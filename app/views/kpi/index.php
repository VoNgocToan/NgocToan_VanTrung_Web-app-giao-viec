<div class="section-header">
    <div>
        <div class="section-title">Báo cáo KPI</div>
        <p class="section-desc">Theo dõi hiệu suất theo số việc, tiến độ hoàn thành và điểm đánh giá.</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Nhân viên</th>
                    <th>Tổng việc</th>
                    <th>Đã duyệt</th>
                    <th>Đúng hạn</th>
                    <th>Trễ hạn</th>
                    <th>Bị trả lại</th>
                    <th>Điểm review</th>
                    <th>KPI</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= e($row['name']) ?></div>
                        <div class="small text-secondary"><?= e($row['email']) ?></div>
                    </td>
                    <td><?= e((string) $row['total_tasks']) ?></td>
                    <td><?= e((string) $row['approved_tasks']) ?></td>
                    <td><?= e((string) $row['on_time_tasks']) ?></td>
                    <td><?= e((string) $row['late_tasks']) ?></td>
                    <td><?= e((string) $row['redo_tasks']) ?></td>
                    <td><?= e((string) $row['review_points']) ?></td>
                    <td><span class="badge text-bg-primary p-2"><?= e((string) $row['kpi_score']) ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?>
                <tr><td colspan="8" class="text-muted">Chưa có dữ liệu KPI.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
