<?php
require_once 'includes/header.php';

// Get Stats
$stats = [];

// Total Products
$stmt = $conn->query("SELECT COUNT(*) FROM products");
$stats['produk'] = $stmt->fetchColumn();

// Total Categories
$stmt = $conn->query("SELECT COUNT(*) FROM categories");
$stats['kategori'] = $stmt->fetchColumn();

// Total Orders
$stmt = $conn->query("SELECT COUNT(*) FROM orders");
$stats['pesanan'] = $stmt->fetchColumn();

// New Orders
$stmt = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'Pesanan Baru'");
$stats['pesanan_baru'] = $stmt->fetchColumn();

// Total Customers
$stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
$stats['pelanggan'] = $stmt->fetchColumn();

// Total Sales (Completed or Delivered)
$stmt = $conn->query("SELECT SUM(total) FROM orders WHERE status IN ('Selesai', 'Dikirim')");
$stats['penjualan'] = $stmt->fetchColumn() ?: 0;

// Low Stock Products
$stmt = $conn->query("SELECT * FROM products WHERE stok <= 5 ORDER BY stok ASC LIMIT 5");
$low_stock = $stmt->fetchAll();

// Recent Orders
$stmt = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
$recent_orders = $stmt->fetchAll();
?>

<div class="flex justify-between items-center mb-4">
    <h2 style="font-size: 1.5rem; color: var(--primary);">Dashboard</h2>
</div>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-title">Total Pendapatan</div>
        <div class="stat-value text-accent"><?php echo format_rupiah($stats['penjualan']); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Pesanan Baru</div>
        <div class="stat-value"><?php echo $stats['pesanan_baru']; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Total Pesanan</div>
        <div class="stat-value"><?php echo $stats['pesanan']; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Total Produk</div>
        <div class="stat-value"><?php echo $stats['produk']; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Total Pelanggan</div>
        <div class="stat-value"><?php echo $stats['pelanggan']; ?></div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
    <!-- Recent Orders -->
    <div class="card">
        <div class="flex justify-between items-center mb-3">
            <h3 style="font-size: 1.1rem;">Pesanan Terbaru</h3>
            <a href="pesanan.php" class="text-accent" style="font-size: 0.9rem;">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No. Order</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($recent_orders) > 0): ?>
                        <?php foreach($recent_orders as $order): ?>
                            <tr>
                                <td><a href="pesanan.php?id=<?php echo $order['id']; ?>" class="text-accent"><?php echo $order['nomor_order']; ?></a></td>
                                <td><?php echo htmlspecialchars($order['nama_pelanggan']); ?></td>
                                <td><?php echo format_rupiah($order['total']); ?></td>
                                <td>
                                    <?php 
                                    $status_color = 'var(--secondary)';
                                    if ($order['status'] == 'Pesanan Baru') $status_color = '#eab308'; // yellow
                                    if ($order['status'] == 'Dikirim' || $order['status'] == 'Selesai') $status_color = 'var(--success)';
                                    if ($order['status'] == 'Dibatalkan') $status_color = 'var(--danger)';
                                    ?>
                                    <span style="background-color: <?php echo $status_color; ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;"><?php echo $order['status']; ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-secondary">Belum ada pesanan</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Low Stock Alert -->
    <div class="card">
        <div class="flex justify-between items-center mb-3">
            <h3 style="font-size: 1.1rem;">Stok Menipis</h3>
            <a href="produk.php" class="text-accent" style="font-size: 0.9rem;">Kelola</a>
        </div>
        <div>
            <?php if(count($low_stock) > 0): ?>
                <?php foreach($low_stock as $prod): ?>
                    <div class="flex justify-between items-center mb-2 pb-2" style="border-bottom: 1px solid var(--border);">
                        <div>
                            <div style="font-weight: 500; font-size: 0.9rem;"><?php echo htmlspecialchars($prod['nama_produk']); ?></div>
                            <div class="text-secondary" style="font-size: 0.8rem;">Stok: <span class="text-danger font-bold"><?php echo $prod['stok']; ?></span></div>
                        </div>
                        <a href="edit_produk.php?id=<?php echo $prod['id']; ?>" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Edit</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-secondary">Semua stok produk aman.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
