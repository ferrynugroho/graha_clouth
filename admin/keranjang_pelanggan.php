<?php
require_once 'includes/header.php';

// Ambil data pelanggan beserta jumlah item di keranjang
$stmt = $conn->query("
    SELECT u.id, u.nama, u.email, u.no_whatsapp, 
           COUNT(c.id) as total_item, 
           SUM(c.qty * p.harga) as total_nilai
    FROM users u
    JOIN carts c ON u.id = c.user_id
    JOIN products p ON c.product_id = p.id
    WHERE u.role = 'customer'
    GROUP BY u.id
    ORDER BY total_nilai DESC
");
$customers = $stmt->fetchAll();
?>

<div class="flex justify-between items-center mb-4">
    <h2 style="font-size: 1.5rem; color: var(--primary);">Pantau Keranjang Pelanggan</h2>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nama Pelanggan</th>
                    <th>Kontak</th>
                    <th>Total Item di Keranjang</th>
                    <th>Estimasi Nilai</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($customers as $c): ?>
                <tr>
                    <td class="font-bold"><?php echo htmlspecialchars($c['nama']); ?></td>
                    <td>
                        <div><?php echo htmlspecialchars($c['email']); ?></div>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $c['no_whatsapp']); ?>" target="_blank" class="text-accent" style="font-size: 0.85rem;">
                            <i class="fab fa-whatsapp"></i> <?php echo htmlspecialchars($c['no_whatsapp']); ?>
                        </a>
                    </td>
                    <td><?php echo $c['total_item']; ?> item</td>
                    <td class="text-accent font-bold"><?php echo format_rupiah($c['total_nilai'] ?: 0); ?></td>
                    <td>
                        <div style="display: flex; gap: 5px;">
                            <a href="detail_keranjang.php?id=<?php echo $c['id']; ?>" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Lihat Detail</a>
                            <a href="kirim_pengingat.php?id=<?php echo $c['id']; ?>" class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; background: #28a745; color: white; border: none;"><i class="fas fa-comment"></i> Ingatkan (Chat)</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(count($customers) == 0): ?>
                    <tr><td colspan="5" class="text-center">Saat ini tidak ada pelanggan yang memiliki barang di keranjang.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
