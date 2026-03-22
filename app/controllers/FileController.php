<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Models\Attachment;
use App\Models\Log;
use App\Models\Task;
use App\Services\FileCryptoService;

/**
 * Controller xử lý tải file công việc sau khi kiểm tra quyền truy cập.
 */
class FileController extends BaseController
{
    /**
     * Giải mã và stream file cho người dùng hợp lệ.
     */
    public function download(int $id): void
    {
        $this->requireLogin();

        $attachmentModel = new Attachment();
        $attachment = $attachmentModel->find($id);

        if (!$attachment) {
            exit('File không tồn tại.');
        }

        if (!(new Task())->canAccess(Auth::user(), (int) $attachment['task_id'])) {
            http_response_code(403);
            exit('Bạn không có quyền tải file này.');
        }

        try {
            $content = (new FileCryptoService())->decrypt($attachment['encrypted_path']);
            (new Log())->create(Auth::user()['id'], 'download', 'attachment', $id, 'Tải file công việc');

            header('Content-Description: File Transfer');
            header('Content-Type: ' . ($attachment['mime_type'] ?: 'application/octet-stream'));
            header('Content-Disposition: attachment; filename="' . basename($attachment['original_name']) . '"');
            header('Content-Length: ' . strlen($content));
            echo $content;
            exit;
        } catch (\Throwable $e) {
            exit('Không thể giải mã hoặc tải file: ' . $e->getMessage());
        }
    }
}
