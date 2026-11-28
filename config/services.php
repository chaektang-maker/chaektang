<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Google AI Studio (Gemini API)
    |--------------------------------------------------------------------------
    | ใช้สำหรับระบบทำนายฝัน และฟีเจอร์ที่ใช้ AI
    | สร้าง API key ได้ที่ https://aistudio.google.com/app/apikey
    */
    'google_ai' => [
        'api_key' => env('AI_API_KEY'),
        'model' => env('AI_MODEL', 'gemini-1.5-flash'),
    ],

    /*
    |--------------------------------------------------------------------------
    | VIP Payment & Packages Configuration
    |--------------------------------------------------------------------------
    | ข้อมูลบัญชีธนาคาร / พร้อมเพย์ และแพ็กเกจสมาชิก VIP สำหรับแสดงในหน้า /request-vip
    */
    'vip_payment' => [
        'bank_name' => env('VIP_BANK_NAME', 'ธนาคารไทยพาณิชย์ (SCB)'),
        'account_name' => env('VIP_BANK_ACCOUNT_NAME', 'อธิวัฒน์ อ้นโตน'),
        'account_number' => env('VIP_BANK_ACCOUNT_NUMBER', '2922420833'),
        'promptpay_id' => env('VIP_PROMPTPAY_ID', '0834871695'),
        'packages' => [
            [
                'id' => 'draw_15d',
                'name' => 'แพ็กเกจรายงวด',
                'days' => 15,
                'price' => 99,
                'badge' => 'เริ่มต้น',
                'popular' => false,
                'description' => 'ปลดล็อกเลขเด็ด VIP + เลขมาแรงอันดับ 1 + เลขฝัน 3 ตัวตรง นาน 15 วัน (1 งวด)',
            ],
            [
                'id' => 'monthly_30d',
                'name' => 'แพ็กเกจรายเดือน',
                'days' => 30,
                'price' => 179,
                'badge' => 'ขายดีที่สุด',
                'popular' => true,
                'description' => 'คุ้มค่า! ครอบคลุม 2 งวดเต็ม ปลดล็อกทุกฟีเจอร์ VIP นาน 30 วัน',
            ],
            [
                'id' => 'quarterly_90d',
                'name' => 'แพ็กเกจ 3 เดือน',
                'days' => 90,
                'price' => 450,
                'badge' => 'ประหยัด 25%',
                'popular' => false,
                'description' => 'ครอบคลุม 6 งวดเต็ม เฉลี่ยงวดละ 75 บาท นาน 90 วัน',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Notification (แจ้งเตือนแอดมินเมื่อมีลูกค้าแนบสลิป VIP)
    |--------------------------------------------------------------------------
    */
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),
        'chat_id' => env('TELEGRAM_CHAT_ID', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact & LINE Official Configuration (สำหรับปุ่มลอยติดต่อแอดมินหน้าเว็บ)
    |--------------------------------------------------------------------------
    */
    'contact' => [
        'line_id' => env('CONTACT_LINE_ID', '@chaektang'),
        'line_url' => env('CONTACT_LINE_URL', 'https://line.me/ti/p/~@chaektang'),
        'phone' => env('CONTACT_PHONE', '083-487-1695'),
        'email' => env('CONTACT_EMAIL', 'chaektang@gmail.com'),
    ],

];
