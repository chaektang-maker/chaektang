<?php

namespace App\Services;

use App\Models\VipRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TelegramNotificationService
{
    /**
     * ส่งแจ้งเตือนคำขอสมัคร VIP พร้อมรูปสลิปโอนเงินเข้า Telegram ของแอดมิน
     */
    public function sendVipRequestNotification(VipRequest $vipRequest): bool
    {
        $botToken = trim((string) config('services.telegram.bot_token', ''));
        $chatId = trim((string) config('services.telegram.chat_id', ''));

        if ($botToken === '' || $chatId === '') {
            return false;
        }

        $vipRequest->loadMissing('customer');
        $customerName = $vipRequest->customer?->name ?? '-';
        $customerEmail = $vipRequest->customer?->email ?? '-';
        $amountText = $vipRequest->amount !== null
            ? number_format((float) $vipRequest->amount, 2) . ' บาท'
            : 'ไม่ระบุยอด';
        $accountName = $vipRequest->bank_account_name ?: '-';
        $accountNumber = $vipRequest->bank_account_number ?: '-';
        $note = $vipRequest->note ?: '-';
        $approveUrl = url("/backoffice/vip-requests/{$vipRequest->id}");

        $caption = implode("\n", [
            "👑 *มีคำขอสมัคร VIP ใหม่!*",
            "━━━━━━━━━━━━━━━━━━",
            "👤 *ลูกค้า:* {$this->escapeMarkdown($customerName)}",
            "📧 *อีเมล:* {$this->escapeMarkdown($customerEmail)}",
            "💰 *ยอดโอน:* {$this->escapeMarkdown($amountText)}",
            "🏦 *ชื่อบัญชีผู้โอน:* {$this->escapeMarkdown($accountName)}",
            "🔢 *เลขท้ายบัญชี:* {$this->escapeMarkdown($accountNumber)}",
            "📝 *แพ็กเกจ/หมายเหตุ:* {$this->escapeMarkdown($note)}",
            "🕒 *เวลาแจ้ง:* " . now()->format('d/m/Y H:i'),
            "━━━━━━━━━━━━━━━━━━",
            "👉 [กดที่นี่เพื่อตรวจสอบและอนุมัติ VIP]({$approveUrl})",
        ]);

        try {
            // หากมีไฟล์รูปสลิปใน storage/app/public ให้ส่งแบบแนบรูปสลิป (sendPhoto) ทันที
            if ($vipRequest->slip_path && Storage::disk('public')->exists($vipRequest->slip_path)) {
                $fullPath = Storage::disk('public')->path($vipRequest->slip_path);
                $filename = basename($fullPath);

                $response = Http::timeout(8)
                    ->attach('photo', file_get_contents($fullPath), $filename)
                    ->post("https://api.telegram.org/bot{$botToken}/sendPhoto", [
                        'chat_id' => $chatId,
                        'caption' => $caption,
                        'parse_mode' => 'Markdown',
                    ]);

                if ($response->successful()) {
                    return true;
                }
            }

            // Fallback: ส่งเป็นข้อความแจ้งเตือนปกติ (sendMessage)
            $response = Http::timeout(8)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $caption,
                'parse_mode' => 'Markdown',
                'disable_web_page_preview' => false,
            ]);

            if (!$response->successful()) {
                Log::warning('Telegram VIP notification failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Telegram VIP notification exception: ' . $e->getMessage());
            return false;
        }
    }

    private function escapeMarkdown(string $text): string
    {
        return str_replace(['_', '*', '`', '['], ['\_', '\*', '\`', '\['], $text);
    }
}

