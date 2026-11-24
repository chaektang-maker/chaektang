<?php

return [
    'accepted' => 'กรุณายอมรับ :attribute',
    'confirmed' => 'การยืนยัน :attribute ไม่ตรงกัน',
    'email' => 'รูปแบบ :attribute ไม่ถูกต้อง',
    'image' => ':attribute ต้องเป็นไฟล์รูปภาพเท่านั้น',
    'integer' => ':attribute ต้องเป็นตัวเลขจำนวนเต็ม',
    'max' => [
        'array' => ':attribute ต้องมีไม่เกิน :max รายการ',
        'file' => 'ขนาดไฟล์ :attribute ต้องไม่เกิน :max กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่าไม่เกิน :max',
        'string' => ':attribute ต้องมีความยาวไม่เกิน :max ตัวอักษร',
    ],
    'min' => [
        'array' => ':attribute ต้องมีอย่างน้อย :min รายการ',
        'file' => 'ขนาดไฟล์ :attribute ต้องไม่น้อยกว่า :min กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่าอย่างน้อย :min',
        'string' => ':attribute ต้องมีความยาวอย่างน้อย :min ตัวอักษร',
    ],
    'numeric' => ':attribute ต้องเป็นตัวเลขเท่านั้น',
    'password' => [
        'letters' => ':attribute ต้องประกอบด้วยตัวอักษรอย่างน้อย 1 ตัว',
        'mixed' => ':attribute ต้องมีทั้งตัวพิมพ์ใหญ่และตัวพิมพ์เล็ก',
        'numbers' => ':attribute ต้องประกอบด้วยตัวเลขอย่างน้อย 1 ตัว',
        'symbols' => ':attribute ต้องประกอบด้วยอักขระพิเศษอย่างน้อย 1 ตัว',
        'uncompromised' => ':attribute นี้ไม่ปลอดภัย กรุณาใช้รหัสผ่านอื่น',
    ],
    'required' => 'กรุณากรอกข้อมูล :attribute',
    'string' => ':attribute ต้องเป็นข้อความ',
    'unique' => ':attribute นี้ถูกใช้งานในระบบแล้ว',

    'attributes' => [
        'name' => 'ชื่อ-นามสกุล',
        'email' => 'อีเมล',
        'password' => 'รหัสผ่าน',
        'password_confirmation' => 'ยืนยันรหัสผ่าน',
        'slip' => 'สลิปการโอนเงิน',
        'amount' => 'จำนวนเงิน',
    ],
];
