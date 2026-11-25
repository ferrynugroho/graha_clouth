<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$admin_id = $_SESSION['user_id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'get_messages') {
    $user_id = isset($_GET['user']) ? (int)$_GET['user'] : 0;
    
    if ($user_id <= 0) {
        echo json_encode([]);
        exit;
    }
    
    $stmt = $conn->prepare("
        SELECT id, sender_id, receiver_id, message, created_at,
        CASE WHEN sender_id IN (SELECT id FROM users WHERE role = 'admin') THEN 'admin' ELSE 'customer' END as sender_type
        FROM messages 
        WHERE sender_id = :uid OR receiver_id = :uid
        ORDER BY created_at ASC
    ");
    $stmt->execute([':uid' => $user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($messages as &$m) {
        $m['time_formatted'] = date('H:i', strtotime($m['created_at']));
    }
    
    // Mark as read
    $upd = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = :uid AND is_read = 0");
    $upd->execute([':uid' => $user_id]);
    
    echo json_encode($messages);
    exit;
} elseif ($action === 'send_message') {
    $user_id = isset($_POST['user']) ? (int)$_POST['user'] : 0;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    if ($user_id > 0 && $message !== '') {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, is_read) VALUES (:sid, :rid, :msg, 0)");
        $success = $stmt->execute([
            ':sid' => $admin_id,
            ':rid' => $user_id,
            ':msg' => $message
        ]);
        
        if ($success) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database execution failed']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid user or empty message']);
    }
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
    exit;
}
