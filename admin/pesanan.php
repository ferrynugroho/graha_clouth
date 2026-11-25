<?php
require_once 'includes/header.php';

// Handle Status Update
if (isset($_POST['update_status'])) {
    $id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :id");
    if ($stmt->execute([':status' => $status, ':id' => $id])) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('Status pesanan berhasil diupdate!');
                setTimeout(() => window.location.href='pesanan.php', 1500);
            });
        </script>";
    }
}

$view_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<style>
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;padding-bottom:1.25rem;border-bottom:2px solid #e9ecef;}
.page-title{font-size:1.55rem;font-weight:700;color:#1a1a2e;display:flex;align-items:center;gap:.75rem;}
.page-title-icon{width:44px;height:44px;background:linear-gradient(135deg,#4f7ef8,#7c3aed);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;flex-shrink:0;}
.page-subtitle{font-size:.82rem;color:#9ca3af;margin-top:.1rem;font-weight:400;}
.status-badge{display:inline-flex;align-items:center;gap:.4rem;padding:.3rem .85rem;border-radius:999px;font-size:.76rem;font-weight:700;letter-spacing:.2px;white-space:nowrap;}
.status-badge::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor;opacity:.7;flex-shrink:0;}
.badge-new{background:#fef3c7;color:#92400e;}
.badge-process{background:#dbeafe;color:#1e40af;}
.badge-pack{background:#ede9fe;color:#5b21b6;}
.badge-ship{background:#d1fae5;color:#065f46;}
.badge-done{background:#dcfce7;color:#14532d;}
.badge-cancel{background:#fee2e2;color:#991b1b;}
.orders-card{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 20px rgba(0,0,0,.04);overflow:hidden;border:1px solid #f1f5f9;}
.orders-card-header{display:flex;justify-content:space-between;align-items:center;padding:1.25rem 1.5rem;border-bottom:1px solid #f1f5f9;background:#fafbfc;}
.orders-count{font-size:.875rem;color:#6b7280;font-weight:500;}
.orders-count span{font-weight:800;color:#4f7ef8;}
.search-box{position:relative;}
.search-box input{padding:.55rem 1rem .55rem 2.4rem;border:1.5px solid #e5e7eb;border-radius:10px;font-size:.875rem;outline:none;transition:all .2s;background:#fff;width:260px;font-family:inherit;color:#374151;}
.search-box input:focus{border-color:#4f7ef8;box-shadow:0 0 0 3px rgba(79,126,248,.12);}
.search-box i{position:absolute;left:.8rem;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:.82rem;}
.elegant-table{width:100%;border-collapse:collapse;}
.elegant-table thead tr{background:#f8faff;}
.elegant-table th{padding:.9rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#64748b;border-bottom:2px solid #eef2ff;white-space:nowrap;}
.elegant-table td{padding:1rem 1.25rem;border-bottom:1px solid #f8f9ff;font-size:.875rem;color:#374151;vertical-align:middle;}
.elegant-table tbody tr{transition:background .15s;}
.elegant-table tbody tr:hover{background:#f8faff;}
.elegant-table tbody tr:last-child td{border-bottom:none;}
.order-number{font-family:'Courier New',monospace;font-weight:700;font-size:.8rem;color:#4f7ef8;background:#eef2ff;padding:.25rem .65rem;border-radius:6px;display:inline-block;}
.customer-name{font-weight:600;color:#1f2937;}
.customer-phone{font-size:.78rem;color:#9ca3af;display:flex;align-items:center;gap:.3rem;margin-top:.2rem;}
.order-date{font-size:.82rem;color:#374151;font-weight:500;}
.order-time{font-size:.75rem;color:#9ca3af;margin-top:.15rem;}
.order-amount{font-weight:800;color:#059669;font-size:.9rem;}
.payment-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.28rem .7rem;border-radius:6px;font-size:.78rem;font-weight:500;background:#f3f4f6;color:#4b5563;}
.btn-detail{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem .9rem;background:linear-gradient(135deg,#4f7ef8,#7c3aed);color:#fff;border-radius:8px;font-size:.8rem;font-weight:600;transition:all .2s;border:none;cursor:pointer;text-decoration:none;}
.btn-detail:hover{transform:translateY(-2px);box-shadow:0 4px 14px rgba(79,126,248,.35);color:#fff;}
.empty-state{text-align:center;padding:4rem 2rem;}
.empty-state i{font-size:3.5rem;color:#e5e7eb;margin-bottom:1rem;display:block;}
.empty-state h3{color:#9ca3af;font-size:1.1rem;margin-bottom:.4rem;}
.empty-state p{color:#d1d5db;font-size:.875rem;}
/* Detail */
.detail-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem;}
.detail-title h2{font-size:1.5rem;font-weight:700;color:#1a1a2e;display:flex;align-items:center;gap:.6rem;}
.detail-title h2 i{color:#4f7ef8;}
.order-id-badge{font-size:.88rem;color:#4f7ef8;background:#eef2ff;padding:.3rem .85rem;border-radius:8px;font-weight:700;font-family:'Courier New',monospace;}
.btn-back{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.25rem;border:1.5px solid #e5e7eb;border-radius:10px;color:#6b7280;font-size:.875rem;font-weight:500;transition:all .2s;text-decoration:none;background:#fff;white-space:nowrap;}
.btn-back:hover{border-color:#4f7ef8;color:#4f7ef8;background:#eef2ff;}
.detail-layout{display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start;}
.detail-card{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04);border:1px solid #f1f5f9;overflow:hidden;margin-bottom:1.25rem;}
.detail-card:last-child{margin-bottom:0;}
.detail-card-header{display:flex;align-items:center;gap:.75rem;padding:1rem 1.5rem;border-bottom:1px solid #f1f5f9;background:#fafbfc;}
.card-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.9rem;color:#fff;flex-shrink:0;}
.icon-product{background:linear-gradient(135deg,#4f7ef8,#7c3aed);}
.icon-customer{background:linear-gradient(135deg,#10b981,#059669);}
.icon-status{background:linear-gradient(135deg,#f59e0b,#ef4444);}
.detail-card-header h3{font-size:.95rem;font-weight:700;color:#1f2937;}
.item-count{margin-left:auto;font-size:.78rem;color:#9ca3af;background:#f3f4f6;padding:.2rem .6rem;border-radius:6px;}
.detail-table{width:100%;border-collapse:collapse;}
.detail-table th{padding:.75rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:#64748b;background:#f8faff;border-bottom:2px solid #eef2ff;}
.detail-table td{padding:.9rem 1.25rem;border-bottom:1px solid #f8f9ff;font-size:.875rem;color:#374151;vertical-align:middle;}
.detail-table tbody tr:hover{background:#f8faff;}
.detail-table tfoot td{padding:.75rem 1.25rem;border-bottom:none;color:#6b7280;font-size:.875rem;}
.info-list{list-style:none;padding:0;margin:0;}
.info-list li{display:flex;align-items:flex-start;gap:.75rem;padding:.7rem 1.5rem;border-bottom:1px solid #f8f9ff;font-size:.875rem;}
.info-list li:last-child{border-bottom:none;}
.info-label{min-width:90px;font-weight:700;color:#9ca3af;font-size:.72rem;text-transform:uppercase;letter-spacing:.5px;padding-top:3px;flex-shrink:0;}
.info-value{color:#1f2937;font-weight:500;line-height:1.6;}
.info-value a{color:#10b981;text-decoration:none;}
.info-value a:hover{text-decoration:underline;}
.current-status-box{display:flex;align-items:center;justify-content:space-between;padding:.85rem 1rem;background:#f8faff;border-radius:10px;margin-bottom:1rem;border:1px solid #eef2ff;}
.cs-label{font-size:.72rem;color:#9ca3af;font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
.status-select-wrap{position:relative;margin-bottom:1rem;}
.status-select{width:100%;padding:.75rem 2.5rem .75rem 1rem;border:1.5px solid #e5e7eb;border-radius:10px;font-size:.875rem;font-family:inherit;appearance:none;-webkit-appearance:none;background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 1rem center;cursor:pointer;outline:none;transition:all .2s;color:#374151;font-weight:500;}
.status-select:focus{border-color:#4f7ef8;box-shadow:0 0 0 3px rgba(79,126,248,.12);}
.btn-update{width:100%;padding:.85rem;background:linear-gradient(135deg,#4f7ef8,#7c3aed);color:#fff;border:none;border-radius:10px;font-size:.9rem;font-weight:700;cursor:pointer;transition:all .2s;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:.5rem;}
.btn-update:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(79,126,248,.35);}
.chip{display:inline-block;padding:.2rem .6rem;border-radius:6px;font-size:.78rem;font-weight:500;}
.chip-gray{background:#f3f4f6;color:#4b5563;}
.chip-yellow{background:#fef3c7;color:#92400e;}
.chip-blue{background:#eef2ff;color:#4f7ef8;font-weight:700;}
.chip-green{color:#059669;font-weight:800;}
/* Toast */
.toast-wrap{position:fixed;top:1.5rem;right:1.5rem;z-index:9999;}
.toast{display:flex;align-items:center;gap:.75rem;padding:.9rem 1.25rem;background:#fff;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.12);font-size:.875rem;font-weight:600;color:#1f2937;border-left:4px solid #10b981;animation:slideIn .3s ease;}
.toast i{color:#10b981;}
@keyframes slideIn{from{opacity:0;transform:translateX(30px);}to{opacity:1;transform:translateX(0);}}
@media(max-width:900px){.detail-layout{grid-template-columns:1fr;}}
</style>

<div class="toast-wrap" id="toastWrap"></div>

<?php
$badge_map = [
    'Pesanan Baru' => 'badge-new',
    'Diproses'     => 'badge-process',
    'Dikemas'      => 'badge-pack',
    'Dikirim'      => 'badge-ship',
    'Selesai'      => 'badge-done',
    'Dibatalkan'   => 'badge-cancel',
];

if ($view_id > 0):
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = :id");
    $stmt->execute([':id' => $view_id]);
    $order = $stmt->fetch();
    if (!$order) {
        echo "<script>alert('Pesanan tidak ditemukan.'); window.location.href='pesanan.php';</script>";
        exit;
    }
    $stmt = $conn->prepare("SELECT * FROM order_details WHERE order_id = :id");
    $stmt->execute([':id' => $view_id]);
    $details = $stmt->fetchAll();
    $current_badge = $badge_map[$order['status']] ?? 'badge-process';
?>

<div class="detail-header">
    <div class="detail-title">
        <h2><i class="fas fa-receipt"></i> Detail Pesanan</h2>
        <div style="margin-top:.5rem;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
            <span class="order-id-badge"><?php echo htmlspecialchars($order['nomor_order']); ?></span>
            <span class="status-badge <?php echo $current_badge; ?>"><?php echo htmlspecialchars($order['status']); ?></span>
        </div>
    </div>
    <a href="pesanan.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="detail-layout">
    <div>
        <div class="detail-card">
            <div class="detail-card-header">
                <div class="card-icon icon-product"><i class="fas fa-box-open"></i></div>
                <h3>Daftar Produk</h3>
                <span class="item-count"><?php echo count($details); ?> item</span>
            </div>
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Ukuran</th>
                        <th>Warna</th>
                        <th>Harga</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($details as $item): ?>
                    <tr>
                        <td style="font-weight:600;color:#1f2937;"><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                        <td><span class="chip chip-gray"><?php echo htmlspecialchars($item['ukuran']); ?></span></td>
                        <td><span class="chip chip-yellow"><?php echo htmlspecialchars($item['warna']); ?></span></td>
                        <td style="color:#6b7280;"><?php echo format_rupiah($item['harga']); ?></td>
                        <td><span class="chip chip-blue">&times;<?php echo $item['jumlah']; ?></span></td>
                        <td style="font-weight:800;color:#059669;"><?php echo format_rupiah($item['subtotal']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align:right;">Subtotal Produk</td>
                        <td style="font-weight:600;color:#374151;"><?php echo format_rupiah($order['subtotal']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="5" style="text-align:right;">Ongkos Kirim</td>
                        <td style="font-weight:600;color:#374151;"><?php echo format_rupiah($order['ongkir']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="5" style="border-top:2px solid #eef2ff;padding-top:1rem;font-weight:800;color:#1f2937;font-size:.95rem;text-align:right;">Total Pembayaran</td>
                        <td style="border-top:2px solid #eef2ff;padding-top:1rem;font-size:1.05rem;font-weight:800;color:#059669;"><?php echo format_rupiah($order['total']); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div>
        <div class="detail-card">
            <div class="detail-card-header">
                <div class="card-icon icon-customer"><i class="fas fa-user"></i></div>
                <h3>Informasi Pelanggan</h3>
            </div>
            <ul class="info-list">
                <li>
                    <span class="info-label">Nama</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['nama_pelanggan']); ?></span>
                </li>
                <li>
                    <span class="info-label">WhatsApp</span>
                    <span class="info-value">
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $order['no_whatsapp']); ?>" target="_blank">
                            <i class="fab fa-whatsapp" style="color:#25d366;"></i>
                            <?php echo htmlspecialchars($order['no_whatsapp']); ?>
                        </a>
                    </span>
                </li>
                <li style="align-items:flex-start;">
                    <span class="info-label">Alamat</span>
                    <span class="info-value"><?php echo nl2br(htmlspecialchars($order['alamat'])); ?></span>
                </li>
                <li>
                    <span class="info-label">Pembayaran</span>
                    <span class="info-value">
                        <span class="payment-badge"><i class="fas fa-credit-card"></i> <?php echo htmlspecialchars($order['metode_pembayaran']); ?></span>
                    </span>
                </li>
                <li>
                    <span class="info-label">Pengiriman</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['metode_pengiriman']); ?></span>
                </li>
                <li>
                    <span class="info-label">Tanggal</span>
                    <span class="info-value"><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></span>
                </li>
            </ul>
        </div>

        <div class="detail-card">
            <div class="detail-card-header">
                <div class="card-icon icon-status"><i class="fas fa-sync-alt"></i></div>
                <h3>Update Status</h3>
            </div>
            <div style="padding:1.25rem;">
                <div class="current-status-box">
                    <span class="cs-label">Status sekarang</span>
                    <span class="status-badge <?php echo $current_badge; ?>"><?php echo htmlspecialchars($order['status']); ?></span>
                </div>
                <form action="" method="POST">
                    <input type="hidden" name="update_status" value="1">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <div class="status-select-wrap">
                        <select name="status" class="status-select" required>
                            <?php
                            $statuses = ['Pesanan Baru','Diproses','Dikemas','Dikirim','Selesai','Dibatalkan'];
                            foreach($statuses as $st) {
                                $sel = $order['status'] == $st ? 'selected' : '';
                                echo "<option value=\"$st\" $sel>$st</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-update">
                        <i class="fas fa-check-circle"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php else:
    $stmt   = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
    $orders = $stmt->fetchAll();
?>

<div class="page-header">
    <div>
        <div class="page-title">
            <div class="page-title-icon"><i class="fas fa-shopping-bag"></i></div>
            <div>
                Manajemen Pesanan
                <div class="page-subtitle">Kelola dan pantau seluruh transaksi pelanggan</div>
            </div>
        </div>
    </div>
</div>

<div class="orders-card">
    <div class="orders-card-header">
        <div class="orders-count">Total <span><?php echo count($orders); ?></span> pesanan</div>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Cari nama, no. order, status..." oninput="filterOrders()">
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table class="elegant-table" id="ordersTable">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>No. Order</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Pembayaran</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($orders)): ?>
                <tr><td colspan="7">
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>Belum ada pesanan</h3>
                        <p>Pesanan dari pelanggan akan muncul di sini</p>
                    </div>
                </td></tr>
                <?php else: ?>
                <?php foreach($orders as $o):
                    $bc = $badge_map[$o['status']] ?? 'badge-process';
                ?>
                <tr>
                    <td>
                        <div class="order-date"><?php echo date('d M Y', strtotime($o['created_at'])); ?></div>
                        <div class="order-time"><?php echo date('H:i', strtotime($o['created_at'])); ?> WIB</div>
                    </td>
                    <td><span class="order-number"><?php echo htmlspecialchars($o['nomor_order']); ?></span></td>
                    <td>
                        <div class="customer-name"><?php echo htmlspecialchars($o['nama_pelanggan']); ?></div>
                        <div class="customer-phone"><i class="fab fa-whatsapp" style="color:#25d366;"></i><?php echo htmlspecialchars($o['no_whatsapp']); ?></div>
                    </td>
                    <td><span class="order-amount"><?php echo format_rupiah($o['total']); ?></span></td>
                    <td><span class="payment-badge"><i class="fas fa-credit-card"></i> <?php echo htmlspecialchars($o['metode_pembayaran']); ?></span></td>
                    <td><span class="status-badge <?php echo $bc; ?>"><?php echo htmlspecialchars($o['status']); ?></span></td>
                    <td><a href="pesanan.php?id=<?php echo $o['id']; ?>" class="btn-detail"><i class="fas fa-eye"></i> Detail</a></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<script>
function filterOrders() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#ordersTable tbody tr').forEach(r => {
        r.style.display = r.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
}
function showToast(msg) {
    const wrap = document.getElementById('toastWrap');
    const t = document.createElement('div');
    t.className = 'toast';
    t.innerHTML = '<i class="fas fa-check-circle"></i> ' + msg;
    wrap.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .4s'; }, 2800);
    setTimeout(() => t.remove(), 3300);
}
</script>

<?php require_once 'includes/footer.php'; ?>
