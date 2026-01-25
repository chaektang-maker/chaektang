<?php
// ปิด error display เพื่อไม่ให้ส่ง HTML error มาปนกับ JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// เริ่ม output buffering เพื่อจับ error
ob_start();

require_once '../config/database.php';

header('Content-Type: application/json; charset=utf-8');

class LottoDetailsFetcher {
    private $conn;
    private $baseUrl = 'https://lotto.api.rayriffy.com';
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * ดึงข้อมูลหวยรายละเอียดจาก API
     */
    private function fetchDetailsFromAPI($lottoId) {
        $url = $this->baseUrl . '/lotto/' . $lottoId;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['success' => false, 'error' => 'CURL Error: ' . $error];
        }
        
        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'HTTP Error: ' . $httpCode];
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'error' => 'JSON Error: ' . json_last_error_msg()];
        }
        
        return ['success' => true, 'data' => $data];
    }
    
    /**
     * บันทึกข้อมูลหวยรายละเอียดลง database
     */
    private function saveDetailsToDatabase($lottoId, $details) {
        $this->conn->begin_transaction();
        
        try {
            // ตรวจสอบว่ามีข้อมูลอยู่แล้วหรือไม่
            $checkQuery = "SELECT id FROM lotto_details WHERE lotto_id = ?";
            $stmt = $this->conn->prepare($checkQuery);
            $stmt->bind_param("s", $lottoId);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->num_rows > 0;
            $stmt->close();
            
            // บันทึกหรืออัปเดตข้อมูลหลัก
            $date = $this->conn->real_escape_string($details['date']);
            $endpoint = $this->conn->real_escape_string($details['endpoint']);
            
            if ($exists) {
                $updateQuery = "UPDATE lotto_details SET date = ?, endpoint = ?, updated_at = NOW() WHERE lotto_id = ?";
                $stmt = $this->conn->prepare($updateQuery);
                $stmt->bind_param("sss", $date, $endpoint, $lottoId);
                $stmt->execute();
                $stmt->close();
            } else {
                $insertQuery = "INSERT INTO lotto_details (lotto_id, date, endpoint) VALUES (?, ?, ?)";
                $stmt = $this->conn->prepare($insertQuery);
                $stmt->bind_param("sss", $lottoId, $date, $endpoint);
                $stmt->execute();
                $stmt->close();
            }
            
            // ลบรางวัลเก่าถ้ามี
            $deletePrizesQuery = "DELETE FROM lotto_prizes WHERE lotto_id = ?";
            $stmt = $this->conn->prepare($deletePrizesQuery);
            $stmt->bind_param("s", $lottoId);
            $stmt->execute();
            $stmt->close();
            
            // บันทึกรางวัล
            if (isset($details['prizes']) && is_array($details['prizes'])) {
                foreach ($details['prizes'] as $prize) {
                    $prizeId = $this->conn->real_escape_string($prize['id']);
                    $prizeName = $this->conn->real_escape_string($prize['name']);
                    $reward = $this->conn->real_escape_string($prize['reward']);
                    $amount = intval($prize['amount']);
                    $numbers = json_encode($prize['number'], JSON_UNESCAPED_UNICODE);
                    
                    $insertPrizeQuery = "INSERT INTO lotto_prizes (lotto_id, prize_id, prize_name, reward, amount, numbers) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $this->conn->prepare($insertPrizeQuery);
                    $stmt->bind_param("ssssis", $lottoId, $prizeId, $prizeName, $reward, $amount, $numbers);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            
            // ลบเลขวิ่งเก่าถ้ามี
            $deleteRunningQuery = "DELETE FROM lotto_running_numbers WHERE lotto_id = ?";
            $stmt = $this->conn->prepare($deleteRunningQuery);
            $stmt->bind_param("s", $lottoId);
            $stmt->execute();
            $stmt->close();
            
            // บันทึกเลขวิ่ง
            if (isset($details['runningNumbers']) && is_array($details['runningNumbers'])) {
                foreach ($details['runningNumbers'] as $running) {
                    $runningId = $this->conn->real_escape_string($running['id']);
                    $runningName = $this->conn->real_escape_string($running['name']);
                    $reward = $this->conn->real_escape_string($running['reward']);
                    $amount = intval($running['amount']);
                    $numbers = json_encode($running['number'], JSON_UNESCAPED_UNICODE);
                    
                    $insertRunningQuery = "INSERT INTO lotto_running_numbers (lotto_id, running_id, running_name, reward, amount, numbers) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $this->conn->prepare($insertRunningQuery);
                    $stmt->bind_param("ssssis", $lottoId, $runningId, $runningName, $reward, $amount, $numbers);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            
            // อัปเดตสถานะ is_fetched ใน lotto_data
            $updateStatusQuery = "UPDATE lotto_data SET is_fetched = 1 WHERE lotto_id = ?";
            $stmt = $this->conn->prepare($updateStatusQuery);
            $stmt->bind_param("s", $lottoId);
            $stmt->execute();
            $stmt->close();
            
            $this->conn->commit();
            return ['success' => true];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * ดึงข้อมูลหวยรายละเอียดจาก lotto_id เดียว
     */
    public function fetchAndSaveSingle($lottoId) {
        $result = $this->fetchDetailsFromAPI($lottoId);
        
        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error'],
                'lotto_id' => $lottoId
            ];
        }
        
        $data = $result['data'];
        
        if (!isset($data['status']) || $data['status'] !== 'success') {
            return [
                'success' => false,
                'error' => 'API returned non-success status',
                'lotto_id' => $lottoId
            ];
        }
        
        if (!isset($data['response']) || !is_array($data['response'])) {
            return [
                'success' => false,
                'error' => 'Invalid response format',
                'lotto_id' => $lottoId
            ];
        }
        
        $details = $data['response'];
        $saveResult = $this->saveDetailsToDatabase($lottoId, $details);
        
        if ($saveResult['success']) {
            return [
                'success' => true,
                'lotto_id' => $lottoId,
                'date' => $details['date'] ?? '',
                'prizes_count' => isset($details['prizes']) ? count($details['prizes']) : 0,
                'running_numbers_count' => isset($details['runningNumbers']) ? count($details['runningNumbers']) : 0
            ];
        } else {
            return [
                'success' => false,
                'error' => $saveResult['error'],
                'lotto_id' => $lottoId
            ];
        }
    }
    
    /**
     * ดึงข้อมูลหวยรายละเอียดทั้งหมดที่ยังไม่ได้ดึง (is_fetched = 0)
     */
    public function fetchAndSaveAllPending() {
        // นับจำนวนรายการที่ยังไม่ได้ดึง
        $countQuery = "SELECT COUNT(*) as total FROM lotto_data WHERE is_fetched = 0";
        $countResult = $this->conn->query($countQuery);
        $totalPending = 0;
        if ($countResult) {
            $countRow = $countResult->fetch_assoc();
            $totalPending = $countRow['total'];
        }
        
        if ($totalPending == 0) {
            return [
                'success' => true,
                'message' => 'ไม่มีข้อมูลที่ต้องดึง (ทุกรายการดึงข้อมูลแล้ว)',
                'total_pending' => 0,
                'total_processed' => 0,
                'total_success' => 0,
                'total_failed' => 0,
                'results' => []
            ];
        }
        
        // ดึงรายการที่ยังไม่ได้ดึงข้อมูล (is_fetched = 0)
        $query = "SELECT lotto_id FROM lotto_data WHERE is_fetched = 0 ORDER BY lotto_id DESC";
        $result = $this->conn->query($query);
        
        if (!$result) {
            return [
                'success' => false,
                'error' => 'Database query error: ' . $this->conn->error
            ];
        }
        
        $totalProcessed = 0;
        $totalSuccess = 0;
        $totalFailed = 0;
        $results = [];
        
        while ($row = $result->fetch_assoc()) {
            $lottoId = $row['lotto_id'];
            $totalProcessed++;
            
            // ดึงข้อมูลจาก API: https://lotto.api.rayriffy.com/lotto/{lotto_id}
            $fetchResult = $this->fetchAndSaveSingle($lottoId);
            $results[] = $fetchResult;
            
            if ($fetchResult['success']) {
                $totalSuccess++;
            } else {
                $totalFailed++;
            }
            
            // หน่วงเวลาเล็กน้อยเพื่อไม่ให้โหลด API หนักเกินไป
            usleep(500000); // 0.5 วินาที
        }
        
        return [
            'success' => true,
            'total_pending' => $totalPending,
            'total_processed' => $totalProcessed,
            'total_success' => $totalSuccess,
            'total_failed' => $totalFailed,
            'results' => $results
        ];
    }
    
    /**
     * ดึงข้อมูลหวยรายละเอียดตามจำนวนที่กำหนด (เฉพาะ is_fetched = 0)
     */
    public function fetchAndSaveBatch($limit = 10) {
        $limit = intval($limit);
        if ($limit <= 0 || $limit > 100) {
            $limit = 10;
        }
        
        // นับจำนวนรายการที่ยังไม่ได้ดึง
        $countQuery = "SELECT COUNT(*) as total FROM lotto_data WHERE is_fetched = 0";
        $countResult = $this->conn->query($countQuery);
        $totalPending = 0;
        if ($countResult) {
            $countRow = $countResult->fetch_assoc();
            $totalPending = $countRow['total'];
        }
        
        if ($totalPending == 0) {
            return [
                'success' => true,
                'message' => 'ไม่มีข้อมูลที่ต้องดึง (ทุกรายการดึงข้อมูลแล้ว)',
                'total_pending' => 0,
                'total_processed' => 0,
                'total_success' => 0,
                'total_failed' => 0,
                'results' => []
            ];
        }
        
        // ดึงรายการที่ยังไม่ได้ดึงข้อมูล (is_fetched = 0)
        $query = "SELECT lotto_id FROM lotto_data WHERE is_fetched = 0 ORDER BY lotto_id DESC LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $totalProcessed = 0;
        $totalSuccess = 0;
        $totalFailed = 0;
        $results = [];
        
        while ($row = $result->fetch_assoc()) {
            $lottoId = $row['lotto_id'];
            $totalProcessed++;
            
            // ดึงข้อมูลจาก API: https://lotto.api.rayriffy.com/lotto/{lotto_id}
            $fetchResult = $this->fetchAndSaveSingle($lottoId);
            $results[] = $fetchResult;
            
            if ($fetchResult['success']) {
                $totalSuccess++;
            } else {
                $totalFailed++;
            }
            
            // หน่วงเวลาเล็กน้อยเพื่อไม่ให้โหลด API หนักเกินไป
            usleep(500000); // 0.5 วินาที
        }
        
        $stmt->close();
        
        return [
            'success' => true,
            'total_pending' => $totalPending,
            'total_processed' => $totalProcessed,
            'total_success' => $totalSuccess,
            'total_failed' => $totalFailed,
            'results' => $results
        ];
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // ล้าง output buffer
        ob_clean();
        
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        
        $fetcher = new LottoDetailsFetcher();
        
        if ($action === 'fetch_all_pending') {
            $result = $fetcher->fetchAndSaveAllPending();
            echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } elseif ($action === 'fetch_batch') {
            $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
            $result = $fetcher->fetchAndSaveBatch($limit);
            echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } elseif ($action === 'fetch_single' && isset($_POST['lotto_id'])) {
            $lottoId = trim($_POST['lotto_id']);
            if (!empty($lottoId)) {
                $result = $fetcher->fetchAndSaveSingle($lottoId);
                echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid lotto_id'], JSON_UNESCAPED_UNICODE);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid action'], JSON_UNESCAPED_UNICODE);
        }
    } catch (Exception $e) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'error' => 'Server error: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    } catch (Error $e) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'error' => 'Fatal error: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
} else {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid request method'], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
