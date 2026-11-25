<?php
require_once 'includes/header.php';

// Filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get Sales Data (Only Completed or Shipped)
$stmt = $conn->prepare("
    SELECT DATE(created_at) as tgl, COUNT(id) as total_transaksi, SUM(total) as pendapatan 
    FROM orders 
    WHERE status IN ('Selesai', 'Dikirim') 
    AND DATE(created_at) >= :start AND DATE(created_at) <= :end 
    GROUP BY DATE(created_at) 
    ORDER BY tgl ASC
");
$stmt->execute([':start' => $start_date, ':end' => $end_date]);
$sales_data = $stmt->fetchAll();

$total_semua_transaksi = 0;
$total_semua_pendapatan = 0;
foreach ($sales_data as $s) {
    $total_semua_transaksi += $s['total_transaksi'];
    $total_semua_pendapatan += $s['pendapatan'];
}

// Get Best Selling Products in this period
$stmt_best = $conn->prepare("
    SELECT od.nama_produk, SUM(od.jumlah) as terjual, SUM(od.subtotal) as total_penjualan 
    FROM order_details od
    JOIN orders o ON od.order_id = o.id
    WHERE o.status IN ('Selesai', 'Dikirim') 
    AND DATE(o.created_at) >= :start AND DATE(o.created_at) <= :end
    GROUP BY od.product_id, od.nama_produk
    ORDER BY terjual DESC
    LIMIT 5
");
$stmt_best->execute([':start' => $start_date, ':end' => $end_date]);
$best_sellers = $stmt_best->fetchAll();
?>

<div class="flex justify-between items-center mb-4">
    <h2 style="font-size: 1.5rem; color: var(--primary);">Laporan Penjualan</h2>
    <button onclick="window.print()" class="btn btn-outline"><i class="fas fa-print"></i> Cetak Laporan</button>
</div>

<div class="card mb-4" style="background-color: var(--light);">
    <form action="" method="GET" class="flex gap-2 items-center" style="flex-wrap: wrap;">
        <div>
            <label class="form-label" style="font-size: 0.85rem;">Dari Tanggal</label>
            <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
        </div>
        <div>
            <label class="form-label" style="font-size: 0.85rem;">Sampai Tanggal</label>
            <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
        </div>
        <div style="margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">Filter Laporan</button>
        </div>
    </form>
</div>

<div class="dashboard-stats mb-4">
    <div class="stat-card">
        <div class="stat-title">Total Transaksi (Periode Ini)</div>
        <div class="stat-value"><?php echo $total_semua_transaksi; ?></div>
    </div>
    <div class="stat-card" style="border-left-color: var(--success);">
        <div class="stat-title">Total Pendapatan (Periode Ini)</div>
        <div class="stat-value text-success" style="color: var(--success);"><?php echo format_rupiah($total_semua_pendapatan); ?></div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
    <!-- Rincian Harian -->
    <div class="card">
        <h3 class="mb-3">Rincian Penjualan Harian</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jumlah Transaksi</th>
                        <th>Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($sales_data) > 0): ?>
                        <?php foreach($sales_data as $s): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($s['tgl'])); ?></td>
                            <td><?php echo $s['total_transaksi']; ?></td>
                            <td class="font-bold text-accent"><?php echo format_rupiah($s['pendapatan']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center text-secondary">Tidak ada data penjualan pada periode ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Best Sellers -->
    <div class="card">
        <h3 class="mb-3">Produk Terlaris</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Terjual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($best_sellers) > 0): ?>
                        <?php foreach($best_sellers as $b): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 500; font-size: 0.9rem; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($b['nama_produk']); ?>">
                                    <?php echo htmlspecialchars($b['nama_produk']); ?>
                                </div>
                                <div class="text-secondary" style="font-size: 0.8rem;"><?php echo format_rupiah($b['total_penjualan']); ?></div>
                            </td>
                            <td class="font-bold" style="font-size: 1.1rem; text-align: center; color: var(--accent);"><?php echo $b['terjual']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="2" class="text-center text-secondary">Belum ada data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    body { background: white; }
    .sidebar, .admin-header, .btn, form { display: none !important; }
    .admin-main { margin: 0; padding: 0; }
    .card { box-shadow: none; border: 1px solid #ccc; }
}
</style>

<?php require_once 'includes/footer.php'; ?>
