<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Models\Attachment;
use App\Models\Log;
use App\Models\Project;
use App\Services\FileCryptoService;
class ProjectController extends BaseController
{
    /**
     * Hiển thị danh sách dự án mà người dùng có quyền truy cập.
     */
    public function index(): void
    {
        $this->requireRole(['admin', 'manager']);
        $du_an = (new Project())->forUser(Auth::user());
        $this->render('du_an/index', ['du_an' => $du_an], 'Quản lý dự án');
    }

    /**
     * Mở form tạo dự án mới.
     */
    public function create(): void
    {
        $this->requireRole(['manager', 'admin']);
        $this->render('du_an/form', ['project' => null], 'Tạo dự án');
    }

    /**
     * Lưu dự án mới và tự động thêm người tạo làm trưởng nhóm của dự án.
     */
    public function store(): void
    {
        $this->requireRole(['manager', 'admin']);
        if (!\is_post()) {
            \redirect('du_an/index');
        }

        $user = Auth::user();
        $model = new Project();

        $projectId = $model->create([
            'code' => trim($_POST['code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null,
            'priority' => $_POST['priority'] ?? 'medium',
            'status' => $_POST['status'] ?? 'active',
            'created_by' => $user['id'],
        ]);

        $model->addMember($projectId, (int) $user['id'], 'lead');
        (new Log())->create($user['id'], 'create', 'project', $projectId, 'Tạo dự án mới');
        
        // Xử lý upload file từ manager (nếu có)
        if (!empty($_FILES['project_attachment']['name'])) {
            $this->handleProjectFileUpload($projectId);
        }
        
        \flash('success', 'Tạo dự án thành công.');
        \redirect('du_an/index');
    }

    /**
     * Mở form cập nhật dự án.
     */
    public function edit(int $id): void
    {
        $this->requireRole(['manager', 'admin']);
        $project = (new Project())->find($id);
        $this->render('du_an/form', ['project' => $project], 'Cập nhật dự án');
    }

    /**
     * Lưu thông tin chỉnh sửa dự án.
     */
    public function update(int $id): void
    {
        $this->requireRole(['manager', 'admin']);
        if (!\is_post()) {
            \redirect('du_an/index');
        }

        $ok = (new Project())->update($id, [
            'code' => trim($_POST['code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null,
            'priority' => $_POST['priority'] ?? 'medium',
            'status' => $_POST['status'] ?? 'active',
        ]);

        if ($ok) {
            (new Log())->create(Auth::user()['id'], 'update', 'project', $id, 'Cập nhật dự án');
            \flash('success', 'Cập nhật dự án thành công.');
        } else {
            \flash('danger', 'Cập nhật dự án thất bại.');
        }

        \redirect('du_an/index');
    }

    /**
     * Hiển thị danh sách thành viên và form thêm thành viên của một dự án.
     */
    public function members(int $id): void
    {
        $this->requireRole(['manager', 'admin']);
        $model = new Project();

        $project = $model->find($id);
        $members = $model->members($id);
        $availableUsers = $model->availableEmployees($id);

        $this->render('du_an/members', compact('project', 'members', 'availableUsers'), 'Thành viên dự án');
    }

    /**
     * Thêm thành viên vào dự án.
     */
    public function addMember(int $projectId): void
    {
        $this->requireRole(['manager', 'admin']);
        if (!\is_post()) {
            \redirect('du_an/members', ['id' => $projectId]);
        }

        $userId = (int) ($_POST['user_id'] ?? 0);
        $projectRole = $_POST['project_role'] ?? 'member';
        $ok = (new Project())->addMember($projectId, $userId, $projectRole);

        if ($ok) {
            (new Log())->create(Auth::user()['id'], 'create', 'project_member', $projectId, 'Thêm thành viên vào dự án');
            \flash('success', 'Đã thêm thành viên vào dự án.');
        } else {
            \flash('danger', 'Không thể thêm thành viên.');
        }

        \redirect('du_an/members', ['id' => $projectId]);
    }

    /**
     * Xóa thành viên khỏi dự án.
     */
    public function removeMember(int $projectId): void
    {
        $this->requireRole(['manager', 'admin']);
        $userId = (int) ($_GET['user_id'] ?? 0);

        $ok = (new Project())->removeMember($projectId, $userId);
        if ($ok) {
            (new Log())->create(Auth::user()['id'], 'delete', 'project_member', $projectId, 'Xóa thành viên khỏi dự án');
            \flash('success', 'Đã xóa thành viên khỏi dự án.');
        } else {
            \flash('danger', 'Không thể xóa thành viên.');
        }

        \redirect('du_an/members', ['id' => $projectId]);
    }

    /**
     * Xử lý upload file dự án từ manager và mã hóa trước lưu.
     */
    private function handleProjectFileUpload(int $projectId): void
    {
        $user = Auth::user();
        $file = $_FILES['project_attachment'] ?? null;

        if (!$file || empty($file['name'])) {
            return;
        }

        try {
            // Kiểm tra kích thước file (20MB)
            if ((int) $file['size'] > MAX_UPLOAD_BYTES) {
                \flash('warning', 'File dự án vượt quá 20MB, bỏ qua upload.');
                return;
            }

            // Kiểm tra định dạng file
            $allowedExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'txt'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExt, true)) {
                \flash('warning', 'Định dạng file dự án không được hỗ trợ, bỏ qua upload.');
                return;
            }

            // Mã hóa file
            $stored = (new FileCryptoService())->encryptAndStore($file['tmp_name'], $file['name']);

            // Lưu metadata với file_type='project_manager'
            $attachmentModel = new \App\Models\Attachment();
            $reason = trim($_POST['upload_reason'] ?? '');

            $ok = $attachmentModel->create([
                'task_id' => null, // Không liên quan task, chỉ project
                'original_name' => $file['name'],
                'stored_name' => $stored['stored_name'],
                'mime_type' => $file['type'] ?: 'application/octet-stream',
                'file_size' => (int) $file['size'],
                'encrypted_path' => $stored['path'],
                'uploaded_by' => $user['id'],
                'file_type' => 'project_manager',
                'upload_reason' => $reason ?: null,
            ]);

            if ($ok) {
                (new Log())->create($user['id'], 'upload', 'project_attachment', $projectId, "Upload file dự án: $reason");
                \flash('info', 'File tài liệu dự án đã upload và mã hóa.');
            } else {
                \flash('warning', 'Không thể lưu metadata file dự án.');
            }
        } catch (\Throwable $e) {
            \flash('warning', 'Upload file dự án thất bại: ' . $e->getMessage());
        }
    }
}
