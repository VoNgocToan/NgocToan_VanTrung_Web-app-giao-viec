<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/**
 * Service mã hóa/giải mã file bằng AES-256-CBC.
 */
class FileCryptoService
{
    /**
     * Sinh khóa nhị phân từ APP_KEY để dùng cho OpenSSL.
     */
    private function key(): string
    {
        return hash('sha256', APP_KEY, true);
    }

    /**
     * Mã hóa file tạm và lưu thành file .bin trong storage/encrypted.
     */
    public function encryptAndStore(string $tmpPath, string $originalName): array
    {
        $content = file_get_contents($tmpPath);
        if ($content === false) {
            throw new RuntimeException('Không đọc được file tải lên.');
        }

        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($content, 'AES-256-CBC', $this->key(), OPENSSL_RAW_DATA, $iv);
        if ($encrypted === false) {
            throw new RuntimeException('Mã hóa file thất bại.');
        }

        $storedName = uniqid('enc_', true) . '.bin';
        $path = STORAGE_PATH . '/encrypted/' . $storedName;
        $bytes = file_put_contents($path, $iv . $encrypted);

        if ($bytes === false) {
            throw new RuntimeException('Không lưu được file đã mã hóa.');
        }

        return [
            'stored_name' => $storedName,
            'path' => $path,
        ];
    }

    /**
     * Giải mã file đã lưu trong storage khi người dùng hợp lệ yêu cầu tải xuống.
     */
    public function decrypt(string $path): string
    {
        if (!file_exists($path)) {
            throw new RuntimeException('File không tồn tại.');
        }

        $raw = file_get_contents($path);
        if ($raw === false || strlen($raw) <= 16) {
            throw new RuntimeException('Dữ liệu file không hợp lệ.');
        }

        $iv = substr($raw, 0, 16);
        $encrypted = substr($raw, 16);
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $this->key(), OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new RuntimeException('Giải mã file thất bại.');
        }

        return $decrypted;
    }
}
