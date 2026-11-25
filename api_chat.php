<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get admin ID from DB dynamically
$stmt_admin = $conn->prepare("SELECT id FROM users WHERE role = 'admin' ORDER BY id ASC LIMIT 1");
$stmt_admin->execute();
$admin = $stmt_admin->fetch(PDO::FETCH_ASSOC);
$admin_id = $admin ? $admin['id'] : 1; // fallback to 1

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'get_messages') {
    $stmt = $conn->prepare("
        SELECT id, sender_id, message, created_at,
        CASE WHEN sender_id = ? THEN 'customer' ELSE 'admin' END as sender_type
        FROM messages 
        WHERE sender_id = ? OR receiver_id = ?
        ORDER BY created_at ASC
    ");
    $stmt->execute([$user_id, $user_id, $user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($messages as &$m) {
        $m['time_formatted'] = date('H:i', strtotime($m['created_at']));
    }
    
    // Mark as read
    $upd = $conn->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND is_read = 0");
    $upd->execute([$user_id]);
    
    echo json_encode($messages);
    exit;
} elseif ($action === 'send_message') {
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    if ($message !== '') {
        // Auto-convert legacy [TANYA PRODUK: ...] format to rich [PRODUK_INQUIRY:...]
        if (preg_match('/\[TANYA PRODUK:\s*([^\]]+)\]\s*([\s\S]*)/i', $message, $m)) {
            $titleAndPrice = trim($m[1]);
            $userText = trim($m[2]);
            $title = $titleAndPrice;
            $price = '';
            if (preg_match('/^(.*?)\s*\((Rp[\s\S]*?)\)$/i', $titleAndPrice, $pm)) {
                $title = trim($pm[1]);
                $price = trim($pm[2]);
            }
            $pStmt = $conn->prepare("SELECT id, nama_produk, harga, gambar FROM products WHERE nama_produk LIKE ? LIMIT 1");
            $pStmt->execute(["%" . $title . "%"]);
            $prod = $pStmt->fetch(PDO::FETCH_ASSOC);
            if ($prod) {
                $pId = $prod['id'];
                $pNama = $prod['nama_produk'];
                $pHarga = $price ?: ('Rp ' . number_format($prod['harga'], 0, ',', '.'));
                $pGambar = $prod['gambar'];
                $message = "[PRODUK_INQUIRY:id={$pId}|nama={$pNama}|harga={$pHarga}|gambar={$pGambar}] {$userText}";
            }
        }

        // Cari admin terakhir yang chat dengan user ini
        $stmt_last = $conn->prepare("SELECT sender_id FROM messages WHERE receiver_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt_last->execute([$user_id]);
        $last = $stmt_last->fetch(PDO::FETCH_ASSOC);
        $target_admin = $last ? $last['sender_id'] : $admin_id; // gunakan admin terakhir atau default
        
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $target_admin, $message]);
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
} elseif ($action === 'check_unread') {
    $stmt = $conn->prepare("SELECT COUNT(id) as unread FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['unread' => (int)$res['unread']]);
    exit;
}
