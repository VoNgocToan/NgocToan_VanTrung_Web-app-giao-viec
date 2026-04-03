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
        $db->exec(
            'CREATE TABLE IF NOT EXISTS cong_viec_phu_trach (
                id INT(11) NOT NULL AUTO_INCREMENT,
                task_id INT(11) NOT NULL,
                user_id INT(11) NOT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_task_user (task_id, user_id),
                KEY idx_task_id (task_id),
                KEY idx_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci'
        );
        $db->exec(
            'INSERT IGNORE INTO cong_viec_phu_trach(task_id, user_id, created_at)
             SELECT id, assignee_id, NOW() FROM cong_viec WHERE assignee_id IS NOT NULL'
        );

        $sql = "SELECT u.id, u.name, u.email,
                       t.id AS task_id, t.status, t.deadline, t.approved_at, t.review_score
                FROM tai_khoan u
                LEFT JOIN cong_viec_phu_trach cpt ON cpt.user_id = u.id
                LEFT JOIN cong_viec t ON t.id = cpt.task_id
                WHERE u.role = 'employee'";

        $params = [];
        if ($userId !== null) {
            $sql .= ' AND u.id = :user_id';
            $params['user_id'] = $userId;
        }

        $sql .= ' ORDER BY u.name';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $grouped = [];
        foreach ($rows as $row) {
            $uid = (int) $row['id'];
            if (!isset($grouped[$uid])) {
                $grouped[$uid] = [
                    'id' => $uid,
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'total_tasks' => 0,
                    'approved_tasks' => 0,
                    'redo_tasks' => 0,
                    'late_tasks' => 0,
                    'on_time_tasks' => 0,
                    'approved_late_tasks' => 0,
                    'review_points' => 0,
                    '_task_ids' => [],
                ];
            }

            if (empty($row['task_id'])) {
                continue;
            }

            $taskId = (int) $row['task_id'];
            if (isset($grouped[$uid]['_task_ids'][$taskId])) {
                continue;
            }
            $grouped[$uid]['_task_ids'][$taskId] = true;
            $grouped[$uid]['total_tasks']++;

            if ($row['status'] === 'approved') {
                $grouped[$uid]['approved_tasks']++;
            }
            if ($row['status'] === 'redo') {
                $grouped[$uid]['redo_tasks']++;
            }
            if (in_array($row['status'], ['assigned', 'in_progress', 'submitted', 'blocked'], true) && $row['deadline'] < date('Y-m-d')) {
                $grouped[$uid]['late_tasks']++;
            }
            if ($row['status'] === 'approved' && !empty($row['approved_at']) && date('Y-m-d', strtotime($row['approved_at'])) <= $row['deadline']) {
                $grouped[$uid]['on_time_tasks']++;
            }
            if ($row['status'] === 'approved' && !empty($row['approved_at']) && date('Y-m-d', strtotime($row['approved_at'])) > $row['deadline']) {
                $grouped[$uid]['approved_late_tasks']++;
            }
            $grouped[$uid]['review_points'] += (int) ($row['review_score'] ?? 0);
        }

        $result = array_values($grouped);
        foreach ($result as &$row) {
            unset($row['_task_ids']);
            $row['kpi_score'] = (int) $row['on_time_tasks'] * 10
                + (int) $row['approved_tasks'] * 5
                + (int) $row['review_points']
                - (int) $row['late_tasks'] * 5
                - (int) $row['redo_tasks'] * 3;
        }

        usort($result, fn ($a, $b) => $b['kpi_score'] <=> $a['kpi_score']);
        return $result;
    }
}
