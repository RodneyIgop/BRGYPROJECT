<?php
session_start();
if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminLogin.php');
    exit;
}
require_once '../Connection/conn.php';

/* ===============================
   AJAX API
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'delete' && isset($_GET['resident_id'])) {
        $stmt = $conn->prepare("DELETE FROM residents WHERE resident_id=?");
        $stmt->bind_param("i", $_GET['resident_id']);
        if ($stmt->execute()) {
            echo "<script>alert('Resident deleted successfully!'); window.location.href='superadminResidents.php';</script>";
        } else {
            echo "<script>alert('Failed to delete resident'); window.location.href='superadminResidents.php';</script>";
        }
        exit;
    }
    
    if ($_GET['action'] === 'view' && isset($_GET['resident_id'])) {
        $stmt = $conn->prepare("SELECT * FROM residents WHERE resident_id=?");
        $stmt->bind_param("i", $_GET['resident_id']);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res) {
            $view_resident = $res;
        } else {
            echo "<script>alert('Resident not found'); window.location.href='superadminResidents.php';</script>";
            exit;
        }
    }
    
    if ($_GET['action'] === 'edit' && isset($_GET['resident_id'])) {
        $stmt = $conn->prepare("SELECT * FROM residents WHERE resident_id=?");
        $stmt->bind_param("i", $_GET['resident_id']);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res) {
            $edit_resident = $res;
        } else {
            echo "<script>alert('Resident not found'); window.location.href='superadminResidents.php';</script>";
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    if ($_GET['action'] === 'add') {
        $stmt = $conn->prepare("
            INSERT INTO residents
            (full_name, date_of_birth, sex, civil_status, address, contact_number)
            VALUES (?,?,?,?,?,?)
        ");
        $stmt->bind_param(
            "ssssss",
            $_POST['fullName'],
            $_POST['dateOfBirth'],
            $_POST['sex'],
            $_POST['civilStatus'],
            $_POST['address'],
            $_POST['contactNumber']
        );
        if ($stmt->execute()) {
            echo "<script>alert('Resident added successfully!'); window.location.href='superadminResidents.php';</script>";
        } else {
            echo "<script>alert('Failed to add resident'); window.location.href='superadminResidents.php';</script>";
        }
        exit;
    }
    
    if ($_GET['action'] === 'edit') {
        $stmt = $conn->prepare("
            UPDATE residents SET
            full_name=?, date_of_birth=?, sex=?, civil_status=?, address=?, contact_number=?
            WHERE resident_id=?
        ");
        $stmt->bind_param(
            "ssssssi",
            $_POST['fullName'],
            $_POST['dateOfBirth'],
            $_POST['sex'],
            $_POST['civilStatus'],
            $_POST['address'],
            $_POST['contactNumber'],
            $_POST['residentId']
        );
        if ($stmt->execute()) {
            echo "<script>alert('Resident updated successfully!'); window.location.href='superadminResidents.php';</script>";
        } else {
            echo "<script>alert('Failed to update resident'); window.location.href='superadminResidents.php';</script>";
        }
        exit;
    }
}

/* ===============================
   PAGE DATA
================================ */
$residents = [];
$columns = [];
$r = $conn->query("SELECT * FROM residents ORDER BY resident_id DESC");
if ($r && $r->num_rows) {
    $residents = $r->fetch_all(MYSQLI_ASSOC);
    $columns = array_keys($residents[0]);
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Superadmin | Resident Information</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        /* RESET */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f5f5f5;
        }

        /* ================= SIDEBAR ================= */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background-color: #014A7F;
            padding: 20px;
            overflow-y: auto;
            scrollbar-gutter: stable;
            scrollbar-width: none;
            z-index: 100;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .sidebar-logo img {
            width: 50px;
            height: 50px;
        }

        .sidebar-logo h2 {
            color: #fff;
            font-size: 14px;
            line-height: 1.2;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .sidebar-nav a,
        .sidebar-nav button,
        .sidebar-dropdown summary {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 8px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.2s ease;
        }

        .sidebar-nav a:hover,
        .sidebar-nav button:hover,
        .sidebar-dropdown summary:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar-nav a.active,
        .sidebar-dropdown[open] summary {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .sidebar-nav button {
            margin-top: auto;
        }

        /* ================= DROPDOWN ================= */
        .sidebar-dropdown {
            display: flex;
            flex-direction: column;
        }

        .sidebar-dropdown summary {
            list-style: none;
        }

        .sidebar-dropdown summary::-webkit-details-marker {
            display: none;
        }

        .submenu-link {
            padding: 8px 16px 8px 48px;
            font-size: 15px;
            border-radius: 6px;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .submenu-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Sidebar Icons */
        .sidebar-nav img,
        .sidebar-dropdown summary img,
        .submenu-link img {
            width: 1em;
            height: 1em;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        /* Arrow rotation */
        .sidebar-dropdown summary img:last-child {
            margin-left: auto;
            transition: transform 0.2s;
        }

        .sidebar-dropdown[open] summary img:last-child {
            transform: rotate(180deg);
        }

        /* Heavier look for specific status icons */
        .submenu-link img[src*="addAdmin"],
        .submenu-link img[src*="addUser"],
        .submenu-link img[src*="pending"] {
            width: 2em;
            height: 1.4em;
            filter: brightness(0) invert(1);
        }

        /* ================= MAIN CONTENT ================= */
        .main-content {
            margin-left: 240px;
            padding: 20px;
        }

        /* ================= HEADER ================= */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 32px;
            color: #333;
        }

        .header-date {
            background: #fff;
            border: 1px solid #d9d9d9;
            border-radius: 4px;
            padding: 8px 16px;
            font-size: 14px;
            text-align: right;
        }

        /* Content styles */
        button {
            cursor: pointer
        }

        .btn-add {
            background: #00386b;
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 4px;
            font-weight: bold
        }

        .btn-add:hover {
            background: #002855
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px
        }

        thead {
            background: #00386b;
            color: #fff
        }

        th,
        td {
            padding: 10px;
            font-size: 13px;
            border-bottom: 1px solid #ddd
        }

        tr:hover td {
            background: #eef3ff
        }

        .action-buttons {
            display: flex;
            gap: 6px
        }

        .action-buttons button {
            border: none;
            border-radius: 6px;
            padding: 6px 8px;
            color: #fff
        }

        .btn-view {
            background: #17a2b8
        }

        .btn-edit {
            background: #28a745
        }

        .btn-delete {
            background: #dc3545
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #00386b;
            box-shadow: 0 0 5px rgba(0, 56, 107, 0.3)
        }

        /* .modal{display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5)}
.modal-content{background-color:#fff;margin:5% auto;padding:0;border-radius:8px;width:90%;max-width:600px;box-shadow:0 4px 20px rgba(0,0,0,0.3)}
.modal-header{display:flex;justify-content:space-between;align-items:center;padding:20px 25px;background-color:#00386b;color:white;border-radius:8px 8px 0 0}
.modal-header h2{margin:0;font-size:1.5em}
.close{color:white;font-size:28px;font-weight:bold;cursor:pointer;line-height:1}
.close:hover{opacity:0.7}
.modal-body{padding:25px}
.form-group{margin-bottom:20px}
.form-group label{display:block;margin-bottom:5px;font-weight:bold;color:#333}
.form-buttons{display:flex;justify-content:flex-end;gap:10px;margin-top:25px;padding-top:20px;border-top:1px solid #eee}
.btn-save{background-color:#00386b;color:white;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;font-size:14px;font-weight:bold;transition:background-color 0.3s}
.btn-save:hover{background-color:#002855}
.btn-cancel{background-color:#6c757d;color:white;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;font-size:14px;font-weight:bold;transition:background-color 0.3s}
.btn-cancel:hover{background-color:#5a6268} */
        /* ================= MODAL BACKDROP ================= */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 1000;
            overflow-y: auto;
        }

        /* ================= MODAL CONTAINER ================= */
        .modal-content {
            background: #ffffff;
            margin: 60px auto;
            border-radius: 10px;
            width: 95%;
            max-width: 620px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            animation: modalFade 0.25s ease-out;
        }

        /* ================= HEADER ================= */
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #00386b;
            color: #ffffff;
            padding: 16px 22px;
            border-radius: 10px 10px 0 0;
        }

        .modal-header h3 {
            font-size: 20px;
            font-weight: 600;
        }

        .modal-header span {
            font-size: 28px;
            cursor: pointer;
            line-height: 1;
        }

        .modal-header span:hover {
            opacity: 0.8;
        }

        /* ================= BODY ================= */
        .modal-body {
            padding: 22px;
        }

        /* ================= FORM LAYOUT ================= */
        .modal-body form {
            display: grid;
            gap: 14px;
        }

        /* Inputs */
        .modal-body input,
        .modal-body select,
        .modal-body textarea {
            width: 100%;
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            font-size: 14px;
        }

        .modal-body textarea {
            resize: vertical;
            min-height: 80px;
        }

        .modal-body input:focus,
        .modal-body select:focus,
        .modal-body textarea:focus {
            outline: none;
            border-color: #00386b;
            box-shadow: 0 0 0 2px rgba(0, 56, 107, 0.15);
        }

        /* ================= VIEW MODAL TEXT ================= */
        #viewBody p {
            margin-bottom: 10px;
            font-size: 14px;
            color: #333;
        }

        #viewBody strong {
            text-transform: capitalize;
            color: #00386b;
        }

        /* ================= FOOTER BUTTONS ================= */
        .form-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }

        /* Buttons */
        .btn-save {
            background: #00386b;
            color: #ffffff;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-save:hover {
            background: #002855;
        }

        .btn-cancel {
            background: #6c757d;
            color: #ffffff;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-cancel:hover {
            background: #5a6268;
        }

        #searchResidents {
            width: 280px;
            padding: 8px 12px;
            font-size: 14px;
        }

        /* ================= ANIMATION ================= */
        @keyframes modalFade {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <?php include 'superadminSidebar.php' ;?>

    <div class="main-content">
        <div class="header">
            <h1>Resident Information</h1>

            <div class="header-date">
                <div class="date" id="currentDate"></div>
                <div class="time" id="currentTime"></div>
            </div>
        </div>

        <button class="btn-add" onclick="document.getElementById('addModal').style.display='block'">Add Resident</button>

        <input type="text" id="searchResidents" placeholder="Search..." style="margin:15px 0 ">

        <table>
            <thead>
                <tr>
                    <?php foreach ($columns as $c):
                        if ($c !== 'resident_id'): ?>
                            <th><?= ucwords(str_replace('_', ' ', $c)) ?></th>
                        <?php endif; endforeach; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($residents as $r): ?>
                    <tr>
                        <?php foreach ($columns as $c):
                            if ($c !== 'resident_id'): ?>
                                <td><?= htmlspecialchars($r[$c]) ?></td>
                            <?php endif; endforeach; ?>
                        <td class="action-buttons">
                            <button class="btn-view" onclick="window.location.href='superadminResidents.php?action=view&resident_id=<?= $r['resident_id'] ?>'"><i
                                    class="bi bi-eye"></i></button>
                            <button class="btn-edit" onclick="window.location.href='superadminResidents.php?action=edit&resident_id=<?= $r['resident_id'] ?>'"><i
                                    class="bi bi-pencil"></i></button>
                            <button class="btn-delete" onclick="if(confirm('Delete resident?')) window.location.href='superadminResidents.php?action=delete&resident_id=<?= $r['resident_id'] ?>'"><i
                                    class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ADD -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Resident</h3><span onclick="document.getElementById('addModal').style.display='none'">&times;</span>
            </div>
            <div class="modal-body">
                <form action="superadminResidents.php?action=add" method="post">
                    <input name="fullName" placeholder="Full Name" required>
                    <input type="date" name="dateOfBirth" required>
                    <select name="sex">
                        <option>Male</option>
                        <option>Female</option>
                    </select>
                    <select name="civilStatus">
                        <option>Single</option>
                        <option>Married</option>
                    </select>
                    <textarea name="address" placeholder="Address"></textarea>
                    <input name="contactNumber" placeholder="Contact">
                    <div class="form-buttons">
                        <button type="button" class="btn-cancel" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
                        <button type="submit" class="btn-save">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Resident</h3><span onclick="document.getElementById('editModal').style.display='none'">&times;</span>
            </div>
            <div class="modal-body">
                <form id="editForm" action="superadminResidents.php?action=edit" method="post">
                    <input type="hidden" name="residentId" value="<?= $edit_resident['resident_id'] ?? '' ?>">
                    <input name="fullName" value="<?= $edit_resident['full_name'] ?? '' ?>">
                    <input type="date" name="dateOfBirth" value="<?= $edit_resident['date_of_birth'] ?? '' ?>">
                    <select name="sex">
                        <option value="Male" <?= ($edit_resident['sex'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= ($edit_resident['sex'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                    <select name="civilStatus">
                        <option value="Single" <?= ($edit_resident['civil_status'] ?? '') === 'Single' ? 'selected' : '' ?>>Single</option>
                        <option value="Married" <?= ($edit_resident['civil_status'] ?? '') === 'Married' ? 'selected' : '' ?>>Married</option>
                    </select>
                    <textarea name="address"><?= $edit_resident['address'] ?? '' ?></textarea>
                    <input name="contactNumber" value="<?= $edit_resident['contact_number'] ?? '' ?>">
                    <div class="form-buttons">
                        <button type="button" class="btn-cancel" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
                        <button type="submit" class="btn-save">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- VIEW -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Resident Details</h3><span onclick="document.getElementById('viewModal').style.display='none'">&times;</span>
            </div>
            <div class="modal-body" id="viewBody">
                <?php if (isset($view_resident)): ?>
                    <?php foreach ($view_resident as $key => $value): ?>
                        <?php if ($key !== 'resident_id'): ?>
                            <p><strong><?= ucwords(str_replace('_', ' ', $key)) ?>:</strong> <?= htmlspecialchars($value) ?></p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (isset($edit_resident)): ?>
        <script>document.getElementById('editModal').style.display='block';</script>
    <?php endif; ?>
    
    <?php if (isset($view_resident)): ?>
        <script>document.getElementById('viewModal').style.display='block';</script>
    <?php endif; ?>

    <script>
        /* ================= DATE & TIME ================= */
        function updateDateTime() {
            const now = new Date();

            const optionsDate = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };

            const optionsTime = {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            };

            document.getElementById('currentDate').textContent =
                now.toLocaleDateString('en-US', optionsDate);

            document.getElementById('currentTime').textContent =
                now.toLocaleTimeString('en-US', optionsTime);
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);

        /* ================= SEARCH ================= */
        document.getElementById('searchResidents').addEventListener('input', function () {
            const value = this.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(value)
                    ? ''
                    : 'none';
            });
        });
    </script>

</body>

</html>