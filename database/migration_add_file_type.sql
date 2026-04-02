-- Migration: Thêm cột file_type vào bảng tep_dinh_kem
-- Lưu ý: Chạy câu lệnh này trong phpMyAdmin trước khi sử dụng tính năng manager upload

-- Thêm cột project_id để hỗ trợ file đính kèm dự án và phân quyền theo thành viên dự án
ALTER TABLE tep_dinh_kem
ADD COLUMN project_id INT NULL AFTER task_id,
ADD COLUMN file_type VARCHAR(20) DEFAULT 'employee' AFTER uploaded_by,
ADD COLUMN upload_reason VARCHAR(255) NULL AFTER file_type;

-- file_type: 'employee' = file employee nộp, 'manager' = file manager gửi hướng dẫn
-- upload_reason: lý do/mô tả tại sao manager upload file này
