<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$admin_id = $_SESSION['user_id'];
$action = isset($_GET['action']) ? $_GET['action'] : 'get_summary';

try {
    if ($action === 'get_summary') {
        // 1. New Orders Count
        $stmt_ord = $conn->query("SELECT COUNT(id) as total FROM orders WHERE status = 'Pesanan Baru'");
        $new_orders_count = (int)$stmt_ord->fetchColumn();

        // 2. Unread Admin Messages Count
        $stmt_msg = $conn->prepare("SELECT COUNT(id) as total FROM messages WHERE receiver_id = ? AND is_read = 0");
        $stmt_msg->execute([$admin_id]);
        $unread_chats_count = (int)$stmt_msg->fetchColumn();

        // 3. Active Abandoned Carts Count
        $stmt_cart = $conn->query("SELECT COUNT(DISTINCT user_id) as total FROM carts");
        $active_carts_count = (int)$stmt_cart->fetchColumn();

        // 4. Latest Order for live trigger
        $stmt_last_ord = $conn->query("
            SELECT id, nomor_order, nama_pelanggan, total, created_at 
            FROM orders 
            ORDER BY id DESC 
            LIMIT 1
        ");
        $latest_order = $stmt_last_ord->fetch(PDO::FETCH_ASSOC);

        // 5. Latest Unread Chat Message for live trigger
        $stmt_last_msg = $conn->prepare("
            SELECT m.id, m.sender_id, m.message, m.created_at, u.nama as sender_name
            FROM messages m
            LEFT JOIN users u ON m.sender_id = u.id
            WHERE m.receiver_id = ? AND m.is_read = 0
            ORDER BY m.id DESC 
            LIMIT 1
        ");
        $stmt_last_msg->execute([$admin_id]);
        $latest_chat = $stmt_last_msg->fetch(PDO::FETCH_ASSOC);

        if ($latest_chat) {
            // Clean up inquiry tags in preview
            $clean_text = preg_replace('/\[PRODUK_INQUIRY:.*?\]|\[TANYA PRODUK:.*?\]/i', '🏷️ [Tanya Produk] ', $latest_chat['message']);
            $latest_chat['message_preview'] = mb_strimwidth($clean_text, 0, 45, '...');
        }

        // 6. Recent Notifications Stream (Combined 8 latest events)
        $events = [];

        // Orders
        $stmt_recent_ord = $conn->query("
            SELECT id, nomor_order, nama_pelanggan, total, created_at, status 
            FROM orders 
            ORDER BY id DESC 
            LIMIT 5
        ");
        while ($ord = $stmt_recent_ord->fetch(PDO::FETCH_ASSOC)) {
            $events[] = [
                'type' => 'order',
                'id' => $ord['id'],
                'title' => 'Pesanan Baru ' . $ord['nomor_order'],
                'desc' => $ord['nama_pelanggan'] . ' • Rp ' . number_format($ord['total'], 0, ',', '.'),
                'link' => 'pesanan.php?id=' . $ord['id'],
                'icon' => 'fas fa-shopping-bag',
                'color' => '#2563EB',
                'bg' => '#EFF6FF',
                'time' => date('H:i', strtotime($ord['created_at'])),
                'created_at' => $ord['created_at']
            ];
        }

        // Unread Chats
        $stmt_recent_chat = $conn->prepare("
            SELECT m.id, m.sender_id, m.message, m.created_at, u.nama as sender_name
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.receiver_id = ? AND m.is_read = 0
            ORDER BY m.id DESC 
            LIMIT 4
        ");
        $stmt_recent_chat->execute([$admin_id]);
        while ($c = $stmt_recent_chat->fetch(PDO::FETCH_ASSOC)) {
            $clean_c = preg_replace('/\[PRODUK_INQUIRY:.*?\]|\[TANYA PRODUK:.*?\]/i', '🏷️ [Tanya Produk] ', $c['message']);
            $events[] = [
                'type' => 'chat',
                'id' => $c['id'],
                'title' => 'Chat dari ' . $c['sender_name'],
                'desc' => mb_strimwidth($clean_c, 0, 40, '...'),
                'link' => 'chat.php?user=' . $c['sender_id'],
                'icon' => 'fas fa-comment-dots',
                'color' => '#10B981',
                'bg' => '#ECFDF5',
                'time' => date('H:i', strtotime($c['created_at'])),
                'created_at' => $c['created_at']
            ];
        }

        // Sort events descending by created_at
        usort($events, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        echo json_encode([
            'status' => 'success',
            'new_orders_count' => $new_orders_count,
            'unread_chats_count' => $unread_chats_count,
            'active_carts_count' => $active_carts_count,
            'total_notifs' => $new_orders_count + $unread_chats_count,
            'latest_order' => $latest_order,
            'latest_chat' => $latest_chat,
            'recent_events' => array_slice($events, 0, 8)
        ]);
        exit;
    }

    if ($action === 'mark_all_read') {
        // Mark all messages as read
        $upd = $conn->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ?");
        $upd->execute([$admin_id]);
        echo json_encode(['status' => 'success']);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
