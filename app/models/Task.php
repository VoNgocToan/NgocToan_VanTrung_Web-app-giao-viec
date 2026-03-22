<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Model xử lý nghiệp vụ công việc, phân công, duyệt và lịch sử trạng thái.
 */
class Task extends BaseModel
{
    /**
     * Lấy danh sách công việc theo quyền và bộ lọc.
     */
    public function forUser(array $user, array $filters = []): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            $conditions[] = 't.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['project_id'])) {
            $conditions[] = 't.project_id = :project_id';
            $params['project_id'] = (int) $filters['project_id'];
        }

        if ($user['role'] === 'employee') {
            $conditions[] = 't.assignee_id = :uid';
            $params['uid'] = (int) $user['id'];
        } elseif ($user['role'] === 'manager') {
            $conditions[] = '(t.created_by = :uid OR p.created_by = :uid OR EXISTS (
                SELECT 1 FROM thanh_vien_du_an pm WHERE pm.project_id = t.project_id AND pm.user_id = :uid
            ))';
            $params['uid'] = (int) $user['id'];
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $sql = "SELECT t.*, p.name AS project_name, p.code AS project_code,
                       assignee.name AS assignee_name, creator.name AS creator_name
                FROM cong_viec t
                INNER JOIN du_an p ON p.id = t.project_id
                LEFT JOIN tai_khoan assignee ON assignee.id = t.assignee_id
                LEFT JOIN tai_khoan creator ON creator.id = t.created_by
                {$where}
                ORDER BY t.deadline ASC, t.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Tìm một công việc theo ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, p.name AS project_name, p.code AS project_code,
                    assignee.name AS assignee_name, creator.name AS creator_name
             FROM cong_viec t
             INNER JOIN du_an p ON p.id = t.project_id
             LEFT JOIN tai_khoan assignee ON assignee.id = t.assignee_id
             LEFT JOIN tai_khoan creator ON creator.id = t.created_by
             WHERE t.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Tạo công việc mới.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO cong_viec(project_id, title, description, priority, status, deadline, start_date, assignee_id, created_by, expected_score, review_score, review_comment, approved_at, created_at, updated_at)
             VALUES(:project_id, :title, :description, :priority, 'new', :deadline, NULL, NULL, :created_by, :expected_score, NULL, NULL, NULL, NOW(), NOW())"
        );
        $stmt->execute([
            'project_id' => $data['project_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'priority' => $data['priority'],
            'deadline' => $data['deadline'],
            'created_by' => $data['created_by'],
            'expected_score' => $data['expected_score'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Gán người phụ trách cho công việc.
     */
    public function assign(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE cong_viec
             SET assignee_id = :assignee_id,
                 start_date = :start_date,
                 deadline = :deadline,
                 status = 'assigned',
                 updated_at = NOW()
             WHERE id = :id"
        );
        return $stmt->execute([
            'id' => $id,
            'assignee_id' => $data['assignee_id'],
            'start_date' => $data['start_date'],
            'deadline' => $data['deadline'],
        ]);
    }

    /**
     * Cập nhật trạng thái công việc và ghi lại lịch sử thay đổi.
     */
    public function updateStatus(int $id, string $status, ?string $note, int $userId): bool
    {
        $old = $this->find($id);
        if (!$old) {
            return false;
        }

        $ok = $this->db->prepare('UPDATE cong_viec SET status = :status, updated_at = NOW() WHERE id = :id')
            ->execute(['status' => $status, 'id' => $id]);

        if ($ok) {
            $logStmt = $this->db->prepare(
                'INSERT INTO lich_su_trang_thai_cong_viec(task_id, user_id, old_status, new_status, note, created_at)
                 VALUES(:task_id, :user_id, :old_status, :new_status, :note, NOW())'
            );
            $logStmt->execute([
                'task_id' => $id,
                'user_id' => $userId,
                'old_status' => $old['status'],
                'new_status' => $status,
                'note' => $note,
            ]);
        }

        return $ok;
    }

    /**
     * Lưu kết quả duyệt công việc và thêm log trạng thái.
     */
    public function review(int $id, string $status, ?string $comment, ?int $score, int $userId): bool
    {
        $old = $this->find($id);
        if (!$old) {
            return false;
        }

        $approvedAt = $status === 'approved' ? date('Y-m-d H:i:s') : null;
        $stmt = $this->db->prepare(
            'UPDATE cong_viec
             SET status = :status,
                 review_comment = :review_comment,
                 review_score = :review_score,
                 approved_at = :approved_at,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $ok = $stmt->execute([
            'id' => $id,
            'status' => $status,
            'review_comment' => $comment,
            'review_score' => $score,
            'approved_at' => $approvedAt,
        ]);

        if ($ok) {
            $logStmt = $this->db->prepare(
                'INSERT INTO lich_su_trang_thai_cong_viec(task_id, user_id, old_status, new_status, note, created_at)
                 VALUES(:task_id, :user_id, :old_status, :new_status, :note, NOW())'
            );
            $logStmt->execute([
                'task_id' => $id,
                'user_id' => $userId,
                'old_status' => $old['status'],
                'new_status' => $status,
                'note' => $comment,
            ]);
        }

        return $ok;
    }

    /**
     * Lấy lịch sử thay đổi trạng thái của công việc.
     */
    public function logs(int $taskId): array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, u.name
             FROM lich_su_trang_thai_cong_viec l
             LEFT JOIN tai_khoan u ON u.id = l.user_id
             WHERE l.task_id = :task_id
             ORDER BY l.created_at DESC'
        );
        $stmt->execute(['task_id' => $taskId]);
        return $stmt->fetchAll();
    }

    /**
     * Kiểm tra người dùng có quyền xem/tải công việc và file liên quan hay không.
     */
    public function canAccess(array $user, int $taskId): bool
    {
        $task = $this->find($taskId);
        if (!$task) {
            return false;
        }

        if ($user['role'] === 'admin') {
            return true;
        }

        if ((int) $task['assignee_id'] === (int) $user['id'] || (int) $task['created_by'] === (int) $user['id']) {
            return true;
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM thanh_vien_du_an WHERE project_id = :project_id AND user_id = :user_id');
        $stmt->execute([
            'project_id' => $task['project_id'],
            'user_id' => $user['id'],
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Tính nhanh các chỉ số tổng quan cho Dashboard.
     */
    public function statsForDashboard(array $user): array
    {
        $cong_viec = $this->forUser($user);
        $stats = [
            'total' => count($cong_viec),
            'approved' => 0,
            'submitted' => 0,
            'overdue' => 0,
        ];

        $today = date('Y-m-d');
        foreach ($cong_viec as $task) {
            if ($task['status'] === 'approved') {
                $stats['approved']++;
            }
            if ($task['status'] === 'submitted') {
                $stats['submitted']++;
            }
            if ($task['deadline'] < $today && !in_array($task['status'], ['approved'], true)) {
                $stats['overdue']++;
            }
        }

        return $stats;
    }
}
