<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * เรียก Google AI Studio (Gemini) API เพื่อทำนายความฝัน
 * ใช้ API key จาก .env (AI_API_KEY)
 */
class DreamInterpretationService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $this->apiKey = config('services.google_ai.api_key', '');
        $this->model = config('services.google_ai.model', 'gemini-1.5-flash');
    }

    /**
     * ส่งข้อความฝันไปให้ AI ทำนาย และรับคำทำนายกลับมา (ภาษาไทย)
     *
     * @param string $dreamText เนื้อหาความฝันที่ผู้ใช้พิมพ์
     * @return array{ success: bool, message?: string, interpretation?: string }
     */
    public function interpret(string $dreamText): array
    {
        $dreamText = trim($dreamText);
        if ($dreamText === '') {
            return ['success' => false, 'message' => 'กรุณาพิมพ์ความฝันของคุณ'];
        }

        if ($this->apiKey === '') {
            Log::warning('DreamInterpretation: AI_API_KEY is not set');
            return ['success' => false, 'message' => 'ระบบทำนายฝันยังไม่ได้ตั้งค่า API key'];
        }

        $systemPrompt = $this->buildSystemPrompt();
        $userPrompt = "ความฝัน: " . $dreamText;

        // Gemini รับข้อความรวมใน parts ได้หลายส่วน (system + user แยกหรือรวมก็ได้)
        $fullPrompt = $systemPrompt . "\n\n" . $userPrompt;

        $url = "{$this->baseUrl}/models/{$this->model}:generateContent";
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $fullPrompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 1024,
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->apiKey,
            ])->timeout(30)->post($url, $payload);

            if (! $response->successful()) {
                Log::warning('DreamInterpretation: API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [
                    'success' => false,
                    'message' => 'เชื่อมต่อ AI ไม่ได้ ลองใหม่ภายหลัง',
                ];
            }

            $data = $response->json();
            $text = $this->extractTextFromResponse($data);

            if ($text === null || $text === '') {
                Log::warning('DreamInterpretation: Empty or invalid response', ['data' => $data]);
                return ['success' => false, 'message' => 'AI ไม่ได้ส่งคำทำนายกลับมา'];
            }

            return [
                'success' => true,
                'interpretation' => trim($text),
            ];
        } catch (\Exception $e) {
            Log::error('DreamInterpretation: Exception ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * สร้างข้อความ system prompt ให้ AI ทำหน้าที่เป็นหมอดูฝัน (ภาษาไทย)
     */
    private function buildSystemPrompt(): string
    {
        return <<<PROMPT
คุณเป็นผู้เชี่ยวชาญการทำนายความฝัน (หมอดูฝัน) ที่ตอบเป็นภาษาไทยเท่านั้น

กฎ:
- ตอบเฉพาะภาษาไทย
- อธิบายความหมายของความฝันอย่างสุภาพ ไม่ยาวเกินไป (ประมาณ 2–4 ย่อหน้า)
- ถ้าต้องการ สามารถแนะนำ "เลขเด็ด" จากสัญลักษณ์ในฝันได้ 1–3 ตัว (เช่น เลขสองหลักหรือสามหลัก) เป็นความบันเทิงเท่านั้น
- ไม่ใช้ภาษาหยาบหรือไม่เหมาะสม
PROMPT;
    }

    /**
     * ดึงข้อความจาก response ของ Gemini API
     */
    private function extractTextFromResponse(array $data): ?string
    {
        $candidates = $data['candidates'] ?? [];
        if (empty($candidates)) {
            return null;
        }
        $parts = $candidates[0]['content']['parts'] ?? [];
        if (empty($parts)) {
            return null;
        }
        return $parts[0]['text'] ?? null;
    }
}
