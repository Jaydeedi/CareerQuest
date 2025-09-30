<?php
// admin/user_delete.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1 || !isset($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$db = getDbConnection();
$user_id = $_GET['id'];

// Huwag hayaang i-delete ng user ang sarili niyang account
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['admin_message'] = '<div class="alert alert-danger">You cannot delete your own account.</div>';
    header('Location: users.php');
    exit;
}

try {
    // MAHALAGA: Tiyakin na ang foreign key sa user_progress ay may ON DELETE CASCADE.
    // I-delete ang User
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$user_id])) {
        $_SESSION['admin_message'] = '<div class="alert alert-success">User ID ' . $user_id . ' and all progress data deleted successfully!</div>';
    } else {
        $_SESSION['admin_message'] = '<div class="alert alert-danger">Failed to delete user.</div>';
    }
} catch (PDOException $e) {
    $_SESSION['admin_message'] = '<div class="alert alert-danger">Error deleting user: ' . $e->getMessage() . '</div>';
}

// I-redirect pabalik sa listahan ng users
header('Location: users.php');
exit;