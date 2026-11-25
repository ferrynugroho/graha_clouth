<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrahaClouth – Fashion Modern untuk Semua</title>
    <meta name="description" content="GrahaClouth menyediakan koleksi fashion modern, nyaman, dan berkualitas premium. Belanja online kaos, kemeja, hoodie, sepatu, dan aksesoris terbaik dengan harga terjangkau.">
    <meta name="keywords" content="fashion online, toko baju, kaos, kemeja, hoodie, sepatu, aksesoris, GrahaClouth">
    <meta name="theme-color" content="#E8892D">
    <!-- Open Graph -->
    <meta property="og:title" content="GrahaClouth – Fashion Modern untuk Semua">
    <meta property="og:description" content="Koleksi fashion pilihan untuk setiap momen.">
    <meta property="og:type" content="website">
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>👔</text></svg>">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
