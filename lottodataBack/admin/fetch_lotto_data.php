<?php
// ปิด error display เพื่อไม่ให้ส่ง HTML error มาปนกับ JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// เริ่ม output buffering เพื่อจับ error
ob_start();

require_once '../config/database.php';

header('Content-Type: application/json; charset=utf-8');

class LottoDataFetcher {
    private $conn;
    private $baseUrl = 'https://lotto.api.rayriffy.com/list/';
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * ดึงข้อมูลจาก API
     */
    private function fetchFromAPI($pageNumber) {
        $url = $this->baseUrl . $pageNumber;
        
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
     * บันทึกข้อมูลลง database
     */
    private function saveToDatabase($items) {
        $inserted = 0;
        $updated = 0;
        $errors = [];
        
        foreach ($items as $item) {
            $lottoId = $this->conn->real_escape_string($item['id']);
            $url = $this->conn->real_escape_string($item['url']);
            $dateText = $this->conn->real_escape_string($item['date']);
            
            // ตรวจสอบว่ามีข้อมูลอยู่แล้วหรือไม่
            $checkQuery = "SELECT id FROM lotto_data WHERE lotto_id = '$lottoId'";
            $result = $this->conn->query($checkQuery);
            
            if ($result && $result->num_rows > 0) {
                // อัปเดตข้อมูล
                $updateQuery = "UPDATE lotto_data SET url = '$url', date_text = '$dateText', updated_at = NOW() WHERE lotto_id = '$lottoId'";
                if ($this->conn->query($updateQuery)) {
                    $updated++;
                } else {
                    $errors[] = "Update error for lotto_id $lottoId: " . $this->conn->error;
                }
            } else {
                // เพิ่มข้อมูลใหม่
                $insertQuery = "INSERT INTO lotto_data (lotto_id, url, date_text) VALUES ('$lottoId', '$url', '$dateText')";
                if ($this->conn->query($insertQuery)) {
                    $inserted++;
                } else {
                    $errors[] = "Insert error for lotto_id $lottoId: " . $this->conn->error;
                }
            }
        }
        
        return [
            'inserted' => $inserted,
            'updated' => $updated,
            'errors' => $errors
        ];
    }
    
    /**
     * ดึงข้อมูลจาก API ตั้งแต่หน้า 23 ถึง 1 และบันทึกลง database
     */
    public function fetchAndSaveAll() {
        $totalInserted = 0;
        $totalUpdated = 0;
        $allErrors = [];
        $pageResults = [];
        
        // ดึงข้อมูลตั้งแต่หน้า 23 ถึง 1
        for ($page = 23; $page >= 1; $page--) {
            $result = $this->fetchFromAPI($page);
            
            if (!$result['success']) {
                $pageResults[] = [
                    'page' => $page,
                    'success' => false,
                    'error' => $result['error']
                ];
                continue;
            }
            
            $data = $result['data'];
            
            if (!isset($data['status']) || $data['status'] !== 'success') {
                $pageResults[] = [
                    'page' => $page,
                    'success' => false,
                    'error' => 'API returned non-success status'
                ];
                continue;
            }
            
            if (!isset($data['response']) || !is_array($data['response'])) {
                $pageResults[] = [
                    'page' => $page,
                    'success' => false,
                    'error' => 'Invalid response format'
                ];
                continue;
            }
            
            $items = $data['response'];
            $saveResult = $this->saveToDatabase($items);
            
            $totalInserted += $saveResult['inserted'];
            $totalUpdated += $saveResult['updated'];
            $allErrors = array_merge($allErrors, $saveResult['errors']);
            
            $pageResults[] = [
                'page' => $page,
                'success' => true,
                'items_count' => count($items),
                'inserted' => $saveResult['inserted'],
                'updated' => $saveResult['updated']
            ];
        }
        
        return [
            'success' => true,
            'total_inserted' => $totalInserted,
            'total_updated' => $totalUpdated,
            'total_errors' => count($allErrors),
            'errors' => $allErrors,
            'page_results' => $pageResults
        ];
    }
    
    /**
     * ดึงข้อมูลจากหน้าเดียว
     */
    public function fetchAndSaveSingle($pageNumber) {
        $result = $this->fetchFromAPI($pageNumber);
        
        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error']
            ];
        }
        
        $data = $result['data'];
        
        if (!isset($data['status']) || $data['status'] !== 'success') {
            return [
                'success' => false,
                'error' => 'API returned non-success status'
            ];
        }
        
        if (!isset($data['response']) || !is_array($data['response'])) {
            return [
                'success' => false,
                'error' => 'Invalid response format'
            ];
        }
        
        $items = $data['response'];
        $saveResult = $this->saveToDatabase($items);
        
        return [
            'success' => true,
            'page' => $pageNumber,
            'items_count' => count($items),
            'inserted' => $saveResult['inserted'],
            'updated' => $saveResult['updated'],
            'errors' => $saveResult['errors']
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
        
        $fetcher = new LottoDataFetcher();
        
        if ($action === 'fetch_all') {
            $result = $fetcher->fetchAndSaveAll();
            echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } elseif ($action === 'fetch_single' && isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page >= 1 && $page <= 23) {
                $result = $fetcher->fetchAndSaveSingle($page);
                echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid page number'], JSON_UNESCAPED_UNICODE);
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
