<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';
require_login();

if (!isset($_POST['checkout'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$is_user = $_SESSION['role'] == 'customer';

// Ambil keranjang dari database
$cart_items = [];
$stmt = $conn->prepare("SELECT c.id as cart_id, c.product_id, c.qty, c.ukuran, c.warna, p.nama_produk, p.harga, p.harga_promo, p.gambar, p.stok 
                        FROM carts c 
                        JOIN products p ON c.product_id = p.id 
                        WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$db_carts = $stmt->fetchAll();
foreach($db_carts as $c) {
    $price = $c['harga_promo'] ? $c['harga_promo'] : $c['harga'];
    $cart_items[] = [
        'product_id' => $c['product_id'],
        'nama_produk' => $c['nama_produk'],
        'harga' => $price,
        'gambar' => $c['gambar'],
        'ukuran' => $c['ukuran'],
        'warna' => $c['warna'],
        'qty' => $c['qty'],
        'stok' => $c['stok']
    ];
}

if (empty($cart_items)) {
    header("Location: keranjang.php");
    exit;
}

// Generate Order Number: GC-YYYYMMDD-XXXX
$date_prefix = date('Ymd');
$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
$stmt->execute();
$count_today = $stmt->fetchColumn() + 1;
$nomor_order = "GC-" . $date_prefix . "-" . str_pad($count_today, 4, '0', STR_PAD_LEFT);

// Collect Form Data
$nama = $_POST['nama'];
$no_whatsapp = $_POST['no_whatsapp'];
$alamat_lengkap = $_POST['alamat'] . ", Kec. " . $_POST['kecamatan'] . ", Kab/Kota " . $_POST['kabupaten'] . ", " . $_POST['provinsi'] . " " . $_POST['kode_pos'];
$metode_pengiriman = $_POST['metode_pengiriman'];
$metode_pembayaran = $_POST['metode_pembayaran'];

// Calculate Totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += ($item['harga'] * $item['qty']);
}
$diskon = 0;
$ongkir = ($metode_pengiriman == 'Ambil di toko') ? 0 : 20000; // Dummy flat rate
$total = $subtotal - $diskon + $ongkir;

try {
    $conn->beginTransaction();
    
    // Insert Order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, nomor_order, nama_pelanggan, no_whatsapp, alamat, metode_pembayaran, metode_pengiriman, subtotal, diskon, ongkir, total, status) VALUES (:uid, :no, :nama, :wa, :alamat, :bayar, :kirim, :subtotal, :diskon, :ongkir, :total, 'Pesanan Baru')");
    $stmt->execute([
        ':uid' => $user_id,
        ':no' => $nomor_order,
        ':nama' => $nama,
        ':wa' => $no_whatsapp,
        ':alamat' => $alamat_lengkap,
        ':bayar' => $metode_pembayaran,
        ':kirim' => $metode_pengiriman,
        ':subtotal' => $subtotal,
        ':diskon' => $diskon,
        ':ongkir' => $ongkir,
        ':total' => $total
    ]);
    
    $order_id = $conn->lastInsertId();
    
    // Format products string for WA message
    $produk_wa = "";
    
    // Insert Order Details
    $stmt_detail = $conn->prepare("INSERT INTO order_details (order_id, product_id, nama_produk, harga, jumlah, ukuran, warna, subtotal) VALUES (:oid, :pid, :nama, :harga, :qty, :ukuran, :warna, :subtotal)");
    $stmt_update_stock = $conn->prepare("UPDATE products SET stok = stok - :qty WHERE id = :pid AND stok >= :qty");
    
    foreach ($cart_items as $item) {
        $item_subtotal = $item['harga'] * $item['qty'];
        $stmt_detail->execute([
            ':oid' => $order_id,
            ':pid' => $item['product_id'],
            ':nama' => $item['nama_produk'],
            ':harga' => $item['harga'],
            ':qty' => $item['qty'],
            ':ukuran' => $item['ukuran'],
            ':warna' => $item['warna'],
            ':subtotal' => $item_subtotal
        ]);
        
        // Update stock
        $stmt_update_stock->execute([
            ':qty' => $item['qty'],
            ':pid' => $item['product_id']
        ]);
        
        $produk_wa .= "- {$item['nama_produk']} (Ukuran: {$item['ukuran']}, Warna: {$item['warna']}) x{$item['qty']} = " . format_rupiah($item_subtotal) . "\n";
    }
    
    $conn->commit();
    
    // Clear Cart in database
    $del = $conn->prepare("DELETE FROM carts WHERE user_id = ?");
    $del->execute([$user_id]);
    unset($_SESSION['cart']); // Also clear session just in case
    
    // Construct WhatsApp URL
    $wa_message = "Halo Graha Clouth, saya ingin melakukan pemesanan.\n\n"
                . "Nomor Pesanan: *$nomor_order*\n"
                . "Nama: $nama\n\n"
                . "Produk:\n$produk_wa\n"
                . "Subtotal: " . format_rupiah($subtotal) . "\n"
                . "Ongkir: " . format_rupiah($ongkir) . "\n"
                . "Total: *" . format_rupiah($total) . "*\n\n"
                . "Metode Pembayaran: $metode_pembayaran\n"
                . "Metode Pengiriman: $metode_pengiriman\n"
                . "Alamat:\n$alamat_lengkap\n\n"
                . "Mohon info selanjutnya, terima kasih.";
                
    $wa_url = "https://wa.me/{$whatsapp_number}?text=" . urlencode($wa_message);
    
    // Redirect to success page or WA directly. We'll show a success page with redirect.
    require_once 'includes/header.php';
    require_once 'includes/navbar.php';
    ?>
    <div class="container section text-center">
        <div style="max-width: 600px; margin: 0 auto; background: var(--white); padding: 3rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div style="width: 80px; height: 80px; background: var(--success); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 1.5rem;">
                <i class="fas fa-check"></i>
            </div>
            <h2>Pesanan Berhasil Dibuat!</h2>
            <p class="text-secondary mt-1 mb-3">Nomor Pesanan Anda: <strong style="color: var(--primary);"><?php echo $nomor_order; ?></strong></p>
            
            <p class="mb-4">Terima kasih telah berbelanja di Graha Clouth. Silakan lanjutkan ke WhatsApp untuk konfirmasi pesanan dan pembayaran Anda.</p>
            
            <div class="flex gap-2 justify-center">
                <a href="<?php echo $wa_url; ?>" target="_blank" class="btn btn-success" style="background-color: #25D366; color: white;">
                    <i class="fab fa-whatsapp" style="margin-right: 0.5rem;"></i> Konfirmasi via WhatsApp
                </a>
                <a href="index.php" class="btn btn-outline">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
    
    <script>
        // Optional: Auto redirect to WA after 5 seconds
        // setTimeout(function() { window.open('<?php echo $wa_url; ?>', '_blank'); }, 5000);
    </script>
    <?php
    require_once 'includes/footer.php';
    
} catch (Exception $e) {
    $conn->rollBack();
    die("Checkout gagal: " . $e->getMessage());
}
?>
