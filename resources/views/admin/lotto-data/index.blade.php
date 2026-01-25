<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - Lotto Data Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .stat-card p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .action-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        
        .action-card h3 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
            margin-top: 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .input-group {
            margin-bottom: 15px;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        .input-group input,
        .input-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 5px;
            font-size: 1em;
        }
        
        .input-group input:focus,
        .input-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .recent-data {
            margin-top: 30px;
        }
        
        .recent-data h3 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
            color: #667eea;
        }
        
        .loading.active {
            display: block;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }
        
        .alert.active {
            display: block;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎰 Lotto Data Management</h1>
            <p>ระบบจัดการข้อมูลหวยย้อนหลัง</p>
        </div>
        
        <div class="content">
            <div class="stats">
                <div class="stat-card">
                    <h3>{{ number_format($totalRecords) }}</h3>
                    <p>จำนวนข้อมูลทั้งหมด</p>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <h3>{{ number_format($fetchedRecords) }}</h3>
                    <p>ดึงข้อมูลแล้ว</p>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <h3>{{ number_format($pendingRecords) }}</h3>
                    <p>ยังไม่ได้ดึงข้อมูล</p>
                </div>
            </div>
            
            <div id="alert-container"></div>
            
            <div class="actions">
                <div class="action-card">
                    <h3>ดึงข้อมูลทั้งหมด</h3>
                    <p>ดึงข้อมูลจาก API ตั้งแต่หน้า 23 ถึง 1 และบันทึกลง database</p>
                    <button class="btn btn-success" onclick="fetchAllData()" id="btn-fetch-all">
                        ดึงข้อมูลทั้งหมด (23 หน้า)
                    </button>
                </div>
                
                <div class="action-card">
                    <h3>ดึงข้อมูลหน้าเดียว</h3>
                    <p>เลือกหน้าที่ต้องการดึงข้อมูล</p>
                    <div class="input-group">
                        <label for="page-number">หมายเลขหน้า (1-23):</label>
                        <select id="page-number">
                            @for ($i = 23; $i >= 1; $i--)
                                <option value="{{ $i }}">หน้า {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <button class="btn" onclick="fetchSinglePage()" id="btn-fetch-single">
                        ดึงข้อมูลหน้าเดียว
                    </button>
                </div>
            </div>
            
            <div class="actions" style="margin-top: 30px;">
                <div class="action-card">
                    <h3>ดึงข้อมูลหวยรายละเอียดทั้งหมด</h3>
                    <p>ดึงข้อมูลหวยรายละเอียดจาก URL ที่เก็บไว้ทั้งหมดที่ยังไม่ได้ดึง</p>
                    <button class="btn btn-success" onclick="fetchAllLottoDetails()" id="btn-fetch-all-details">
                        ดึงข้อมูลหวยรายละเอียดทั้งหมด
                    </button>
                </div>
                
                <div class="action-card">
                    <h3>ดึงข้อมูลหวยรายละเอียดแบบ Batch</h3>
                    <p>ดึงข้อมูลหวยรายละเอียดครั้งละจำนวนที่กำหนด</p>
                    <div class="input-group">
                        <label for="batch-limit">จำนวนรายการ (1-100):</label>
                        <input type="number" id="batch-limit" min="1" max="100" value="10">
                    </div>
                    <button class="btn" onclick="fetchBatchLottoDetails()" id="btn-fetch-batch-details">
                        ดึงข้อมูลแบบ Batch
                    </button>
                </div>
                
                <div class="action-card">
                    <h3>ดึงข้อมูลหวยรายละเอียดรายการเดียว</h3>
                    <p>ดึงข้อมูลหวยรายละเอียดจาก Lotto ID</p>
                    <div class="input-group">
                        <label for="lotto-id">Lotto ID:</label>
                        <input type="text" id="lotto-id" placeholder="เช่น 01042550">
                    </div>
                    <button class="btn" onclick="fetchSingleLottoDetail()" id="btn-fetch-single-detail">
                        ดึงข้อมูลรายการเดียว
                    </button>
                </div>
            </div>
            
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>กำลังประมวลผล...</p>
            </div>
            
            <div class="recent-data">
                <h3>ข้อมูลล่าสุด 10 รายการ</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Lotto ID</th>
                                <th>URL</th>
                                <th>วันที่</th>
                                <th>สถานะ</th>
                                <th>สร้างเมื่อ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($recentData->count() > 0)
                                @foreach($recentData as $row)
                                    <tr>
                                        <td>{{ $row->lotto_id }}</td>
                                        <td>{{ $row->url }}</td>
                                        <td>{{ $row->date_text }}</td>
                                        <td>
                                            @if($row->is_fetched)
                                                <span style="color: #28a745; font-weight: bold;">✓ ดึงแล้ว</span>
                                            @else
                                                <span style="color: #dc3545; font-weight: bold;">✗ ยังไม่ได้ดึง</span>
                                            @endif
                                        </td>
                                        <td>{{ $row->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #999;">
                                        ยังไม่มีข้อมูล
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // ตั้งค่า CSRF token สำหรับ fetch requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        function showAlert(message, type = 'info') {
            const alertContainer = document.getElementById('alert-container');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} active`;
            alert.textContent = message;
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alert);
            
            setTimeout(() => {
                alert.classList.remove('active');
            }, 5000);
        }
        
        function setLoading(loading) {
            const loadingEl = document.getElementById('loading');
            const btnFetchAll = document.getElementById('btn-fetch-all');
            const btnFetchSingle = document.getElementById('btn-fetch-single');
            const btnFetchAllDetails = document.getElementById('btn-fetch-all-details');
            const btnFetchBatchDetails = document.getElementById('btn-fetch-batch-details');
            const btnFetchSingleDetail = document.getElementById('btn-fetch-single-detail');
            
            if (loading) {
                loadingEl.classList.add('active');
                btnFetchAll.disabled = true;
                btnFetchSingle.disabled = true;
                if (btnFetchAllDetails) btnFetchAllDetails.disabled = true;
                if (btnFetchBatchDetails) btnFetchBatchDetails.disabled = true;
                if (btnFetchSingleDetail) btnFetchSingleDetail.disabled = true;
            } else {
                loadingEl.classList.remove('active');
                btnFetchAll.disabled = false;
                btnFetchSingle.disabled = false;
                if (btnFetchAllDetails) btnFetchAllDetails.disabled = false;
                if (btnFetchBatchDetails) btnFetchBatchDetails.disabled = false;
                if (btnFetchSingleDetail) btnFetchSingleDetail.disabled = false;
            }
        }
        
        async function fetchAllData() {
            if (!confirm('คุณต้องการดึงข้อมูลทั้งหมด 23 หน้าหรือไม่? กระบวนการนี้อาจใช้เวลาสักครู่')) {
                return;
            }
            
            setLoading(true);
            showAlert('กำลังดึงข้อมูลทั้งหมด...', 'info');
            
            try {
                const formData = new FormData();
                formData.append('action', 'fetch_all');
                
                const response = await fetch('{{ route("admin.lotto-data.fetch-all") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                        // ไม่ต้อง set Content-Type เพราะ browser จะ set ให้อัตโนมัติเมื่อใช้ FormData
                    },
                    body: formData
                });
                
                // ตรวจสอบ HTTP status code
                if (!response.ok) {
                    const text = await response.text();
                    console.error('HTTP Error:', response.status, text.substring(0, 500));
                    showAlert(`เกิดข้อผิดพลาด HTTP ${response.status}: ${response.statusText}`, 'error');
                    setLoading(false);
                    return;
                }
                
                // ตรวจสอบว่า response เป็น JSON หรือไม่
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Response is not JSON:', text.substring(0, 500));
                    showAlert('เกิดข้อผิดพลาด: Server ส่ง HTML กลับมาแทน JSON. อาจเป็นเพราะไม่ได้ login หรือ route ไม่ถูกต้อง', 'error');
                    setLoading(false);
                    return;
                }
                
                const result = await response.json();
                
                if (result.success) {
                    let message = `สำเร็จ! เพิ่มข้อมูลใหม่: ${result.total_inserted} รายการ, อัปเดต: ${result.total_updated} รายการ`;
                    if (result.total_errors > 0) {
                        message += `, ข้อผิดพลาด: ${result.total_errors} รายการ`;
                    }
                    showAlert(message, 'success');
                    
                    // รีเฟรชหน้าเว็บหลังจาก 2 วินาที
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    let errorMsg = result.error || 'Unknown error';
                    if (result.file && result.line) {
                        errorMsg += ` (File: ${result.file}, Line: ${result.line})`;
                    }
                    showAlert('เกิดข้อผิดพลาด: ' + errorMsg, 'error');
                    console.error('Error details:', result);
                }
            } catch (error) {
                showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + error.message, 'error');
            } finally {
                setLoading(false);
            }
        }
        
        async function fetchSinglePage() {
            const pageNumber = document.getElementById('page-number').value;
            
            setLoading(true);
            showAlert(`กำลังดึงข้อมูลหน้า ${pageNumber}...`, 'info');
            
            try {
                const formData = new FormData();
                formData.append('page', pageNumber);
                
                const response = await fetch('{{ route("admin.lotto-data.fetch-single") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                // ตรวจสอบ HTTP status code
                if (!response.ok) {
                    const text = await response.text();
                    console.error('HTTP Error:', response.status, text.substring(0, 500));
                    showAlert(`เกิดข้อผิดพลาด HTTP ${response.status}: ${response.statusText}`, 'error');
                    setLoading(false);
                    return;
                }
                
                // ตรวจสอบว่า response เป็น JSON หรือไม่
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Response is not JSON:', text.substring(0, 500));
                    showAlert('เกิดข้อผิดพลาด: Server ส่ง HTML กลับมาแทน JSON. อาจเป็นเพราะไม่ได้ login หรือ route ไม่ถูกต้อง', 'error');
                    setLoading(false);
                    return;
                }
                
                const result = await response.json();
                
                if (result.success) {
                    let message = `สำเร็จ! เพิ่มข้อมูลใหม่: ${result.inserted} รายการ, อัปเดต: ${result.updated} รายการ`;
                    if (result.errors && result.errors.length > 0) {
                        message += `, ข้อผิดพลาด: ${result.errors.length} รายการ`;
                    }
                    showAlert(message, 'success');
                    
                    // รีเฟรชหน้าเว็บหลังจาก 2 วินาที
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showAlert('เกิดข้อผิดพลาด: ' + (result.error || 'Unknown error'), 'error');
                }
            } catch (error) {
                showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + error.message, 'error');
            } finally {
                setLoading(false);
            }
        }
        
        async function fetchAllLottoDetails() {
            if (!confirm('คุณต้องการดึงข้อมูลหวยรายละเอียดทั้งหมดที่ยังไม่ได้ดึงหรือไม่? กระบวนการนี้อาจใช้เวลานาน')) {
                return;
            }
            
            setLoading(true);
            showAlert('กำลังดึงข้อมูลหวยรายละเอียดทั้งหมด...', 'info');
            
            try {
                const formData = new FormData();
                formData.append('action', 'fetch_all_pending');
                
                const response = await fetch('{{ route("admin.lotto-details.fetch-all-pending") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                // ตรวจสอบ HTTP status code
                if (!response.ok) {
                    const text = await response.text();
                    console.error('HTTP Error:', response.status, text.substring(0, 500));
                    showAlert(`เกิดข้อผิดพลาด HTTP ${response.status}: ${response.statusText}`, 'error');
                    setLoading(false);
                    return;
                }
                
                // ตรวจสอบว่า response เป็น JSON หรือไม่
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Response is not JSON:', text.substring(0, 500));
                    showAlert('เกิดข้อผิดพลาด: Server ส่ง HTML กลับมาแทน JSON. อาจเป็นเพราะไม่ได้ login หรือ route ไม่ถูกต้อง', 'error');
                    setLoading(false);
                    return;
                }
                
                const result = await response.json();
                
                if (result.success) {
                    let message = `สำเร็จ! ประมวลผล: ${result.total_processed} รายการ, สำเร็จ: ${result.total_success} รายการ, ล้มเหลว: ${result.total_failed} รายการ`;
                    showAlert(message, 'success');
                    
                    // รีเฟรชหน้าเว็บหลังจาก 2 วินาที
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showAlert('เกิดข้อผิดพลาด: ' + (result.error || 'Unknown error'), 'error');
                }
            } catch (error) {
                showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + error.message, 'error');
            } finally {
                setLoading(false);
            }
        }
        
        async function fetchBatchLottoDetails() {
            const limit = document.getElementById('batch-limit').value;
            
            if (!limit || limit < 1 || limit > 100) {
                showAlert('กรุณากรอกจำนวนรายการที่ถูกต้อง (1-100)', 'error');
                return;
            }
            
            setLoading(true);
            showAlert(`กำลังดึงข้อมูลหวยรายละเอียด ${limit} รายการ...`, 'info');
            
            try {
                const formData = new FormData();
                formData.append('limit', limit);
                
                const response = await fetch('{{ route("admin.lotto-details.fetch-batch") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                // ตรวจสอบ HTTP status code
                if (!response.ok) {
                    const text = await response.text();
                    console.error('HTTP Error:', response.status, text.substring(0, 500));
                    showAlert(`เกิดข้อผิดพลาด HTTP ${response.status}: ${response.statusText}`, 'error');
                    setLoading(false);
                    return;
                }
                
                // ตรวจสอบว่า response เป็น JSON หรือไม่
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Response is not JSON:', text.substring(0, 500));
                    showAlert('เกิดข้อผิดพลาด: Server ส่ง HTML กลับมาแทน JSON. อาจเป็นเพราะไม่ได้ login หรือ route ไม่ถูกต้อง', 'error');
                    setLoading(false);
                    return;
                }
                
                const result = await response.json();
                
                if (result.success) {
                    if (result.message) {
                        showAlert(result.message, 'info');
                    } else {
                        let message = `สำเร็จ! ดึงข้อมูลจาก ${result.total_pending || 0} รายการที่ยังไม่ได้ดึง, ประมวลผล: ${result.total_processed} รายการ, สำเร็จ: ${result.total_success} รายการ, ล้มเหลว: ${result.total_failed} รายการ`;
                        showAlert(message, 'success');
                    }
                    
                    // รีเฟรชหน้าเว็บหลังจาก 2 วินาที
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showAlert('เกิดข้อผิดพลาด: ' + (result.error || 'Unknown error'), 'error');
                }
            } catch (error) {
                showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + error.message, 'error');
            } finally {
                setLoading(false);
            }
        }
        
        async function fetchSingleLottoDetail() {
            const lottoId = document.getElementById('lotto-id').value.trim();
            
            if (!lottoId) {
                showAlert('กรุณากรอก Lotto ID', 'error');
                return;
            }
            
            setLoading(true);
            showAlert(`กำลังดึงข้อมูลหวยรายละเอียด ${lottoId}...`, 'info');
            
            try {
                const formData = new FormData();
                formData.append('lotto_id', lottoId);
                
                const response = await fetch('{{ route("admin.lotto-details.fetch-single") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                // ตรวจสอบ HTTP status code
                if (!response.ok) {
                    const text = await response.text();
                    console.error('HTTP Error:', response.status, text.substring(0, 500));
                    showAlert(`เกิดข้อผิดพลาด HTTP ${response.status}: ${response.statusText}`, 'error');
                    setLoading(false);
                    return;
                }
                
                // ตรวจสอบว่า response เป็น JSON หรือไม่
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Response is not JSON:', text.substring(0, 500));
                    showAlert('เกิดข้อผิดพลาด: Server ส่ง HTML กลับมาแทน JSON. อาจเป็นเพราะไม่ได้ login หรือ route ไม่ถูกต้อง', 'error');
                    setLoading(false);
                    return;
                }
                
                const result = await response.json();
                
                if (result.success) {
                    let message = `สำเร็จ! ดึงข้อมูล ${result.lotto_id} แล้ว (รางวัล: ${result.prizes_count}, เลขวิ่ง: ${result.running_numbers_count})`;
                    showAlert(message, 'success');
                    
                    // รีเฟรชหน้าเว็บหลังจาก 2 วินาที
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showAlert('เกิดข้อผิดพลาด: ' + (result.error || 'Unknown error'), 'error');
                }
            } catch (error) {
                showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + error.message, 'error');
            } finally {
                setLoading(false);
            }
        }
    </script>
</body>
</html>
