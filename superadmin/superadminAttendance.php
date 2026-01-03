<?php
ob_start();
session_start();

// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminLogin.php');
    exit;
}

require_once '../Connection/conn.php';

/* ======================
   HELPERS
====================== */
function validateGmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) &&
           preg_match('/@gmail\.com$/', $email);
}

function validatePHNumber($num) {
    return preg_match('/^09\d{9}$/', $num);
}

function attendanceStatus($timeIn, $scheduled = '08:00:00') {
    return ($timeIn <= $scheduled) ? 'Present' : 'Late';
}

function getPositionLimits() {
    return [
        'Captain' => 1,
        'Secretary' => 1,
        'Treasurer' => 1,
        'SK Chairman' => 1,
        'Kagawad' => 7,
        'Tanod' => 10,
        'Barangay Health Worker' => 5,
        'Barangay Nutrition Scholar' => 3,
        'Barangay Day Care Worker' => 2,
        'SK Kagawad' => 7,
        'Other' => 20
    ];
}

function checkPositionLimit($conn, $position) {
    $limits = getPositionLimits();
    $limit = $limits[$position] ?? 20;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM officials WHERE position = ?");
    $stmt->bind_param("s", $position);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    return $result['count'] < $limit;
}

/* ======================
   AJAX HANDLERS
====================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture any output/errors
    ob_start();
    
    // Set JSON header early
    header('Content-Type: application/json');
    
    try {
        /* ADD OFFICIAL */
        if (isset($_POST['add_official'])) {
            $name = trim($_POST['full_name']);
            $pos  = $_POST['position'];
            $mail = trim($_POST['gmail']);
            $num  = trim($_POST['contact_number']);

            $errors = [];
            if (!$name) $errors[] = "Full name is required";
            if (!$pos)  $errors[] = "Position is required";
            if (!validateGmail($mail)) $errors[] = "Valid Gmail account is required";
            if (!validatePHNumber($num)) $errors[] = "Valid Philippine contact number is required";

            // Check position limit
            if (!checkPositionLimit($conn, $pos)) {
                $limits = getPositionLimits();
                $limit = $limits[$pos] ?? 20;
                $current = $conn->prepare("SELECT COUNT(*) as count FROM officials WHERE position = ?");
                $current->bind_param("s", $pos);
                $current->execute();
                $currentCount = $current->get_result()->fetch_assoc()['count'];
                $errors[] = "Position '{$pos}' is full ({$currentCount}/{$limit} slots)";
            }

            if ($errors) {
                ob_end_clean();
                echo json_encode(['success'=>false,'message'=>implode(', ', $errors)]);
                exit;
            }

            $chk = $conn->prepare("SELECT id FROM officials WHERE gmail=?");
            $chk->bind_param("s",$mail);
            $chk->execute();
            if ($chk->get_result()->num_rows) {
                ob_end_clean();
                echo json_encode(['success'=>false,'message'=>'Gmail account already exists']);
                exit;
            }

            $stmt = $conn->prepare(
                "INSERT INTO officials (full_name, position, gmail, contact_number)
                 VALUES (?,?,?,?)"
            );
            $stmt->bind_param("ssss",$name,$pos,$mail,$num);
            ob_end_clean();
            echo json_encode(['success'=>$stmt->execute()]);
            exit;
        }

        /* GET OFFICIALS */
        if ($_POST['action'] === 'get_officials') {
            $res = $conn->query("SELECT id, full_name, position FROM officials ORDER BY full_name");
            ob_end_clean();
            echo json_encode(['success'=>true,'officials'=>$res->fetch_all(MYSQLI_ASSOC)]);
            exit;
        }

        /* TIME IN */
        if (isset($_POST['time_in']) || ($_POST['action'] ?? '') === 'time_in') {
            $oid = (int)($_POST['official_id'] ?? 0);
            $today = date('Y-m-d');
            $now = date('H:i:s');
            
            // Validate official ID
            if ($oid <= 0) {
                ob_end_clean();
                echo json_encode(['success'=>false,'message'=>'Invalid official ID']);
                exit;
            }
            
            // Check if official exists
            $officialCheck = $conn->prepare("SELECT id FROM officials WHERE id = ?");
            $officialCheck->bind_param("i", $oid);
            $officialCheck->execute();
            if ($officialCheck->get_result()->num_rows === 0) {
                ob_end_clean();
                echo json_encode(['success'=>false,'message'=>'Official not found']);
                exit;
            }
            
            // Check if already timed in today
            $chk = $conn->prepare("SELECT id FROM attendance WHERE official_id=? AND date=?");
            $chk->bind_param("is",$oid,$today);
            $chk->execute();
            if ($chk->get_result()->num_rows) {
                ob_end_clean();
                echo json_encode(['success'=>false,'message'=>'Already timed in today']);
                exit;
            }

            // Get scheduled time - try settings table first, then use default
            $scheduled = '08:00:00';
            try {
                $sched = $conn->prepare("SELECT setting_value FROM attendance_settings WHERE setting_name = 'scheduled_time_in' LIMIT 1");
                $sched->execute();
                $result = $sched->get_result()->fetch_assoc();
                if ($result) {
                    $scheduled = $result['setting_value'];
                }
            } catch (Exception $e) {
                // Use default if settings table doesn't exist or has issues
                $scheduled = '08:00:00';
            }

            // Calculate status based on real time
            $status = attendanceStatus($now, $scheduled);

            // Try inserting with scheduled_time_in first
            try {
                $stmt = $conn->prepare(
                    "INSERT INTO attendance (official_id, date, time_in, status, scheduled_time_in)
                     VALUES (?,?,?,?,?)"
                );
                
                if (!$stmt) {
                    throw new Exception('Prepare failed: ' . $conn->error);
                }
                
                $bindResult = $stmt->bind_param("issss",$oid,$today,$now,$status,$scheduled);
                if (!$bindResult) {
                    throw new Exception('Bind param failed: ' . $stmt->error);
                }
                
                $result = $stmt->execute();
                if (!$result) {
                    throw new Exception('Execute failed: ' . $stmt->error);
                }
                
                ob_end_clean();
                echo json_encode(['success'=>true,'message'=>"Time in recorded successfully at {$now}"]);
                exit;
                
            } catch (Exception $e) {
                // If scheduled_time_in column doesn't exist, try without it
                if (strpos($e->getMessage(), 'scheduled_time_in') !== false) {
                    try {
                        $stmt = $conn->prepare(
                            "INSERT INTO attendance (official_id, date, time_in, status)
                             VALUES (?,?,?,?)"
                        );
                        
                        if (!$stmt) {
                            throw new Exception('Prepare failed: ' . $conn->error);
                        }
                        
                        $bindResult = $stmt->bind_param("isss",$oid,$today,$now,$status);
                        if (!$bindResult) {
                            throw new Exception('Bind param failed: ' . $stmt->error);
                        }
                        
                        $result = $stmt->execute();
                        if (!$result) {
                            throw new Exception('Execute failed: ' . $stmt->error);
                        }
                        
                        ob_end_clean();
                        echo json_encode(['success'=>true,'message'=>"Time in recorded successfully at {$now}"]);
                        exit;
                        
                    } catch (Exception $e2) {
                        ob_end_clean();
                        echo json_encode(['success'=>false,'message'=>'Failed to record time in: ' . $e2->getMessage()]);
                        exit;
                    }
                } else {
                    ob_end_clean();
                    echo json_encode(['success'=>false,'message'=>'Failed to record time in: ' . $e->getMessage()]);
                    exit;
                }
            }
        }

        /* TIME OUT */
        if (isset($_POST['time_out']) || ($_POST['action'] ?? '') === 'time_out') {
            $id = (int)($_POST['official_id'] ?? 0);
            $date = date('Y-m-d');
            $time = date('H:i:s');

            // Validate official ID
            if ($id <= 0) {
                ob_end_clean();
                echo json_encode(['success'=>false,'message'=>'Invalid official ID']);
                exit;
            }

            $stmt = $conn->prepare(
                "UPDATE attendance SET time_out=?
                 WHERE official_id=? AND date=? AND time_out IS NULL"
            );
            
            if (!$stmt) {
                ob_end_clean();
                echo json_encode(['success'=>false,'message'=>'Prepare failed: ' . $conn->error]);
                exit;
            }
            
            $bindResult = $stmt->bind_param("sis",$time,$id,$date);
            if (!$bindResult) {
                ob_end_clean();
                echo json_encode(['success'=>false,'message'=>'Bind param failed: ' . $stmt->error]);
                exit;
            }
            
            $result = $stmt->execute();
            ob_end_clean();
            
            if ($result && $stmt->affected_rows > 0) {
                echo json_encode(['success'=>true,'message'=>"Time out recorded successfully at {$time}"]);
            } elseif ($result && $stmt->affected_rows === 0) {
                echo json_encode(['success'=>false,'message'=>'No active time-in found for today']);
            } else {
                echo json_encode(['success'=>false,'message'=>'Failed to record time out: ' . $stmt->error]);
            }
            exit;
        }

        /* DELETE OFFICIAL */
        if ($_POST['action'] === 'delete_official') {
            $id = (int)($_POST['id'] ?? 0);
            
            // Validate ID
            if ($id <= 0) {
                ob_end_clean();
                echo json_encode(['success'=>false,'message'=>'Invalid official ID']);
                exit;
            }
            
            $conn->begin_transaction();
            try {
                // Delete attendance records first
                $a = $conn->prepare("DELETE FROM attendance WHERE official_id=?");
                $a->bind_param("i", $id);
                $a->execute();

                // Delete the official
                $o = $conn->prepare("DELETE FROM officials WHERE id=?");
                $o->bind_param("i", $id);
                $o->execute();

                $conn->commit();
                ob_end_clean();
                echo json_encode(['success'=>true,'message'=>'Official deleted successfully']);
            } catch(Exception $e){
                $conn->rollback();
                ob_end_clean();
                echo json_encode(['success'=>false,'message'=>'Delete failed: ' . $e->getMessage()]);
            }
            exit;
        }

        /* GET SERVER TIME */
        if ($_POST['action'] === 'get_server_time') {
            ob_end_clean();
            echo json_encode([
                'success' => true,
                'server_time' => date('H:i:s'),
                'server_date' => date('Y-m-d'),
                'timezone' => date_default_timezone_get()
            ]);
            exit;
        }

        /* GET POSITION STATS */
        if ($_POST['action'] === 'get_position_stats') {
            $limits = getPositionLimits();
            $stats = [];
            
            foreach ($limits as $position => $limit) {
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM officials WHERE position = ?");
                $stmt->bind_param("s", $position);
                $stmt->execute();
                $count = $stmt->get_result()->fetch_assoc()['count'];
                
                $stats[] = [
                    'position' => $position,
                    'current' => $count,
                    'limit' => $limit,
                    'available' => $limit - $count,
                    'is_full' => $count >= $limit
                ];
            }
            
            ob_end_clean();
            echo json_encode(['success'=>true, 'stats' => $stats]);
            exit;
        }

        // Default response for unknown actions
        ob_end_clean();
        echo json_encode(['success'=>false,'message'=>'Unknown action']);
        
    } catch (Exception $e) {
        ob_end_clean();
        echo json_encode(['success'=>false,'message'=>'PHP Error: ' . $e->getMessage()]);
    } catch (Error $e) {
        ob_end_clean();
        echo json_encode(['success'=>false,'message'=>'PHP Error: ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Super Admin Attendance</title>
<link rel="stylesheet" href="superadminAttendance.css">
</head>

<body>

<!-- SIDEBAR -->
<?php include 'superadminSidebar.php' ;?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="header">
        <h1>Attendance Management</h1>
        <div class="header-date">
            <div class="date" id="currentDate"></div>
            <div class="time" id="currentTime"></div>
        </div>
    </div>

    <div class="attendance-container">

    <!-- TAB BUTTONS -->
    <div class="tabs">
        <button class="tab-button active" onclick="switchTab('officials', this)">Officials</button>
        <button class="tab-button" onclick="switchTab('attendance', this)">Attendance</button>
    </div>

    <!-- OFFICIALS TAB -->
    <div id="officials-tab" class="tab-content active">

        <button class="primary-btn" onclick="showModal('addOfficialModal')">
            + Add Official
        </button>

        <!-- POSITION LIMITS -->
        <div class="position-limits">
            <h3>Position Limits</h3>
            <div class="limits-grid" id="positionLimitsGrid">
                <!-- Position limits will be loaded here -->
            </div>
        </div>

        <div class="official-grid">
            <?php
            $res = $conn->query("SELECT * FROM officials ORDER BY full_name");
            while ($o = $res->fetch_assoc()):
            ?>
            <div class="official-card">
                <h3><?= htmlspecialchars($o['full_name']) ?></h3>
                <p><?= htmlspecialchars($o['position']) ?></p>

                <div class="card-actions">
                    <button onclick="timeInOfficial(<?= $o['id'] ?>)">Time In</button>
                    <button class="danger" onclick="deleteOfficial(<?= $o['id'] ?>)">Delete</button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- ATTENDANCE TAB -->
    <div id="attendance-tab" class="tab-content">

        <div class="current-time-header">
            <h3>Attendance Records</h3>
            <div class="current-time-display">
                Current Time: <span id="attendanceCurrentTime"></span>
                <span class="real-time-indicator"></span>
            </div>
            <div class="debug-time" style="font-size: 12px; color: #666; margin-top: 5px;">
                Server Time (PHP): <span id="debugServerTime"><?= date('H:i:s') ?></span>
            </div>
        </div>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $q = "
                SELECT a.id, a.official_id, o.full_name, a.date, a.time_in, a.time_out, a.status
                FROM attendance a
                JOIN officials o ON o.id = a.official_id
                ORDER BY a.date DESC, a.time_in DESC
            ";
            $r = $conn->query($q);
            while ($row = $r->fetch_assoc()):
            ?>
                <tr>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= $row['date'] ?></td>
                    <td>
                        <?php if ($row['time_in']): ?>
                            <span class="time-display"><?= $row['time_in'] ?></span>
                        <?php else: ?>
                            <span class="time-empty">Not recorded</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['time_out']): ?>
                            <span class="time-display"><?= $row['time_out'] ?></span>
                        <?php else: ?>
                            <span class="time-empty">Not recorded</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $statusClass = 'status-' . strtolower(str_replace(' ', '-', $row['status']));
                        echo "<span class='{$statusClass}'>" . htmlspecialchars($row['status']) . "</span>";
                        ?>
                    </td>
                    <td>
                        <?php if (!$row['time_out']): ?>
                            <button class="time-out-btn" onclick="timeOutOfficial(<?= $row['official_id'] ?>)">Time Out</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

    </div>
</div>
</div>

<!-- ADD OFFICIAL MODAL -->
<div class="modal" id="addOfficialModal">
    <div class="modal-box">
        <div class="modal-header">
            <h2>Add Official</h2>
            <span class="close" onclick="closeModal('addOfficialModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addOfficialForm" method="post">
                <input type="hidden" name="add_official" value="1">
                <input type="text" name="full_name" placeholder="Full Name" required>
                <select id="officialPosition" name="position" required>
                    <option value="">Select Position...</option>
                </select>
                <input type="email" name="gmail" placeholder="Gmail" required>
                <input type="text" name="contact_number" placeholder="Contact Number" required>
                <div class="modal-actions">
                    <button type="submit" class="primary-btn">Save</button>
                    <button type="button" onclick="closeModal('addOfficialModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- TIME IN MODAL -->
<div class="modal" id="timeInModal">
    <div class="modal-box">
        <div class="modal-header">
            <h2>Confirm Time In</h2>
            <span class="close" onclick="closeModal('timeInModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="timeInForm" method="post">
                <input type="hidden" name="action" value="time_in">
                <input type="hidden" name="time_in" value="1">
                <input type="hidden" id="timeInOfficial" name="official_id">
                <div class="modal-actions">
                    <button type="submit" class="primary-btn">Confirm Time In</button>
                    <button type="button" onclick="closeModal('timeInModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Position limits data
let positionStats = [];

// Update date and time
function updateDateTime() {
    const now = new Date();
    const optionsDate = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const optionsTime = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
    
    if (document.getElementById('currentDate')) {
        document.getElementById('currentDate').textContent = now.toLocaleDateString('en-US', optionsDate);
    }
    if (document.getElementById('currentTime')) {
        document.getElementById('currentTime').textContent = now.toLocaleTimeString('en-US', optionsTime);
    }
    
    // Update attendance current time
    if (document.getElementById('attendanceCurrentTime')) {
        document.getElementById('attendanceCurrentTime').textContent = now.toLocaleTimeString('en-US', optionsTime);
    }
    
    // Update debug server time display
    if (document.getElementById('debugServerTime')) {
        document.getElementById('debugServerTime').textContent = now.toLocaleTimeString('en-US', optionsTime);
    }
}

// Sync with server time
function syncWithServerTime() {
    fetch('superadminAttendance.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=get_server_time'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Server Time:', data.server_time, 'Date:', data.server_date, 'Timezone:', data.timezone);
            
            // Update debug display with actual server time
            if (document.getElementById('debugServerTime')) {
                document.getElementById('debugServerTime').textContent = data.server_time;
            }
        }
    })
    .catch(error => console.error('Error syncing server time:', error));
}

// Load position limits
function loadPositionLimits() {
    fetch('superadminAttendance.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=get_position_stats'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            positionStats = data.stats;
            displayPositionLimits();
            updatePositionSelect();
        }
    })
    .catch(error => console.error('Error loading position limits:', error));
}

// Display position limits
function displayPositionLimits() {
    const grid = document.getElementById('positionLimitsGrid');
    grid.innerHTML = '';
    
    positionStats.forEach(stat => {
        const percentage = (stat.current / stat.limit) * 100;
        const card = document.createElement('div');
        card.className = `position-limit-card ${stat.is_full ? 'full' : ''}`;
        
        card.innerHTML = `
            <h4>${stat.position}</h4>
            <div class="limit-count">${stat.current}/${stat.limit}</div>
            <div class="limit-bar">
                <div class="limit-fill" style="width: ${Math.min(percentage, 100)}%"></div>
            </div>
            <div class="limit-text">${stat.is_full ? 'FULL' : `${stat.available} slots available`}</div>
        `;
        
        grid.appendChild(card);
    });
}

// Update position select options
function updatePositionSelect() {
    const select = document.getElementById('officialPosition');
    if (!select) return;
    
    // Clear existing options
    select.innerHTML = '<option value="">Select Position...</option>';
    
    // Add positions with availability info
    positionStats.forEach(stat => {
        if (!stat.is_full) {
            const option = document.createElement('option');
            option.value = stat.position;
            option.textContent = `${stat.position} (${stat.available} slots available)`;
            select.appendChild(option);
        }
    });
    
    // If no positions available, disable the form
    if (positionStats.every(stat => stat.is_full)) {
        select.innerHTML = '<option value="">All positions are full</option>';
        select.disabled = true;
        
        const submitBtn = document.querySelector('#addOfficialForm button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'All Positions Full';
        }
    }
}

// Initialize date/time and position limits
document.addEventListener('DOMContentLoaded', function() {
    updateDateTime();
    setInterval(updateDateTime, 1000);
    
    // Sync with server time on page load
    syncWithServerTime();
    
    // Sync server time every 30 seconds
    setInterval(syncWithServerTime, 30000);
    
    loadPositionLimits();
    
    // Handle add official form submission
    const addOfficialForm = document.getElementById('addOfficialForm');
    if (addOfficialForm) {
        addOfficialForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.textContent = 'Adding...';
            this.classList.add('loading');
            
            const formData = new FormData(this);
            
            fetch('superadminAttendance.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const successMsg = document.createElement('div');
                    successMsg.className = 'success-message';
                    successMsg.textContent = 'Official added successfully!';
                    this.insertBefore(successMsg, this.firstChild);
                    
                    // Reset form and close modal
                    this.reset();
                    setTimeout(() => {
                        closeModal('addOfficialModal');
                        successMsg.remove();
                    }, 1500);
                    
                    // Reload position limits
                    loadPositionLimits();
                    
                    // Reload the page to show the new official in the grid
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    // Show error message
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message';
                    errorMsg.textContent = 'Error: ' + data.message;
                    
                    // Remove any existing error messages
                    const existingError = this.querySelector('.error-message');
                    if (existingError) existingError.remove();
                    
                    this.insertBefore(errorMsg, this.firstChild);
                    
                    // Re-enable form
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    this.classList.remove('loading');
                    
                    // Remove error message after 5 seconds
                    setTimeout(() => {
                        errorMsg.remove();
                    }, 5000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                const errorMsg = document.createElement('div');
                errorMsg.className = 'error-message';
                errorMsg.textContent = 'An error occurred while adding official';
                
                this.insertBefore(errorMsg, this.firstChild);
                
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                this.classList.remove('loading');
                
                setTimeout(() => {
                    errorMsg.remove();
                }, 5000);
            });
        });
    }
    
    // Handle time-in form submission
    const timeInForm = document.getElementById('timeInForm');
    if (timeInForm) {
        timeInForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.textContent = 'Recording...';
            this.classList.add('loading');
            
            const formData = new FormData(this);
            
            fetch('superadminAttendance.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Check if response is actually JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        throw new Error('Server returned non-JSON response: ' + text.substring(0, 200));
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show success message
                    const successMsg = document.createElement('div');
                    successMsg.className = 'success-message';
                    successMsg.textContent = 'Time in recorded successfully!';
                    this.insertBefore(successMsg, this.firstChild);
                    
                    // Reset form and close modal
                    this.reset();
                    setTimeout(() => {
                        closeModal('timeInModal');
                        successMsg.remove();
                    }, 1500);
                    
                    // Reload the page to show the attendance record
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    // Show error message
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message';
                    errorMsg.textContent = 'Error: ' + data.message;
                    
                    // Remove any existing error messages
                    const existingError = this.querySelector('.error-message');
                    if (existingError) existingError.remove();
                    
                    this.insertBefore(errorMsg, this.firstChild);
                    
                    // Re-enable form
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    this.classList.remove('loading');
                    
                    // Remove error message after 5 seconds
                    setTimeout(() => {
                        errorMsg.remove();
                    }, 5000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                const errorMsg = document.createElement('div');
                errorMsg.className = 'error-message';
                errorMsg.textContent = 'An error occurred while recording time in: ' + error.message;
                
                this.insertBefore(errorMsg, this.firstChild);
                
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                this.classList.remove('loading');
                
                setTimeout(() => {
                    errorMsg.remove();
                }, 5000);
            });
        });
    }
});

function switchTab(tab, btn){
    document.querySelectorAll('.tab-content').forEach(t=>t.classList.remove('active'));
    document.querySelectorAll('.tab-button').forEach(b=>b.classList.remove('active'));
    document.getElementById(tab+'-tab').classList.add('active');
    btn.classList.add('active');
    
    // Reload position limits when switching to officials tab
    if (tab === 'officials') {
        loadPositionLimits();
    }
}

function showModal(id){ 
    document.getElementById(id).classList.add('show');
    
    // Reload position limits when opening add official modal
    if (id === 'addOfficialModal') {
        loadPositionLimits();
    }
}

function timeInOfficial(officialId) {
    document.getElementById('timeInOfficial').value = officialId;
    showModal('timeInModal');
}

function closeModal(id){ document.getElementById(id).classList.remove('show'); }

function deleteOfficial(id){
    if(!confirm('Delete official and all attendance records? This action cannot be undone.')) return;
    
    fetch('superadminAttendance.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`action=delete_official&id=${id}`
    }).then(r=>r.json()).then(d=>{
        if(d.success) {
            alert('Official deleted successfully!');
            
            // Reload position limits to reflect the freed slot
            loadPositionLimits();
            
            // Reload the page to update the officials grid
            setTimeout(() => {
                location.reload();
            }, 300);
        }
        else {
            alert('Failed to delete official. Please try again.');
        }
    }).catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting official');
    });
}

function timeOutOfficial(attendanceId) {
    if(!confirm('Confirm time out for this official?')) return;
    
    const btn = event.target;
    const originalText = btn.textContent;
    
    // Show loading state
    btn.disabled = true;
    btn.textContent = 'Recording...';
    btn.classList.add('loading');
    
    fetch('superadminAttendance.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=time_out&official_id=${attendanceId}`
    })
    .then(response => {
        // Check if response is actually JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                throw new Error('Server returned non-JSON response: ' + text.substring(0, 200));
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success message
            btn.textContent = 'Success!';
            btn.classList.add('success');
            btn.classList.remove('loading');
            
            // Reload the page to show the updated attendance record
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            // Show error message
            alert('Error: ' + data.message);
            btn.disabled = false;
            btn.textContent = originalText;
            btn.classList.remove('loading');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while recording time out: ' + error.message);
        btn.disabled = false;
        btn.textContent = originalText;
        btn.classList.remove('loading');
    });
}

function logout() {
    location.href = 'superadminLogout.php';
}
</script>


<?php
$conn->close();
ob_end_flush();
?>
</body>
</html>
