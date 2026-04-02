<div class="section-header">
    <div>
        <div class="section-title"><?= e($task['title']) ?></div>
        <p class="section-desc"><?= e($task['project_name']) ?> • Người tạo: <?= e($task['creator_name']) ?></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if (in_array(current_user()['role'], ['manager', 'admin'], true)): ?>
            <a class="btn btn-outline-primary" href="<?= e(route_url('cong_viec/assign', ['id' => $task['id']])) ?>">Phân công</a>
            <a class="btn btn-primary" href="<?= e(route_url('cong_viec/review', ['id' => $task['id']])) ?>">Duyệt & đánh giá</a>
        <?php endif; ?>
        <a class="btn btn-outline-secondary" href="<?= e(route_url('cong_viec/index')) ?>">Quay lại</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><strong>Dự án:</strong> <?= e($task['project_name']) ?></div>
                    <div class="col-md-6"><strong>Người phụ trách:</strong> <?= e($task['assignee_name'] ?? 'Chưa phân công') ?></div>
                    <div class="col-md-6"><strong>Mức ưu tiên:</strong> <?= e(priority_label($task['priority'])) ?></div>
                    <div class="col-md-6"><strong>Trạng thái:</strong> <span class="badge text-bg-<?= e(status_badge_class($task['status'])) ?>"><?= e(status_label($task['status'])) ?></span></div>
                    <div class="col-md-6"><strong>Hạn chót:</strong> <?= e(format_date($task['deadline'])) ?></div>
                    <div class="col-md-6"><strong>KPI kỳ vọng:</strong> <?= e((string) $task['expected_score']) ?></div>
                </div>
                <hr>
                <h2 class="h6 fw-bold">Mô tả công việc</h2>
                <p class="mb-0"><?= nl2br(e($task['description'])) ?></p>

                <?php if ($task['review_comment']): ?>
                    <hr>
                    <h2 class="h6 fw-bold">Nhận xét sau duyệt</h2>
                    <p class="mb-1"><?= nl2br(e($task['review_comment'])) ?></p>
                    <div class="small text-secondary">Điểm đánh giá: <?= e((string) $task['review_score']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (current_user()['role'] === 'employee' && (int) ($task['assignee_id'] ?? 0) === (int) current_user()['id']): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">Cập nhật tiến độ</h2>
                    <form method="post" action="<?= e(route_url('cong_viec/updateStatus', ['id' => $task['id']])) ?>">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Trạng thái mới</label>
                                <select class="form-select" name="status">
                                    <?php foreach (['in_progress' => 'Đang thực hiện', 'blocked' => 'Bị chặn', 'submitted' => 'Chờ duyệt'] as $status => $label): ?>
                                        <option value="<?= e($status) ?>"><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Ghi chú tiến độ</label>
                                <input class="form-control" name="note" placeholder="Ví dụ: đã hoàn thành 80% chức năng upload file">
                            </div>
                        </div>
                        <button class="btn btn-primary mt-3">Lưu trạng thái</button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">Upload file kết quả</h2>
                    <form method="post" enctype="multipart/form-data" action="<?= e(route_url('cong_viec/upload', ['id' => $task['id']])) ?>">
                        <input type="file" class="form-control" name="attachment" required>
                        <div class="small text-secondary mt-2">Hệ thống sẽ kiểm tra định dạng, mã hóa file rồi mới lưu trữ.</div>
                        <button class="btn btn-dark mt-3">Upload và mã hóa file</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header">📎 File đính kèm</div>
            <div class="card-body">
                <?php 
                $userRole = current_user()['role'];
                $isAssignee = (int) ($task['assignee_id'] ?? 0) === (int) current_user()['id'];
                $canAccessTask = (new \App\Models\Task())->canAccess(current_user(), $task['id']);
                ?>
                
                <?php foreach ($tep_dinh_kem as $file): ?>
                    <?php
                    // Quyền xem file manager: Manager/Admin
                    $canViewManagerFile = ($file['file_type'] === 'manager') && 
                        in_array($userRole, ['manager', 'admin'], true);
                    
                    // Quyền xem file project_manager: Tất cả thành viên có quyền truy cập task
                    $canViewProjectManagerFile = ($file['file_type'] === 'project_manager') && $canAccessTask;
                    
                    // Hiển thị file nếu: là file employee HOẶC file manager mà user có quyền HOẶC file project_manager mà user có quyền
                    if ($file['file_type'] === 'employee' || $canViewManagerFile || $canViewProjectManagerFile):
                    ?>
                        <div class="border rounded-4 p-3 mb-3">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">
                                        <?= e($file['original_name']) ?>
                                        <?php if ($file['file_type'] === 'manager'): ?>
                                            <span class="badge text-bg-info ms-2">Hướng dẫn từ Manager</span>
                                        <?php elseif ($file['file_type'] === 'project_manager'): ?>
                                            <span class="badge text-bg-warning ms-2">File từ Manager</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-success ms-2">Nộp từ Employee</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="small text-secondary mb-2 mt-1">
                                        Tải lên bởi <strong><?= e($file['uploader_name']) ?></strong> 
                                        • <?= e(format_datetime($file['created_at'])) ?>
                                    </div>
                                    
                                    <?php if (($file['file_type'] === 'manager' || $file['file_type'] === 'project_manager') && $file['upload_reason']): ?>
                                        <div class="small text-muted">
                                            📝 <em><?= e($file['upload_reason']) ?></em>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a class="btn btn-sm btn-outline-primary mt-2" href="<?= e(route_url('files/download', ['id' => $file['id']])) ?>">
                                ⬇️ Tải file
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <?php if (!$tep_dinh_kem): ?>
                    <div class="text-muted">Chưa có file đính kèm.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header">Lịch sử trạng thái</div>
            <div class="card-body">
                <?php foreach ($logs as $log): ?>
                    <div class="timeline-item">
                        <div class="fw-semibold"><?= e(status_label($log['old_status'])) ?> → <?= e(status_label($log['new_status'])) ?></div>
                        <div class="small text-secondary"><?= e($log['name']) ?> • <?= e(format_datetime($log['created_at'])) ?></div>
                        <?php if ($log['note']): ?><div class="small"><?= e($log['note']) ?></div><?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (!$logs): ?>
                    <div class="text-muted">Chưa có lịch sử thay đổi.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
