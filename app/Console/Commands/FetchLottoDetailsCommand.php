<?php

namespace App\Console\Commands;

use App\Services\LottoDetailsFetcherService;
use Illuminate\Console\Command;

class FetchLottoDetailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lotto:fetch-details 
                            {--all : ดึงข้อมูลทั้งหมดที่ยังไม่ได้ดึง}
                            {--batch= : ดึงข้อมูลแบบ batch (ระบุจำนวน)}
                            {--lotto-id= : ดึงข้อมูลจาก lotto_id เดียว}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ดึงข้อมูลหวยรายละเอียด (รางวัล, เลขวิ่ง) จาก API และบันทึกลงฐานข้อมูล';

    /**
     * Execute the console command.
     */
    public function handle(LottoDetailsFetcherService $service)
    {
        $this->info('เริ่มดึงข้อมูลหวยรายละเอียด...');

        if ($this->option('all')) {
            // ดึงข้อมูลทั้งหมดที่ยังไม่ได้ดึง
            $this->info('กำลังดึงข้อมูลทั้งหมดที่ยังไม่ได้ดึง...');
            $result = $service->fetchAndSaveAllPending();
            
            if ($result['success']) {
                $this->info("✅ ดึงข้อมูลเสร็จสิ้น!");
                $this->info("   - รายการที่รอดึง: {$result['total_pending']}");
                $this->info("   - ดึงแล้ว: {$result['total_processed']} รายการ");
                $this->info("   - สำเร็จ: {$result['total_success']} รายการ");
                $this->info("   - ล้มเหลว: {$result['total_failed']} รายการ");
                
                if ($result['total_failed'] > 0) {
                    $this->warn('รายการที่ล้มเหลว:');
                    $failedCount = 0;
                    foreach ($result['results'] as $res) {
                        if (!$res['success'] && $failedCount < 10) {
                            $this->error("  - {$res['lotto_id']}: {$res['error']}");
                            $failedCount++;
                        }
                    }
                }
            } else {
                $this->error("❌ ดึงข้อมูลล้มเหลว");
                return 1;
            }
        } elseif ($this->option('batch')) {
            // ดึงข้อมูลแบบ batch
            $limit = (int)$this->option('batch');
            
            if ($limit < 1 || $limit > 100) {
                $this->error('จำนวนต้องอยู่ระหว่าง 1-100');
                return 1;
            }
            
            $this->info("กำลังดึงข้อมูลแบบ batch (จำนวน {$limit} รายการ)...");
            $result = $service->fetchAndSaveBatch($limit);
            
            if ($result['success']) {
                $this->info("✅ ดึงข้อมูลเสร็จสิ้น!");
                $this->info("   - รายการที่รอดึง: {$result['total_pending']}");
                $this->info("   - ดึงแล้ว: {$result['total_processed']} รายการ");
                $this->info("   - สำเร็จ: {$result['total_success']} รายการ");
                $this->info("   - ล้มเหลว: {$result['total_failed']} รายการ");
            } else {
                $this->error("❌ ดึงข้อมูลล้มเหลว");
                return 1;
            }
        } elseif ($this->option('lotto-id')) {
            // ดึงข้อมูลจาก lotto_id เดียว
            $lottoId = $this->option('lotto-id');
            
            $this->info("กำลังดึงข้อมูลสำหรับ lotto_id: {$lottoId}...");
            $result = $service->fetchAndSaveSingle($lottoId);
            
            if ($result['success']) {
                $this->info("✅ ดึงข้อมูลเสร็จสิ้น!");
                $this->info("   - lotto_id: {$result['lotto_id']}");
                $this->info("   - วันที่: {$result['date']}");
                $this->info("   - จำนวนรางวัล: {$result['prizes_count']}");
                $this->info("   - จำนวนเลขวิ่ง: {$result['running_numbers_count']}");
            } else {
                $this->error("❌ ดึงข้อมูลล้มเหลว: {$result['error']}");
                return 1;
            }
        } else {
            $this->error('กรุณาระบุ --all, --batch=จำนวน หรือ --lotto-id=id');
            $this->info('');
            $this->info('ตัวอย่างการใช้งาน:');
            $this->info('  php artisan lotto:fetch-details --all');
            $this->info('  php artisan lotto:fetch-details --batch=10');
            $this->info('  php artisan lotto:fetch-details --lotto-id=01042550');
            return 1;
        }

        return 0;
    }
}
