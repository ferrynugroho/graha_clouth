<?php
require_once 'config/database.php';

try {
    // Check if column alamat exists
    $check_alamat = $conn->query("SHOW COLUMNS FROM users LIKE 'alamat'")->rowCount();
    if ($check_alamat == 0) {
        $conn->exec("ALTER TABLE users ADD COLUMN alamat TEXT NULL AFTER no_whatsapp");
        echo "Kolom 'alamat' berhasil ditambahkan.<br>";
    }

    // Check if column foto exists
    $check_foto = $conn->query("SHOW COLUMNS FROM users LIKE 'foto'")->rowCount();
    if ($check_foto == 0) {
        $conn->exec("ALTER TABLE users ADD COLUMN foto VARCHAR(255) NULL AFTER alamat");
        echo "Kolom 'foto' berhasil ditambahkan.<br>";
    }

    // Check if column status exists
    $check_status = $conn->query("SHOW COLUMNS FROM users LIKE 'status'")->rowCount();
    if ($check_status == 0) {
        $conn->exec("ALTER TABLE users ADD COLUMN status ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif' AFTER foto");
        echo "Kolom 'status' berhasil ditambahkan.<br>";
    }

    echo "Update database selesai.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
