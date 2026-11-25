<?php
require_once 'includes/header.php';
require_once 'includes/navbar.php';

if (!is_logged_in() || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch Order
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = :id AND user_id = :uid");
$stmt->execute([':id' => $order_id, ':uid' => $user_id]);
$order = $stmt->fetch();

if (!$order) {
    echo "<div class='container section text-center py-5' style='min-height: 50vh; display: flex; align-items: center; justify-content: center; flex-direction: column;'>
            <i class='fas fa-exclamation-triangle text-secondary mb-3' style='font-size: 3rem;'></i>
            <h3 style='color: var(--dark); font-weight: 700;'>Pesanan tidak ditemukan</h3>
            <p class='text-secondary mb-4'>Pesanan yang Anda cari mungkin telah dihapus atau Anda tidak memiliki akses.</p>
            <a href='profil.php' class='btn btn-primary' style='border-radius: 30px; padding: 0.75rem 1.5rem; font-weight: 600;'><i class='fas fa-arrow-left mr-2'></i> Kembali ke Profil</a>
          </div>";
    require_once 'includes/footer.php';
    exit;
}

// Fetch Order Items with product image
$stmt_items = $conn->prepare("
    SELECT od.*, p.gambar, p.id as product_id 
    FROM order_details od 
    LEFT JOIN products p ON od.product_id = p.id 
    WHERE od.order_id = :oid
");
$stmt_items->execute([':oid' => $order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// Determine Status Colors
$status_color = '#64748b';
$status_bg = '#f1f5f9';
$status_icon = 'fa-clock';
$status_txt = $order['status'];

if ($order['status'] == 'Pesanan Baru') {
    $status_color = '#d97706';
    $status_bg = '#fef3c7';
    $status_icon = 'fa-clock';
    $status_txt = 'Menunggu Konfirmasi';
} elseif ($order['status'] == 'Diproses' || $order['status'] == 'Dikemas') {
    $status_color = '#2563eb';
    $status_bg = '#eff6ff';
    $status_icon = 'fa-box';
    $status_txt = 'Sedang Dikemas';
} elseif ($order['status'] == 'Dikirim') {
    $status_color = '#0284c7';
    $status_bg = '#e0f2fe';
    $status_icon = 'fa-truck-fast';
    $status_txt = 'Dalam Pengiriman';
} elseif ($order['status'] == 'Selesai') {
    $status_color = '#16a34a';
    $status_bg = '#dcfce7';
    $status_icon = 'fa-circle-check';
    $status_txt = 'Selesai';
} elseif ($order['status'] == 'Dibatalkan') {
    $status_color = '#dc2626';
    $status_bg = '#fee2e2';
    $status_icon = 'fa-circle-xmark';
    $status_txt = 'Dibatalkan';
}
?>

<div class="container section" style="padding-top: 2.5rem; padding-bottom: 3.5rem;">
    <div style="margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem;">
        <a href="profil.php" class="btn btn-outline" style="border-radius: 12px; width: 42px; height: 42px; padding: 0; display: flex; align-items: center; justify-content: center; border-color: #E2E8F0; color: #475569; background: #FFFFFF;" title="Kembali">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 style="font-weight: 800; color: #0F172A; margin: 0;">Detail Pesanan</h2>
            <p class="text-muted" style="margin: 0; font-size: 0.85rem;">Rincian lengkap transaksi pembelian Anda</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;" class="order-detail-layout">
        
        <!-- Order Items -->
        <div style="background: var(--white); border-radius: 20px; padding: 2rem; box-shadow: var(--shadow-sm); border: 1px solid #E2E8F0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #F1F5F9; padding-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <p class="text-muted mb-1" style="font-size: 0.8rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Nomor Pesanan</p>
                    <h3 style="font-weight: 800; color: #0F172A; margin: 0; font-family: monospace;">#<?php echo htmlspecialchars($order['nomor_order']); ?></h3>
                    <p class="text-muted mt-1" style="font-size: 0.85rem; margin: 0;"><i class="far fa-calendar-alt mr-1"></i> <?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?></p>
                </div>
                <div>
                    <span style="background-color: <?php echo $status_bg; ?>; color: <?php echo $status_color; ?>; padding: 0.45rem 1.1rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.45rem;">
                        <i class="fas <?php echo $status_icon; ?>"></i> <?php echo $status_txt; ?>
                    </span>
                </div>
            </div>

            <div class="order-items">
                <h4 style="font-weight: 800; color: #0F172A; margin-bottom: 1.25rem; font-size: 1.05rem;">Produk yang Dipesan</h4>
                <?php foreach($items as $item): ?>
                    <div style="display: flex; gap: 1rem; padding: 1rem 0; border-bottom: 1px solid #F1F5F9; align-items: center;">
                        <img src="<?php echo htmlspecialchars($item['gambar']); ?>" 
                             alt="<?php echo htmlspecialchars($item['nama_produk']); ?>" 
                             style="width: 72px; height: 72px; border-radius: 12px; object-fit: cover; border: 1px solid #E2E8F0; background: #F8FAFC; flex-shrink: 0;"
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=100&h=100&fit=crop';">
                        
                        <div style="flex-grow: 1; min-width: 0;">
                            <h5 style="font-weight: 700; color: #0F172A; margin-bottom: 0.3rem; font-size: 0.95rem;">
                                <?php if(!empty($item['product_id'])): ?>
                                    <a href="detail_produk.php?id=<?php echo $item['product_id']; ?>" style="color: inherit;">
                                        <?php echo htmlspecialchars($item['nama_produk']); ?>
                                    </a>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($item['nama_produk']); ?>
                                <?php endif; ?>
                            </h5>
                            
                            <div style="display: flex; gap: 0.35rem; margin-bottom: 0.3rem; flex-wrap: wrap;">
                                <?php if(!empty($item['ukuran'])): ?>
                                    <span style="font-size: 0.72rem; font-weight: 600; background: #F1F5F9; color: #475569; padding: 0.1rem 0.45rem; border-radius: 4px;">
                                        Size: <?php echo htmlspecialchars($item['ukuran']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if(!empty($item['warna'])): ?>
                                    <span style="font-size: 0.72rem; font-weight: 600; background: #F1F5F9; color: #475569; padding: 0.1rem 0.45rem; border-radius: 4px;">
                                        <?php echo htmlspecialchars($item['warna']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <p class="text-muted" style="font-size: 0.82rem; margin: 0;">
                                <?php echo $item['jumlah']; ?> pcs x <?php echo format_rupiah($item['harga']); ?>
                            </p>
                        </div>
                        
                        <div style="text-align: right; font-weight: 800; color: var(--accent, #2563EB); font-size: 1rem;">
                            <?php echo format_rupiah($item['subtotal']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="margin-top: 2rem; padding: 1.5rem; background: #F8FAFC; border-radius: 14px; border: 1px solid #E2E8F0;">
                <h4 style="font-weight: 800; color: #0F172A; margin-bottom: 0.85rem; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-truck-fast text-accent"></i> Informasi Pengiriman
                </h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.85rem;">
                    <div>
                        <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Penerima</div>
                        <div style="font-weight: 700; color: #0F172A;"><?php echo htmlspecialchars($order['nama_pelanggan']); ?></div>
                        <div class="text-muted"><?php echo htmlspecialchars($order['no_whatsapp']); ?></div>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Kurir Ekspedisi</div>
                        <div style="font-weight: 700; color: #0F172A;"><?php echo htmlspecialchars($order['metode_pengiriman']); ?></div>
                    </div>
                </div>
                <div style="margin-top: 0.85rem; padding-top: 0.85rem; border-top: 1px dashed #CBD5E1; font-size: 0.85rem;">
                    <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Alamat Lengkap</div>
                    <div style="color: #334155; line-height: 1.4;"><?php echo nl2br(htmlspecialchars($order['alamat'])); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Summary Sidebar -->
        <div style="background: var(--white); border-radius: 16px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.04); border: 1px solid rgba(0,0,0,0.02);">
            <h4 style="font-weight: 700; color: var(--dark); border-bottom: 2px solid var(--light); padding-bottom: 1rem; margin-bottom: 1.5rem;">Ringkasan Belanja</h4>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; color: var(--secondary);">
                <span>Total Harga (<?php echo count($items); ?> Barang)</span>
                <span style="color: var(--dark); font-weight: 600;"><?php echo format_rupiah($order['subtotal']); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; color: var(--secondary);">
                <span>Ongkos Kirim</span>
                <span style="color: var(--dark); font-weight: 600;"><?php echo format_rupiah($order['ongkir']); ?></span>
            </div>
            <?php if($order['diskon'] > 0): ?>
            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; color: var(--success);">
                <span>Diskon</span>
                <span style="font-weight: 600;">-<?php echo format_rupiah($order['diskon']); ?></span>
            </div>
            <?php endif; ?>
            
            <hr style="border-top: 1px dashed var(--border); margin: 1.5rem 0;">
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; align-items: center;">
                <span style="font-weight: 600; color: var(--dark);">Total Belanja</span>
                <span style="font-weight: 700; color: var(--primary); font-size: 1.25rem;"><?php echo format_rupiah($order['total']); ?></span>
            </div>
            
            <div style="padding: 1rem; background: #e0f2fe; border-radius: 8px; border-left: 4px solid #0284c7;">
                <p style="margin: 0; font-size: 0.85rem; color: #0c4a6e;">
                    <strong>Metode Pembayaran:</strong><br>
                    <?php echo htmlspecialchars($order['metode_pembayaran']); ?>
                </p>
            </div>
            
            <div style="margin-top: 2rem;">
                <a href="https://wa.me/<?php echo $whatsapp_number; ?>?text=Halo%20Admin,%20saya%20ingin%20bertanya%20mengenai%20pesanan%20saya%20dengan%20nomor%20<?php echo $order['nomor_order']; ?>" target="_blank" class="btn btn-outline" style="width: 100%; border-radius: 8px; font-weight: 600; padding: 0.75rem; border-color: #25D366; color: #25D366; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.3s ease;">
                    <i class="fab fa-whatsapp"></i> Hubungi Admin
                </a>
            </div>
        </div>
        
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .order-detail-layout {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>
