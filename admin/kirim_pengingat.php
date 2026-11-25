<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$admin_id = $_SESSION['user_id'];

if ($id > 0) {
    // Pesan default pengingat keranjang
    $message = "Halo kak! 😊 Kami melihat ada barang menarik di keranjang belanja kakak yang belum di-checkout. Yuk, segera diselesaikan pembayarannya sebelum kehabisan stok! Jika ada kendala, jangan ragu untuk membalas pesan ini ya.";
    
    // Kirim pesan dari admin ke pelanggan
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$admin_id, $id, $message]);
    
    // Arahkan admin ke halaman chat pelanggan tersebut
    header("Location: chat.php?user=" . $id);
    exit;
} else {
    header("Location: keranjang_pelanggan.php");
    exit;
}
