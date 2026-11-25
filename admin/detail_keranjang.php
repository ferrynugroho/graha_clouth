<?php
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get customer info
$stmt = $conn->prepare("SELECT id, nama, email, no_whatsapp FROM users WHERE id = ? AND role = 'customer'");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    echo "<script>alert('Pelanggan tidak ditemukan'); window.location.href='keranjang_pelanggan.php';</script>";
    exit;
}

// Get cart items
$stmt = $conn->prepare("
    SELECT c.*, p.nama_produk, p.gambar, p.harga, p.harga_promo
    FROM carts c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$id]);
$cart_items = $stmt->fetchAll();
$total = 0;
?>

<div class="flex justify-between items-center mb-4">
    <h2 style="font-size: 1.5rem; color: var(--primary);">Detail Keranjang: <?php echo htmlspecialchars($customer['nama']); ?></h2>
    <a href="keranjang_pelanggan.php" class="btn btn-secondary">Kembali</a>
</div>

<div class="card mb-4">
    <h3 class="mb-3">Informasi Pelanggan</h3>
    <p><strong>Nama:</strong> <?php echo htmlspecialchars($customer['nama']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
    <p><strong>WhatsApp:</strong> 
        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $customer['no_whatsapp']); ?>" target="_blank" class="text-accent">
            <?php echo htmlspecialchars($customer['no_whatsapp']); ?>
        </a>
    </p>
</div>

<div class="card">
    <h3 class="mb-3">Isi Keranjang</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Ukuran & Warna</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($cart_items as $item): ?>
                    <?php 
                    $price = $item['harga_promo'] ? $item['harga_promo'] : $item['harga'];
                    $subtotal = $price * $item['qty'];
                    $total += $subtotal;
                    ?>
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                <img src="<?php echo htmlspecialchars($item['gambar']); ?>" alt="img" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                <span class="font-bold"><?php echo htmlspecialchars($item['nama_produk']); ?></span>
                            </div>
                        </td>
                        <td>
                            <?php if($item['ukuran']) echo "Uk: " . htmlspecialchars($item['ukuran']); ?>
                            <?php if($item['ukuran'] && $item['warna']) echo "<br>"; ?>
                            <?php if($item['warna']) echo "Warna: " . htmlspecialchars($item['warna']); ?>
                        </td>
                        <td><?php echo format_rupiah($price); ?></td>
                        <td><?php echo $item['qty']; ?></td>
                        <td class="font-bold text-accent"><?php echo format_rupiah($subtotal); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if(count($cart_items) == 0): ?>
                    <tr><td colspan="5" class="text-center">Keranjang kosong.</td></tr>
                <?php endif; ?>
            </tbody>
            <?php if(count($cart_items) > 0): ?>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right font-bold">Total Estimasi Belanja:</td>
                    <td class="font-bold text-accent" style="font-size: 1.1rem;"><?php echo format_rupiah($total); ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
