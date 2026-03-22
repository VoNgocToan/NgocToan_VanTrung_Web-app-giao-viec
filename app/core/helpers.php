<?php
declare(strict_types=1);

use App\Core\Auth;

/**
 * Chuyển chuỗi sang dạng an toàn để hiển thị HTML.
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Tạo URL theo kiểu index.php?route=module/action.
 */
function route_url(string $route, array $params = []): string
{
    $query = array_merge(['route' => $route], $params);
    return 'index.php?' . http_build_query($query);
}

/**
 * Điều hướng sang route khác.
 */
function redirect(string $route, array $params = []): void
{
    header('Location: ' . route_url($route, $params));
    exit;
}

/**
 * Quay lại trang trước nếu có referrer.
 */
function back(): void
{
    $ref = $_SERVER['HTTP_REFERER'] ?? route_url('dashboard/index');
    header('Location: ' . $ref);
    exit;
}

/**
 * Lưu thông báo một lần vào session để hiển thị sau khi redirect.
 */
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = compact('type', 'message');
}

/**
 * Lấy và xóa thông báo flash khỏi session.
 */
function consume_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Đọc lại giá trị cũ của form khi submit lỗi.
 */
function old(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

/**
 * Trả về người dùng đang đăng nhập.
 */
function current_user(): ?array
{
    return Auth::user();
}

/**
 * Kiểm tra request hiện tại có phải POST hay không.
 */
function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/**
 * Đổi trạng thái kỹ thuật sang text tiếng Việt dễ nhìn hơn ở giao diện.
 */
function status_label(string $status): string
{
    return match ($status) {
        'new' => 'Mới tạo',
        'assigned' => 'Đã phân công',
        'in_progress' => 'Đang thực hiện',
        'blocked' => 'Bị chặn',
        'submitted' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'redo' => 'Yêu cầu làm lại',
        'active' => 'Đang hoạt động',
        'inactive' => 'Ngưng hoạt động',
        'planning' => 'Lên kế hoạch',
        'completed' => 'Hoàn thành',
        default => ucfirst($status),
    };
}

/**
 * Chọn màu badge theo trạng thái.
 */
function status_badge_class(string $status): string
{
    return match ($status) {
        'active', 'approved', 'completed' => 'success',
        'new' => 'secondary',
        'assigned' => 'info',
        'in_progress' => 'primary',
        'blocked' => 'warning',
        'submitted' => 'dark',
        'redo' => 'danger',
        'planning', 'inactive' => 'secondary',
        default => 'secondary',
    };
}

/**
 * Đổi mức ưu tiên sang tiếng Việt.
 */
function priority_label(string $priority): string
{
    return match ($priority) {
        'low' => 'Thấp',
        'medium' => 'Trung bình',
        'high' => 'Cao',
        default => ucfirst($priority),
    };
}

/**
 * Hiển thị nhãn vai trò.
 */
function role_label(string $role): string
{
    return match ($role) {
        'admin' => 'Admin',
        'manager' => 'Trưởng nhóm',
        'employee' => 'Nhân viên',
        default => ucfirst($role),
    };
}

/**
 * Định dạng ngày cho dễ nhìn trong giao diện báo cáo.
 */
function format_date(?string $value): string
{
    if (!$value) {
        return '-';
    }

    $time = strtotime($value);
    if ($time === false) {
        return $value;
    }

    return date('d/m/Y', $time);
}

/**
 * Định dạng ngày giờ cho phần nhật ký.
 */
function format_datetime(?string $value): string
{
    if (!$value) {
        return '-';
    }

    $time = strtotime($value);
    if ($time === false) {
        return $value;
    }

    return date('d/m/Y H:i', $time);
}

/**
 * Đánh dấu menu đang được chọn.
 */
function active_menu(string $routePrefix): string
{
    $route = (string) ($_GET['route'] ?? 'dashboard/index');
    return str_starts_with($route, $routePrefix) ? 'is-active' : '';
}
