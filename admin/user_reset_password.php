<?php
// admin/user_reset_password.php
session_start();
require_once '../config/db.php';

// I-check kung Admin at kung may ID
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1 || !isset($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$db = getDbConnection();
$user_id = $_GET['id'];

// Huwag hayaang i-reset ng user ang sarili niyang password gamit ang Admin tool
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['admin_message'] = '<div class="alert alert-danger">You cannot reset your own password via this tool. Please use the Profile page.</div>';
    header('Location: user_edit.php?id=' . $user_id);
    exit;
}

// 1. I-define ang Temporary Password at i-hash ito
$new_temp_password = 'password123'; 
$hashed_password = password_hash($new_temp_password, PASSWORD_DEFAULT);

try {
    // 2. I-update ang password sa database
    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    if ($stmt->execute([$hashed_password, $user_id])) {
        // Success Message
        $_SESSION['admin_message'] = '
            <div class="alert alert-success">
                Password for User ID ' . $user_id . ' has been successfully reset. 
                The new temporary password is: <strong>' . $new_temp_password . '</strong>.
                Please inform the user to change it immediately.
            </div>
        ';
    } else {
        // Failure Message
        $_SESSION['admin_message'] = '<div class="alert alert-danger">Failed to reset password.</div>';
    }
} catch (PDOException $e) {
    $_SESSION['admin_message'] = '<div class="alert alert-danger">Database Error: ' . $e->getMessage() . '</div>';
}

// 3. I-redirect pabalik sa Edit User page
header('Location: user_edit.php?id=' . $user_id);
exit;