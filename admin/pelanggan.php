<?php
require_once 'includes/header.php';

// Handle Toggle Status
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $new_status = $_GET['toggle_status'] === 'Aktif' ? 'Aktif' : 'Nonaktif';
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'customer'");
    $stmt->execute([$new_status, $id]);
    
    $_SESSION['success'] = "Status pelanggan berhasil diubah menjadi $new_status.";
    header("Location: pelanggan.php");
    exit;
}

// Handle Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = "u.role = 'customer'";
$params = [];

if ($search !== '') {
    $where_clause .= " AND (u.nama LIKE ? OR u.email LIKE ? OR u.no_whatsapp LIKE ?)";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
}

// Get Customers
$stmt = $conn->prepare("
    SELECT u.*, COUNT(o.id) as total_pesanan, SUM(o.total) as total_belanja 
    FROM users u 
    LEFT JOIN orders o ON u.id = o.user_id AND o.status IN ('Selesai', 'Dikirim')
    WHERE $where_clause 
    GROUP BY u.id 
    ORDER BY u.created_at DESC
");
$stmt->execute($params);
$customers = $stmt->fetchAll();
?>

<?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success" style="padding: 1rem; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 1rem;">
        <?php 
        echo $_SESSION['success']; 
        unset($_SESSION['success']);
        ?>
    </div>
<?php endif; ?>

<div class="flex justify-between items-center mb-4">
    <h2 style="font-size: 1.5rem; color: var(--primary);">Daftar Pelanggan</h2>
    <form action="" method="GET" style="display: flex; gap: 0.5rem;">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari nama, email..." class="form-control" style="width: 250px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
        <?php if($search !== ''): ?>
            <a href="pelanggan.php" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Tgl Daftar</th>
                    <th>Nama Pelanggan</th>
                    <th>Email</th>
                    <th>WhatsApp</th>
                    <th>Total Transaksi</th>
                    <th>Total Belanja</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($customers as $c): ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($c['created_at'])); ?></td>
                    <td class="font-bold"><?php echo htmlspecialchars($c['nama']); ?></td>
                    <td><?php echo htmlspecialchars($c['email']); ?></td>
                    <td>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $c['no_whatsapp']); ?>" target="_blank" class="text-accent">
                            <i class="fab fa-whatsapp"></i> <?php echo htmlspecialchars($c['no_whatsapp']); ?>
                        </a>
                    </td>
                    <td><?php echo $c['total_pesanan']; ?> pesanan</td>
                    <td class="text-accent font-bold"><?php echo format_rupiah($c['total_belanja'] ?: 0); ?></td>
                    <td>
                        <?php if (isset($c['status']) && $c['status'] === 'Nonaktif'): ?>
                            <span style="background: #f8d7da; color: #721c24; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem;">Nonaktif</span>
                        <?php else: ?>
                            <span style="background: #d4edda; color: #155724; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem;">Aktif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (isset($c['status']) && $c['status'] === 'Nonaktif'): ?>
                            <a href="pelanggan.php?toggle_status=Aktif&id=<?php echo $c['id']; ?>" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;" onclick="return confirm('Aktifkan pelanggan ini?')">Aktifkan</a>
                        <?php else: ?>
                            <a href="pelanggan.php?toggle_status=Nonaktif&id=<?php echo $c['id']; ?>" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; background: #dc3545; color: white;" onclick="return confirm('Nonaktifkan pelanggan ini?')">Nonaktifkan</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(count($customers) == 0): ?>
                    <tr><td colspan="8" class="text-center">Belum ada pelanggan ditemukan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
