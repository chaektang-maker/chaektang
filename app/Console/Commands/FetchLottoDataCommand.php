<?php

namespace App\Console\Commands;

use App\Services\LottoDataFetcherService;
use Illuminate\Console\Command;

class FetchLottoDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lotto:fetch-data 
                            {--all : ดึงข้อมูลทั้งหมด (หน้า 23-1)}
                            {--page= : ดึงข้อมูลจากหน้าเดียว (1-23)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ดึงข้อมูลรายการหวยจาก API และบันทึกลงฐานข้อมูล';

    /**
     * Execute the console command.
     */
    public function handle(LottoDataFetcherService $service)
    {
        $this->info('เริ่มดึงข้อมูลหวย...');

        if ($this->option('all')) {
            // ดึงข้อมูลทั้งหมด
            $this->info('กำลังดึงข้อมูลทั้งหมด (หน้า 23-1)...');
            $result = $service->fetchAndSaveAll();
            
            $this->info("✅ ดึงข้อมูลเสร็จสิ้น!");
            $this->info("   - เพิ่มใหม่: {$result['total_inserted']} รายการ");
            $this->info("   - อัปเดต: {$result['total_updated']} รายการ");
            $this->info("   - เกิดข้อผิดพลาด: {$result['total_errors']} รายการ");
            
            if (!empty($result['errors'])) {
                $this->warn('ข้อผิดพลาด:');
                foreach (array_slice($result['errors'], 0, 10) as $error) {
                    $this->error("  - {$error}");
                }
                if (count($result['errors']) > 10) {
                    $this->warn("  ... และอีก " . (count($result['errors']) - 10) . " ข้อผิดพลาด");
                }
            }
        } elseif ($this->option('page')) {
            // ดึงข้อมูลจากหน้าเดียว
            $page = (int)$this->option('page');
            
            if ($page < 1 || $page > 23) {
                $this->error('หมายเลขหน้าต้องอยู่ระหว่าง 1-23');
                return 1;
            }
            
            $this->info("กำลังดึงข้อมูลจากหน้า {$page}...");
            $result = $service->fetchAndSaveSingle($page);
            
            if ($result['success']) {
                $this->info("✅ ดึงข้อมูลเสร็จสิ้น!");
                $this->info("   - จำนวนรายการ: {$result['items_count']}");
                $this->info("   - เพิ่มใหม่: {$result['inserted']} รายการ");
                $this->info("   - อัปเดต: {$result['updated']} รายการ");
                
                if (!empty($result['errors'])) {
                    $this->warn('ข้อผิดพลาด:');
                    foreach ($result['errors'] as $error) {
                        $this->error("  - {$error}");
                    }
                }
            } else {
                $this->error("❌ ดึงข้อมูลล้มเหลว: {$result['error']}");
                return 1;
            }
        } else {
            $this->error('กรุณาระบุ --all หรือ --page=เลขหน้า');
            $this->info('');
            $this->info('ตัวอย่างการใช้งาน:');
            $this->info('  php artisan lotto:fetch-data --all');
            $this->info('  php artisan lotto:fetch-data --page=1');
            return 1;
        }

        return 0;
    }
}
