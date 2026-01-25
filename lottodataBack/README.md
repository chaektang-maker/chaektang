# Lotto Data Backend System

ระบบจัดการข้อมูลหวยย้อนหลัง โดยดึงข้อมูลจาก API และบันทึกลงฐานข้อมูล

## การติดตั้ง

### 1. สร้างฐานข้อมูล

รันไฟล์ SQL เพื่อสร้างฐานข้อมูลและตาราง:

```sql
-- เปิด phpMyAdmin หรือ MySQL command line
-- รันไฟล์ database/schema.sql
```

หรือรันคำสั่ง:
```bash
mysql -u root -p < database/schema.sql
```

### 2. ตั้งค่า Database Connection

แก้ไขไฟล์ `config/database.php` ตามการตั้งค่าของคุณ:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lottobackdata');
```

### 3. เปิดใช้งานระบบ

1. เปิดเว็บเบราว์เซอร์
2. ไปที่: `http://localhost/lottodataBack/admin/`
3. ใช้หน้า admin เพื่อดึงข้อมูลจาก API

## โครงสร้างโปรเจกต์

```
lottodataBack/
├── config/
│   └── database.php              # การตั้งค่าการเชื่อมต่อฐานข้อมูล
├── database/
│   └── schema.sql                # SQL schema สำหรับสร้างตาราง
├── admin/
│   ├── index.php                 # หน้า admin interface
│   ├── fetch_lotto_data.php      # API endpoint สำหรับดึงข้อมูลรายการหวย
│   └── fetch_lotto_details.php   # API endpoint สำหรับดึงข้อมูลหวยรายละเอียด
└── README.md
```

## ฟีเจอร์

### ดึงข้อมูลรายการหวย
- ✅ ดึงข้อมูลจาก API ตั้งแต่หน้า 23 ถึง 1
- ✅ บันทึกข้อมูลลงฐานข้อมูล MySQL
- ✅ ตรวจสอบข้อมูลซ้ำ (ไม่บันทึกซ้ำ)
- ✅ อัปเดตข้อมูลที่มีอยู่แล้ว

### ดึงข้อมูลหวยรายละเอียด
- ✅ ดึงข้อมูลหวยรายละเอียดจาก URL ที่เก็บไว้
- ✅ บันทึกรางวัลต่างๆ (รางวัลที่ 1-5, รางวัลข้างเคียง)
- ✅ บันทึกเลขวิ่ง (เลขหน้า 3 ตัว, เลขท้าย 3 ตัว, เลขท้าย 2 ตัว)
- ✅ รองรับการดึงข้อมูลทั้งหมด, แบบ batch, หรือรายการเดียว
- ✅ ตรวจสอบสถานะการดึงข้อมูล (is_fetched)

### หน้า Admin Interface
- ✅ หน้า admin interface ที่สวยงาม
- ✅ แสดงสถิติและข้อมูลล่าสุด
- ✅ แสดงสถานะการดึงข้อมูล

## API Endpoints

### POST /admin/fetch_lotto_data.php

**ดึงข้อมูลทั้งหมด:**
```json
{
  "action": "fetch_all"
}
```

**ดึงข้อมูลหน้าเดียว:**
```json
{
  "action": "fetch_single",
  "page": 1
}
```

### POST /admin/fetch_lotto_details.php

**ดึงข้อมูลหวยรายละเอียดทั้งหมดที่ยังไม่ได้ดึง:**
```json
{
  "action": "fetch_all_pending"
}
```

**ดึงข้อมูลหวยรายละเอียดแบบ Batch:**
```json
{
  "action": "fetch_batch",
  "limit": 10
}
```

**ดึงข้อมูลหวยรายละเอียดรายการเดียว:**
```json
{
  "action": "fetch_single",
  "lotto_id": "01042550"
}
```

## ข้อมูลที่เก็บ

### ตาราง `lotto_data`
เก็บข้อมูลรายการหวย:
- `lotto_id`: ID ของหวย (unique)
- `url`: URL ของข้อมูลหวย
- `date_text`: ข้อความวันที่
- `is_fetched`: สถานะการดึงข้อมูลรายละเอียด (0=ยังไม่ได้ดึง, 1=ดึงแล้ว)
- `created_at`: วันที่สร้าง
- `updated_at`: วันที่อัปเดตล่าสุด

### ตาราง `lotto_details`
เก็บข้อมูลหลักของหวย:
- `lotto_id`: ID ของหวย (foreign key)
- `date`: วันที่หวย
- `endpoint`: URL อ้างอิง

### ตาราง `lotto_prizes`
เก็บรางวัลต่างๆ:
- `lotto_id`: ID ของหวย (foreign key)
- `prize_id`: ID ของรางวัล (prizeFirst, prizeFirstNear, prizeSecond, etc.)
- `prize_name`: ชื่อรางวัล
- `reward`: จำนวนเงินรางวัล
- `amount`: จำนวนรางวัล
- `numbers`: หมายเลขที่ถูกรางวัล (JSON array)

### ตาราง `lotto_running_numbers`
เก็บเลขวิ่ง:
- `lotto_id`: ID ของหวย (foreign key)
- `running_id`: ID ของเลขวิ่ง (runningNumberFrontThree, runningNumberBackThree, etc.)
- `running_name`: ชื่อเลขวิ่ง
- `reward`: จำนวนเงินรางวัล
- `amount`: จำนวนรางวัล
- `numbers`: หมายเลขที่ถูกรางวัล (JSON array)

## หมายเหตุ

### API ที่ใช้
- **รายการหวย**: `https://lotto.api.rayriffy.com/list/{page}` (หน้า 23 ถึง 1)
- **ข้อมูลหวยรายละเอียด**: `https://lotto.api.rayriffy.com/lotto/{lotto_id}`

### ข้อมูลสำคัญ
- ข้อมูลจะไม่ถูกบันทึกซ้ำ (ใช้ lotto_id เป็น unique key)
- ระบบจะอัปเดตข้อมูลที่มีอยู่แล้วอัตโนมัติ
- มีการหน่วงเวลา 0.5 วินาทีระหว่างการดึงข้อมูลแต่ละรายการเพื่อไม่ให้โหลด API หนักเกินไป