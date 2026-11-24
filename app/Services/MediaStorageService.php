<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaStorageService
{
    /**
     * ดึงชื่อ Disk ที่ใช้เก็บรูปภาพมีเดีย ('public' หรือ 's3' ตามค่า FILESYSTEM_MEDIA_DISK ใน .env)
     */
    public static function diskName(): string
    {
        return (string) config('filesystems.media_disk', 'public');
    }

    /**
     * อัปโหลดไฟล์รูปภาพโดยจัดโครงสร้างโฟลเดอร์แยกตามหมวดหมู่ + ปี/เดือน (YYYY/MM)
     * และตั้งชื่อไฟล์ให้เป็นระเบียบ (prefix-YYYYMMDD-HHMMSS-random.ext)
     * พร้อมรองรับทั้ง Local Storage และ Amazon S3
     *
     * @param UploadedFile $file
     * @param string $category เช่น 'posts/covers', 'posts/content', 'affiliate-products', 'platforms', 'vip-slips'
     * @param string $prefix คำนำหน้าชื่อไฟล์ เช่น 'cover', 'content', 'product', 'platform', 'slip'
     * @return string Relative path ที่บันทึกใน Storage (หรือ Full URL กรณีใช้ S3)
     */
    public static function store(UploadedFile $file, string $category, string $prefix = 'img'): string
    {
        $disk = static::diskName();
        $yearMonth = now()->format('Y/m');
        $cleanCategory = trim($category, '/');
        $directory = "{$cleanCategory}/{$yearMonth}";

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $timestamp = now()->format('Ymd-His');
        $random = strtolower(Str::random(6));
        $filename = "{$prefix}-{$timestamp}-{$random}.{$ext}";

        $storedPath = $file->storeAs($directory, $filename, [
            'disk' => $disk,
            'visibility' => 'public',
        ]);

        if ($disk === 's3') {
            return Storage::disk('s3')->url($storedPath);
        }

        // สำหรับ Windows / Local ที่ไม่ได้ใช้ symlink ให้สำเนาไฟล์ไปที่ public/storage ด้วยเพื่อให้ Web Server เสิร์ฟไฟล์รูปได้โดยตรงและรวดเร็ว
        static::syncToPublicDirectory($storedPath);

        return $storedPath;
    }

    /**
     * สำเนาไฟล์จาก storage/app/public ไปยัง public/storage กรณีที่ public/storage ไม่ใช่ symlink
     */
    public static function syncToPublicDirectory(string $relativePath): void
    {
        try {
            $publicStorageRoot = public_path('storage');
            if (is_link($publicStorageRoot)) {
                return;
            }

            $sourceFullPath = Storage::disk('public')->path($relativePath);
            if (!is_file($sourceFullPath)) {
                return;
            }

            $targetFullPath = $publicStorageRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relativePath, '/\\'));
            $targetDir = dirname($targetFullPath);

            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }

            @copy($sourceFullPath, $targetFullPath);
        } catch (\Throwable $e) {
            // ไม่ให้กระทบการทำงานหลักหาก copy ไม่สำเร็จ
        }
    }

    /**
     * แปลงค่าที่เก็บในฐานข้อมูลให้เป็น URL สำหรับแสดงผลบนหน้าเว็บ
     * รองรับทั้ง URL เต็ม (จาก S3 / CDN) และ Path ภายในเครื่อง (/storage/...)
     */
    public static function url(?string $pathOrUrl): ?string
    {
        if (!$pathOrUrl) {
            return null;
        }

        $trimmed = trim($pathOrUrl);
        if ($trimmed === '') {
            return null;
        }

        // หากเป็นลิงก์ http://localhost/storage/... ให้แปลงเป็น /storage/... เพื่อให้เปิดได้ทุกพอร์ต
        if (preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?/storage/(.+)$#i', $trimmed, $m)) {
            return '/storage/' . ltrim($m[3], '/');
        }

        // หากเป็น Full URL จาก S3 หรือ CDN ภายนอก
        if (Str::startsWith($trimmed, ['http://', 'https://'])) {
            return $trimmed;
        }

        // หากมี /storage/ นำหน้าอยู่แล้ว
        if (Str::startsWith($trimmed, '/storage/')) {
            return $trimmed;
        }

        // กรณีเก็บเป็น relative path เช่น posts/covers/2026/11/...
        $disk = static::diskName();
        if ($disk === 's3' && !Storage::disk('public')->exists($trimmed)) {
            return Storage::disk('s3')->url($trimmed);
        }

        return '/storage/' . ltrim($trimmed, '/');
    }

    /**
     * ลบไฟล์รูปภาพออกจาก Storage (รองรับทั้ง Local และ S3)
     */
    public static function delete(?string $pathOrUrl): void
    {
        if (!$pathOrUrl) {
            return;
        }

        if (Str::startsWith($pathOrUrl, ['http://', 'https://'])) {
            // ถ้าเป็น S3 URL ให้พยายามแปลงกลับเป็น key หรือข้ามถ้าเป็น external URL
            $s3Url = rtrim((string) config('filesystems.disks.s3.url', ''), '/');
            if ($s3Url !== '' && Str::startsWith($pathOrUrl, $s3Url . '/')) {
                $key = ltrim(Str::after($pathOrUrl, $s3Url), '/');
                Storage::disk('s3')->delete($key);
            }
            return;
        }

        $cleanPath = ltrim(preg_replace('#^/storage/#', '', $pathOrUrl) ?? $pathOrUrl, '/');
        if (Storage::disk('public')->exists($cleanPath)) {
            Storage::disk('public')->delete($cleanPath);
        } elseif (static::diskName() === 's3') {
            Storage::disk('s3')->delete($cleanPath);
        }

        $publicCopy = public_path('storage/' . $cleanPath);
        if (!is_link(public_path('storage')) && is_file($publicCopy)) {
            @unlink($publicCopy);
        }
    }
}
