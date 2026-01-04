<?php
session_start();

// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminLogin.php');
    exit;
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// Fix connection path
require_once __DIR__ . '/../Connection/conn.php';

// Handle message insertion from public form (if needed)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contactnumberRaw = trim($_POST['contactnumber'] ?? '');
    $message = trim($_POST['message'] ?? '');

    $digitsOnly = preg_replace('/\D+/', '', $contactnumberRaw);
    if ($fullname === '' || $message === '' || $digitsOnly === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: landingpage.php?msg=error#getintouch');
        exit;
    }

    $contactnumber = (int)$digitsOnly;

    $dateColumnName = null;
    $dateColumnType = null;
    $columnsResult = $conn->query('SHOW COLUMNS FROM messages');
    if ($columnsResult instanceof mysqli_result) {
        while ($col = $columnsResult->fetch_assoc()) {
            $field = (string)($col['Field'] ?? '');
            $type = strtolower((string)($col['Type'] ?? ''));
            if ($field === '') {
                continue;
            }
            $looksLikeDateType = (strpos($type, 'date') !== false) || (strpos($type, 'timestamp') !== false);
            $lname = strtolower($field);
            $looksLikeDateName = in_array($lname, ['date', 'datesent', 'date_sent', 'sentdate', 'sent_date', 'createdat', 'created_at', 'createdon', 'created_on', 'timestamp'], true)
                || (strpos($lname, 'date') !== false)
                || (strpos($lname, 'time') !== false);
            if ($looksLikeDateType && $looksLikeDateName) {
                $dateColumnName = $field;
                $dateColumnType = $type;
                break;
            }
        }
        $columnsResult->free();
    }

    $stmt = null;
    if ($dateColumnName && preg_match('/^[A-Za-z0-9_]+$/', $dateColumnName)) {
        $sql = 'INSERT INTO messages (Fullname, email, contactnumber, message, `' . $dateColumnName . '`) VALUES (?, ?, ?, ?, ?)';
        $nowValue = date('Y-m-d H:i:s'); // Always use full datetime for real-time timestamp
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('ssiss', $fullname, $email, $contactnumber, $message, $nowValue);
        }
    } else {
        $stmt = $conn->prepare('INSERT INTO messages (Fullname, email, contactnumber, message) VALUES (?, ?, ?, ?)');
        if ($stmt) {
            $stmt->bind_param('ssis', $fullname, $email, $contactnumber, $message);
        }
    }

    if (!$stmt) {
        header('Location: landingpage.php?msg=error#getintouch');
        exit;
    }

    if ($stmt->execute()) {
        $stmt->close();
        header('Location: landingpage.php?msg=sent#getintouch');
        exit;
    }

    $stmt->close();
    header('Location: landingpage.php?msg=error#getintouch');
    exit;
}

// Fetch messages for display
$messages = [];
$fields = [];
$orderClause = '';

// Determine ordering column
if ($resCols = $conn->query('SHOW COLUMNS FROM messages')) {
    $idCol = null;
    $dateCol = null;
    while ($col = $resCols->fetch_assoc()) {
        $field = (string)($col['Field'] ?? '');
        $lname = strtolower($field);
        if ($lname === 'id' || $lname === 'messageid' || $lname === 'message_id') {
            $idCol = $field;
        }
        if ($dateCol === null && ($lname === 'date' || $lname === 'created_at' || $lname === 'createdon' || $lname === 'created_on' || $lname === 'datesent' || $lname === 'date_sent' || strpos($lname, 'date') !== false || strpos($lname, 'time') !== false || $lname === 'timestamp')) {
            $dateCol = $field;
        }
    }
    $resCols->free();
    if ($dateCol) {
        $orderClause = ' ORDER BY `'.$conn->real_escape_string($dateCol).'` DESC';
    } elseif ($idCol) {
        $orderClause = ' ORDER BY `'.$conn->real_escape_string($idCol).'` DESC';
    }
}

// Fetch messages
if ($res = $conn->query('SELECT * FROM messages' . $orderClause)) {
    $fieldsMeta = $res->fetch_fields();
    foreach ($fieldsMeta as $f) { $fields[] = $f->name; }
    while ($row = $res->fetch_assoc()) { $messages[] = $row; }
    $res->free();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin Dashboard</title>
    <link rel="stylesheet" href="adminSidebar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .messages-container {
            background: #fff;
            border-radius: 4px;
            padding: 24px;
            border: 1px solid #d9d9d9;
        }
        
        .messages-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .messages-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        
        .messages-table th,
        .messages-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .messages-table th {
            background: #f5f5f5;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        
        .messages-table tr:hover {
            background: #fafafa;
        }
        
        .message-content {
            max-width: 300px;
            word-wrap: break-word;
            white-space: pre-wrap;
        }
        
        .no-messages {
            text-align: center;
            color: #666;
            padding: 40px;
        }
        
        .table-wrapper {
            overflow-x: auto;
            max-height: 70vh;
            border: 1px solid #eee;
        }
        
        .message-meta {
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <?php include 'adminSidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <h1>Messages</h1>
            <div class="header-date" id="dateTimeCard">
                <div class="date" id="currentDate"><?php echo date('l, F j, Y'); ?></div>
                <div class="time" id="currentTime"><?php echo date('g:i A'); ?></div>
            </div>
        </div>

        <div class="messages-container">
            <div class="messages-header">
                <h2>All Messages</h2>
                <span>Total: <?php echo count($messages); ?></span>
            </div>
            
            <?php if (!empty($messages) && !empty($fields)): ?>
                <div class="table-wrapper">
                    <table class="messages-table">
                        <thead>
                            <tr>
                                <?php 
                                // Display common columns with friendly headers
                                foreach ($fields as $col): 
                                    $header = $col;
                                    $lname = strtolower($col);
                                    if ($lname === 'fullname') $header = 'Name';
                                    elseif ($lname === 'email') $header = 'Email';
                                    elseif ($lname === 'contactnumber') $header = 'Contact';
                                    elseif ($lname === 'message') $header = 'Message';
                                    elseif (strpos($lname, 'date') !== false || strpos($lname, 'time') !== false || $lname === 'timestamp') $header = 'Date';
                                    elseif ($lname === 'id' || strpos($lname, 'id') !== false) $header = 'ID';
                                ?>
                                    <th><?php echo htmlspecialchars($header); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $row): ?>
                                <tr>
                                    <?php foreach ($fields as $col): ?>
                                        <td>
                                            <?php 
                                            $value = (string)($row[$col] ?? '');
                                            $lname = strtolower($col);
                                            
                                            // Special formatting for certain columns
                                            if ($lname === 'message') {
                                                echo '<div class="message-content">' . htmlspecialchars($value) . '</div>';
                                            } elseif (strpos($lname, 'date') !== false || strpos($lname, 'time') !== false || $lname === 'timestamp') {
                                                if (!empty($value)) {
                                                    $date = new DateTime($value);
                                                    echo '<div class="message-meta">' . $date->format('M j, Y g:i A') . '</div>';
                                                } else {
                                                    echo '-';
                                                }
                                            } elseif ($lname === 'contactnumber') {
                                                echo htmlspecialchars($value);
                                            } else {
                                                echo htmlspecialchars($value);
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-messages">
                    <i class="bi bi-envelope" style="font-size: 48px; color: #ddd;"></i>
                    <p>No messages found.</p>
                    <p style="font-size: 14px; color: #999;">Messages from the contact form will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="adminIndex.js"></script>
</body>
</html>
