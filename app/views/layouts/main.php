<?php $flash = consume_flash(); $authUser = current_user(); ?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= e($title) ?> - Web App Giao Việc</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
<?php if (!$authUser): ?>
    <main class="auth-shell">
        <div class="container py-5">
            <?= $content ?>
        </div>
    </main>
<?php else: ?>
    <div class="app-shell">
        <aside class="sidebar shadow-sm">
            <div class="sidebar-brand">
                <div class="brand-icon">PA</div>
                <div class="brand-title">Web app giao việc</div>
            </div>

            <nav class="sidebar-menu">
                <a class="menu-link <?= e(active_menu('dashboard/')) ?>" href="<?= e(route_url('dashboard/index')) ?>">
                    <span>Tổng quan</span>
                </a>
                <a class="menu-link <?= e(active_menu('cong_viec/')) ?>" href="<?= e(route_url('cong_viec/index')) ?>">
                    <span>Quản lý công việc</span>
                </a>
                <a class="menu-link <?= e(active_menu('kpi/')) ?>" href="<?= e(route_url('kpi/index')) ?>">
                    <span>Báo cáo KPI</span>
                </a>

                <?php if (in_array($authUser['role'], ['admin', 'manager'], true)): ?>
                    <a class="menu-link <?= e(active_menu('du_an/')) ?>" href="<?= e(route_url('du_an/index')) ?>">
                        <span>Quản lý dự án</span>
                    </a>
                <?php endif; ?>

                <?php if ($authUser['role'] === 'admin'): ?>
                    <a class="menu-link <?= e(active_menu('tai_khoan/')) ?>" href="<?= e(route_url('tai_khoan/index')) ?>">
                        <span>Quản lý tài khoản</span>
                    </a>
                    <a class="menu-link <?= e(active_menu('nhat_ky/')) ?>" href="<?= e(route_url('nhat_ky/index')) ?>">
                        <span>Nhật ký truy cập</span>
                    </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <div class="small text-secondary">Đề tài: xây dựng web app giao việc có mã hóa file và tính KPI.</div>
            </div>
        </aside>

        <div class="main-shell">
            <header class="topbar shadow-sm">
                <div class="page-info">
                    <div class="page-label">Hệ thống quản lý công việc theo dự án</div>
                </div>
                <div class="topbar-user">
                    <div class="avatar-badge"><?= e(strtoupper(substr($authUser['name'], 0, 1))) ?></div>
                    <div>
                        <div class="fw-semibold"><?= e($authUser['name']) ?></div>
                        <div class="small text-secondary"><?= e(role_label($authUser['role'])) ?></div>
                    </div>
                    <a class="btn btn-outline-secondary btn-sm" href="<?= e(route_url('auth/logout')) ?>">Đăng xuất</a>
                </div>
            </header>

            <main class="content-shell">
                <?php if ($flash): ?>
                    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show shadow-sm border-0">
                        <?= e($flash['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </main>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
