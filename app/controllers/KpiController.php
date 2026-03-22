<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Services\KpiService;

/**
 * Controller hiển thị báo cáo KPI cá nhân hoặc toàn hệ thống.
 */
class KpiController extends BaseController
{
    /**
     * Nạp báo cáo KPI theo vai trò người dùng.
     */
    public function index(): void
    {
        $this->requireLogin();
        $user = Auth::user();

        $service = new KpiService();
        $rows = $user['role'] === 'employee'
            ? $service->report((int) $user['id'])
            : $service->report();

        $this->render('kpi/index', ['rows' => $rows], 'Báo cáo KPI');
    }
}
