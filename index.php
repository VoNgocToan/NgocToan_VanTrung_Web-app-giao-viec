<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

header('Content-Type: text/html; charset=UTF-8');

// Tách route từ query string theo dạng module/action/id.
$route = $_GET['route'] ?? 'dashboard/index';
$route = trim($route, '/');
$parts = array_values(array_filter(explode('/', $route)));

$controllerKey = $parts[0] ?? 'dashboard';
$action = $parts[1] ?? 'index';
$id = $parts[2] ?? ($_GET['id'] ?? null);

if (is_string($id) && ctype_digit($id)) {
    $id = (int) $id;
}

// Map route tiếng Việt không dấu sang controller để dễ tìm trong code.
$controllerMap = [
    'auth' => \App\Controllers\AuthController::class,
    'dashboard' => \App\Controllers\DashboardController::class,
    'tai_khoan' => \App\Controllers\UserController::class,
    'du_an' => \App\Controllers\ProjectController::class,
    'cong_viec' => \App\Controllers\TaskController::class,
    'files' => \App\Controllers\FileController::class,
    'kpi' => \App\Controllers\KpiController::class,
    'nhat_ky' => \App\Controllers\LogController::class,
];

if (!isset($controllerMap[$controllerKey])) {
    http_response_code(404);
    exit('Controller not found');
}

$controller = new $controllerMap[$controllerKey]();

if (!method_exists($controller, $action)) {
    http_response_code(404);
    exit('Action not found');
}

if ($id !== null) {
    $controller->$action($id);
} else {
    $controller->$action();
}
