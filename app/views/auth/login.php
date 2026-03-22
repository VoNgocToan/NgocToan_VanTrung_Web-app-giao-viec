<div class="login-card">
    <div class="login-hero">
        <div class="hero-badge">Project_App • MVC • PHP & MySQL</div>
        <h1 class="section-title mb-2">Đăng nhập hệ thống giao việc</h1>
        <p class="section-desc">Bản demo phục vụ khóa luận: quản lý tài khoản, dự án, công việc, mã hóa file và báo cáo KPI.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-lg-5">
            <form method="post" action="<?= e(route_url('auth/login')) ?>">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email đăng nhập</label>
                    <input type="email" name="email" class="form-control" required value="<?= e(old('email', 'admin@taskflow.local')) ?>">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required value="admin123">
                </div>
                <button type="submit" class="btn btn-primary w-100">Đăng nhập vào Project_App</button>
            </form>

            <div class="info-strip mt-4">
                <div class="fw-semibold mb-2">Tài khoản mẫu để demo</div>
                <div class="small text-secondary">
                    <div><strong>Admin:</strong> admin@taskflow.local / admin123</div>
                    <div><strong>Trưởng nhóm:</strong> manager@taskflow.local / manager123</div>
                    <div><strong>Nhân viên:</strong> employee1@taskflow.local / employee123</div>
                </div>
            </div>
        </div>
    </div>
</div>
