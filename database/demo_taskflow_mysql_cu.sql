CREATE DATABASE IF NOT EXISTS Project_App CHARACTER SET utf8 COLLATE utf8_general_ci;
USE Project_App;

DROP TABLE IF EXISTS nhat_ky_truy_cap;
DROP TABLE IF EXISTS tep_dinh_kem;
DROP TABLE IF EXISTS lich_su_trang_thai_cong_viec;
DROP TABLE IF EXISTS cong_viec;
DROP TABLE IF EXISTS thanh_vien_du_an;
DROP TABLE IF EXISTS du_an;
DROP TABLE IF EXISTS tai_khoan;

CREATE TABLE tai_khoan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'employee') NOT NULL DEFAULT 'employee',
    department VARCHAR(100) DEFAULT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL
);

CREATE TABLE du_an (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    status ENUM('planning', 'active', 'completed') NOT NULL DEFAULT 'active',
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_du_an_created_by FOREIGN KEY (created_by) REFERENCES tai_khoan(id) ON DELETE RESTRICT
);

CREATE TABLE thanh_vien_du_an (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    project_role ENUM('lead', 'member', 'reviewer') NOT NULL DEFAULT 'member',
    joined_at DATETIME NOT NULL,
    UNIQUE KEY uk_project_member (project_id, user_id),
    CONSTRAINT fk_thanh_vien_du_an_project FOREIGN KEY (project_id) REFERENCES du_an(id) ON DELETE CASCADE,
    CONSTRAINT fk_thanh_vien_du_an_user FOREIGN KEY (user_id) REFERENCES tai_khoan(id) ON DELETE CASCADE
);

CREATE TABLE cong_viec (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    status ENUM('new', 'assigned', 'in_progress', 'blocked', 'submitted', 'approved', 'redo') NOT NULL DEFAULT 'new',
    deadline DATE NOT NULL,
    start_date DATE DEFAULT NULL,
    assignee_id INT DEFAULT NULL,
    created_by INT NOT NULL,
    expected_score INT NOT NULL DEFAULT 0,
    review_score INT DEFAULT NULL,
    review_comment TEXT DEFAULT NULL,
    approved_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_cong_viec_project FOREIGN KEY (project_id) REFERENCES du_an(id) ON DELETE CASCADE,
    CONSTRAINT fk_cong_viec_assignee FOREIGN KEY (assignee_id) REFERENCES tai_khoan(id) ON DELETE SET NULL,
    CONSTRAINT fk_cong_viec_created_by FOREIGN KEY (created_by) REFERENCES tai_khoan(id) ON DELETE RESTRICT
);

CREATE TABLE lich_su_trang_thai_cong_viec (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    old_status VARCHAR(50) NOT NULL,
    new_status VARCHAR(50) NOT NULL,
    note VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_task_logs_task FOREIGN KEY (task_id) REFERENCES cong_viec(id) ON DELETE CASCADE,
    CONSTRAINT fk_task_logs_user FOREIGN KEY (user_id) REFERENCES tai_khoan(id) ON DELETE CASCADE
);

CREATE TABLE tep_dinh_kem (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(150) DEFAULT NULL,
    file_size INT NOT NULL,
    encrypted_path VARCHAR(255) NOT NULL,
    uploaded_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_tep_dinh_kem_task FOREIGN KEY (task_id) REFERENCES cong_viec(id) ON DELETE CASCADE,
    CONSTRAINT fk_tep_dinh_kem_user FOREIGN KEY (uploaded_by) REFERENCES tai_khoan(id) ON DELETE RESTRICT
);

CREATE TABLE nhat_ky_truy_cap (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(50) NOT NULL,
    target_type VARCHAR(50) NOT NULL,
    target_id INT DEFAULT NULL,
    description VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_nhat_ky_truy_cap_user FOREIGN KEY (user_id) REFERENCES tai_khoan(id) ON DELETE SET NULL
);

INSERT INTO tai_khoan (id, name, email, password, role, department, status, created_at) VALUES
(1, 'Nguyen Admin', 'admin@taskflow.local', '$2y$12$ZTAdewvUJLPqmSxU6o3lROMlEjs7mfWX.EAySFNB3VBI9goD2npf2', 'admin', 'Ban CNTT', 'active', NOW()),
(2, 'Tran Quan Ly', 'manager@taskflow.local', '$2y$12$xxANoFxi4jMPl1ANwVf5WeuJ.fq3GTynS46wxUuyj7B1uCAEMBA86', 'manager', 'PMO', 'active', NOW()),
(3, 'Le Nhan Vien 1', 'employee1@taskflow.local', '$2y$12$4b1dzmsaU4obe86xP/Uybu8R0xaXghTHdD/gRNB4WWFf9b48mCVJe', 'employee', 'Kỹ thuật', 'active', NOW()),
(4, 'Pham Nhan Vien 2', 'employee2@taskflow.local', '$2y$12$vKjlixWa1n9xltdBeB1zYeQLhE01VNGJ2Tul.pDTP32OLdIzGePf6', 'employee', 'Thiết kế', 'active', NOW());

INSERT INTO du_an (id, code, name, description, start_date, end_date, priority, status, created_by, created_at) VALUES
(1, 'PRJ-001', 'Website giao việc nội bộ', 'Dự án demo theo đề tài môn học, có quản lý file mã hóa và KPI.', '2026-03-01', '2026-04-30', 'high', 'active', 2, NOW()),
(2, 'PRJ-002', 'Cải tiến dashboard KPI', 'Bổ sung thống kê KPI theo nhân sự và dự án.', '2026-03-05', '2026-05-15', 'medium', 'planning', 2, NOW());

INSERT INTO thanh_vien_du_an (project_id, user_id, project_role, joined_at) VALUES
(1, 2, 'lead', NOW()),
(1, 3, 'member', NOW()),
(1, 4, 'member', NOW()),
(2, 2, 'lead', NOW()),
(2, 3, 'member', NOW());

INSERT INTO cong_viec (id, project_id, title, description, priority, status, deadline, start_date, assignee_id, created_by, expected_score, review_score, review_comment, approved_at, created_at, updated_at) VALUES
(1, 1, 'Thiết kế giao diện đăng nhập', 'Tạo giao diện đăng nhập theo vai trò Admin/Manager/Employee.', 'high', 'approved', '2026-03-10', '2026-03-02', 3, 2, 10, 8, 'Hoàn thành tốt, giao diện đúng yêu cầu.', '2026-03-09 10:00:00', NOW(), NOW()),
(2, 1, 'Lập trình chức năng phân công việc', 'Xây dựng form phân công và lưu lịch sử giao việc.', 'high', 'submitted', '2026-03-18', '2026-03-11', 3, 2, 15, NULL, NULL, NULL, NOW(), NOW()),
(3, 1, 'Xây dựng upload file mã hóa', 'Upload file kết quả, mã hóa trước khi lưu.', 'high', 'in_progress', '2026-03-20', '2026-03-12', 4, 2, 18, NULL, NULL, NULL, NOW(), NOW()),
(4, 2, 'Báo cáo KPI theo nhân viên', 'Tính đúng hạn, trễ hạn, điểm review và xếp hạng KPI.', 'medium', 'redo', '2026-03-17', '2026-03-08', 3, 2, 12, 4, 'Cần bổ sung xử lý trường hợp công việc bị trả lại.', NULL, NOW(), NOW());

INSERT INTO lich_su_trang_thai_cong_viec (task_id, user_id, old_status, new_status, note, created_at) VALUES
(1, 2, 'new', 'assigned', 'Phân công cho nhân viên 1', NOW()),
(1, 3, 'assigned', 'submitted', 'Đã hoàn thành và gửi duyệt', NOW()),
(1, 2, 'submitted', 'approved', 'Duyệt đạt', NOW()),
(2, 2, 'new', 'assigned', 'Phân công cho nhân viên 1', NOW()),
(2, 3, 'assigned', 'submitted', 'Đã nộp chờ duyệt', NOW()),
(3, 2, 'new', 'assigned', 'Phân công cho nhân viên 2', NOW()),
(3, 4, 'assigned', 'in_progress', 'Đang lập trình và test', NOW()),
(4, 2, 'submitted', 'redo', 'Yêu cầu bổ sung KPI', NOW());

INSERT INTO nhat_ky_truy_cap(user_id, action, target_type, target_id, description, created_at) VALUES
(1, 'login', 'auth', NULL, 'Admin đăng nhập hệ thống', NOW()),
(2, 'create', 'project', 1, 'Tạo dự án Website giao việc nội bộ', NOW()),
(2, 'assign', 'task', 2, 'Phân công công việc cho nhân viên', NOW()),
(3, 'update_status', 'task', 2, 'Nhân viên cập nhật trạng thái submitted', NOW());
