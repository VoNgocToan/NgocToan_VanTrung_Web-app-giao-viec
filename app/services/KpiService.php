<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Service tính điểm KPI cho nhân viên.
 */
class KpiService
{
    /**
     * Tạo báo cáo KPI cho một nhân viên hoặc toàn bộ nhân viên.
     */
    public function report(?int $userId = null): array
    {
        $db = Database::connection();

        $sql = "SELECT
                    u.id,
                    u.name,
                    u.email,
                    COUNT(t.id) AS total_tasks,
                    SUM(CASE WHEN t.status = 'approved' THEN 1 ELSE 0 END) AS approved_tasks,
                    SUM(CASE WHEN t.status = 'redo' THEN 1 ELSE 0 END) AS redo_tasks,
                    SUM(CASE WHEN t.status IN ('assigned','in_progress','submitted','blocked') AND t.deadline < CURDATE() THEN 1 ELSE 0 END) AS late_tasks,
                    SUM(CASE WHEN t.status = 'approved' AND DATE(t.approved_at) <= DATE(t.deadline) THEN 1 ELSE 0 END) AS on_time_tasks,
                    SUM(CASE WHEN t.status = 'approved' AND DATE(t.approved_at) > DATE(t.deadline) THEN 1 ELSE 0 END) AS approved_late_tasks,
                    COALESCE(SUM(t.review_score), 0) AS review_points
                FROM tai_khoan u
                LEFT JOIN cong_viec t ON t.assignee_id = u.id
                WHERE u.role = 'employee'";

        $params = [];
        if ($userId !== null) {
            $sql .= ' AND u.id = :user_id';
            $params['user_id'] = $userId;
        }

        $sql .= ' GROUP BY u.id, u.name, u.email ORDER BY u.name';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['kpi_score'] = (int) $row['on_time_tasks'] * 10
                + (int) $row['approved_tasks'] * 5
                + (int) $row['review_points']
                - (int) $row['late_tasks'] * 5
                - (int) $row['redo_tasks'] * 3;
        }

        usort($rows, fn ($a, $b) => $b['kpi_score'] <=> $a['kpi_score']);
        return $rows;
    }
}
