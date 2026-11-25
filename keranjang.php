<?php
require_once 'includes/header.php';
require_once 'includes/navbar.php';

$is_user = is_logged_in() && $_SESSION['role'] == 'customer';
$user_id = $is_user ? $_SESSION['user_id'] : null;

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'add') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $qty = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;
    $ukuran = isset($_GET['ukuran']) ? $_GET['ukuran'] : '';
    $warna = isset($_GET['warna']) ? $_GET['warna'] : '';
    
    // Check if product exists and get details
    $stmt = $conn->prepare("SELECT id, nama_produk, harga, harga_promo, gambar, stok FROM products WHERE id = :id AND status = 'aktif'");
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch();
    
    if ($product) {
        $price = $product['harga_promo'] ? $product['harga_promo'] : $product['harga'];
        
        if ($is_user) {
            $check = $conn->prepare("SELECT id FROM carts WHERE user_id = ? AND product_id = ? AND ukuran = ? AND warna = ?");
            $check->execute([$user_id, $id, $ukuran, $warna]);
            $exist = $check->fetch();
            if ($exist) {
                $upd = $conn->prepare("UPDATE carts SET qty = qty + ? WHERE id = ?");
                $upd->execute([$qty, $exist['id']]);
            } else {
                $ins = $conn->prepare("INSERT INTO carts (user_id, product_id, ukuran, warna, qty) VALUES (?, ?, ?, ?, ?)");
                $ins->execute([$user_id, $id, $ukuran, $warna, $qty]);
            }
        } else {
            $cart_id = $id . '_' . $ukuran . '_' . $warna;
            if (isset($_SESSION['cart'][$cart_id])) {
                $_SESSION['cart'][$cart_id]['qty'] += $qty;
            } else {
                $_SESSION['cart'][$cart_id] = [
                    'product_id' => $id,
                    'nama_produk' => $product['nama_produk'],
                    'harga' => $price,
                    'gambar' => $product['gambar'],
                    'ukuran' => $ukuran,
                    'warna' => $warna,
                    'qty' => $qty,
                    'stok' => $product['stok']
                ];
            }
        }
        echo "<script>window.location.href='keranjang.php';</script>";
        exit;
    }
} elseif ($action == 'update') {
    $cart_id = isset($_GET['cart_id']) ? $_GET['cart_id'] : '';
    $qty = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;
    
    if ($is_user) {
        // check stok first
        $chk = $conn->prepare("SELECT c.qty, p.stok FROM carts c JOIN products p ON c.product_id = p.id WHERE c.id = ? AND c.user_id = ?");
        $chk->execute([$cart_id, $user_id]);
        $row = $chk->fetch();
        if ($row && $qty > 0 && $qty <= $row['stok']) {
            $upd = $conn->prepare("UPDATE carts SET qty = ? WHERE id = ?");
            $upd->execute([$qty, $cart_id]);
        }
    } else {
        if (isset($_SESSION['cart'][$cart_id])) {
            if ($qty > 0 && $qty <= $_SESSION['cart'][$cart_id]['stok']) {
                $_SESSION['cart'][$cart_id]['qty'] = $qty;
            }
        }
    }
    echo "<script>window.location.href='keranjang.php';</script>";
    exit;
} elseif ($action == 'remove') {
    $cart_id = isset($_GET['cart_id']) ? $_GET['cart_id'] : '';
    if ($is_user) {
        $del = $conn->prepare("DELETE FROM carts WHERE id = ? AND user_id = ?");
        $del->execute([$cart_id, $user_id]);
    } else {
        if (isset($_SESSION['cart'][$cart_id])) {
            unset($_SESSION['cart'][$cart_id]);
        }
    }
    echo "<script>window.location.href='keranjang.php';</script>";
    exit;
} elseif ($action == 'clear') {
    if ($is_user) {
        $del = $conn->prepare("DELETE FROM carts WHERE user_id = ?");
        $del->execute([$user_id]);
    } else {
        $_SESSION['cart'] = [];
    }
    echo "<script>window.location.href='keranjang.php';</script>";
    exit;
}

// Fetch current cart items
$cart_items = [];
if ($is_user) {
    $stmt = $conn->prepare("SELECT c.id as cart_id, c.product_id, c.qty, c.ukuran, c.warna, p.nama_produk, p.harga, p.harga_promo, p.gambar, p.stok 
                            FROM carts c 
                            JOIN products p ON c.product_id = p.id 
                            WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    $db_carts = $stmt->fetchAll();
    foreach($db_carts as $c) {
        $price = $c['harga_promo'] ? $c['harga_promo'] : $c['harga'];
        $cart_items[$c['cart_id']] = [
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
} else {
    $cart_items = $_SESSION['cart'];
}

$subtotal = 0;
$total_items_count = 0;
foreach ($cart_items as $item) {
    $subtotal += ($item['harga'] * $item['qty']);
    $total_items_count += $item['qty'];
}
?>

<div class="container section-sm">

    <!-- Progress Checkout Stepper -->
    <div class="checkout-stepper-wrap">
        <div class="checkout-step-node active">
            <div class="step-num"><i class="fas fa-shopping-bag"></i></div>
            <span class="step-txt">Keranjang</span>
        </div>
        <div class="step-divider"></div>
        <div class="checkout-step-node">
            <div class="step-num">2</div>
            <span class="step-txt">Pengiriman</span>
        </div>
        <div class="step-divider"></div>
        <div class="checkout-step-node">
            <div class="step-num">3</div>
            <span class="step-txt">Pembayaran</span>
        </div>
    </div>

    <div class="page-title-wrap mb-4">
        <h1 class="page-main-heading">Keranjang Belanja</h1>
        <p class="page-sub-heading text-muted">Periksa kembali item fashion pilihan Anda sebelum menyelesaikan pembayaran.</p>
    </div>
    
    <?php if(empty($cart_items)): ?>
        <div class="empty-cart-card">
            <div class="empty-cart-icon">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <h2 class="empty-cart-title">Keranjang Belanja Anda Kosong</h2>
            <p class="empty-cart-desc">Belum ada koleksi fashion yang Anda tambahkan. Jelajahi tren busana terbaru kami sekarang!</p>
            <a href="produk.php" class="btn btn-primary btn-shimmer" style="padding: 0.85rem 2rem; border-radius: 12px; font-weight: 700;">
                <i class="fas fa-compass" style="margin-right: 0.5rem;"></i> Mulai Belanja Sekarang
            </a>
        </div>
    <?php else: ?>
        <div class="cart-layout-grid">
            <!-- Cart Items Container -->
            <div class="cart-items-panel">
                
                <div class="cart-panel-header">
                    <div class="cart-panel-title">
                        Daftar Produk <span>(<?php echo $total_items_count; ?> item)</span>
                    </div>
                    <a href="keranjang.php?action=clear" class="cart-clear-btn" onclick="return confirm('Apakah Anda yakin ingin mengosongkan seluruh isi keranjang?');">
                        <i class="fas fa-trash-alt"></i> Kosongkan
                    </a>
                </div>

                <!-- Desktop Table View (Hidden on Mobile) -->
                <div class="cart-desktop-table-wrap">
                    <table class="cart-desktop-table">
                        <thead>
                            <tr>
                                <th>Produk Fashion</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($cart_items as $cart_id => $item): ?>
                                <?php $item_subtotal = $item['harga'] * $item['qty']; ?>
                                <tr>
                                    <td>
                                        <div class="cart-prod-cell">
                                            <img src="<?php echo htmlspecialchars($item['gambar']); ?>" alt="<?php echo htmlspecialchars($item['nama_produk']); ?>" class="cart-prod-thumb" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=100&h=100&fit=crop';">
                                            <div class="cart-prod-meta">
                                                <a href="detail_produk.php?id=<?php echo $item['product_id']; ?>" class="cart-prod-title">
                                                    <?php echo htmlspecialchars($item['nama_produk']); ?>
                                                </a>
                                                <div class="cart-variant-tags">
                                                    <?php if(!empty($item['ukuran'])): ?>
                                                        <span class="variant-pill"><i class="fas fa-ruler-combined"></i> Size <?php echo htmlspecialchars($item['ukuran']); ?></span>
                                                    <?php endif; ?>
                                                    <?php if(!empty($item['warna'])): ?>
                                                        <span class="variant-pill"><i class="fas fa-palette"></i> <?php echo htmlspecialchars($item['warna']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="cart-price-col"><?php echo format_rupiah($item['harga']); ?></td>
                                    <td>
                                        <div class="cart-stepper-box">
                                            <a href="keranjang.php?action=update&cart_id=<?php echo $cart_id; ?>&qty=<?php echo max(1, $item['qty'] - 1); ?>" class="stepper-btn" title="Kurangi">-</a>
                                            <span class="stepper-val"><?php echo $item['qty']; ?></span>
                                            <a href="keranjang.php?action=update&cart_id=<?php echo $cart_id; ?>&qty=<?php echo min($item['stok'], $item['qty'] + 1); ?>" class="stepper-btn" title="Tambah">+</a>
                                        </div>
                                    </td>
                                    <td class="cart-subtotal-col"><?php echo format_rupiah($item_subtotal); ?></td>
                                    <td style="text-align: center;">
                                        <a href="keranjang.php?action=remove&cart_id=<?php echo $cart_id; ?>" class="cart-item-del-btn" title="Hapus dari keranjang">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card-Based List (Visible on Mobile Only) -->
                <div class="cart-mobile-cards-wrap">
                    <?php foreach($cart_items as $cart_id => $item): ?>
                        <?php $item_subtotal = $item['harga'] * $item['qty']; ?>
                        <div class="cart-mobile-item-card">
                            <img src="<?php echo htmlspecialchars($item['gambar']); ?>" alt="<?php echo htmlspecialchars($item['nama_produk']); ?>" class="cart-mobile-thumb" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=100&h=100&fit=crop';">
                            <div class="cart-mobile-details">
                                <div class="cart-mobile-title-row">
                                    <a href="detail_produk.php?id=<?php echo $item['product_id']; ?>" class="cart-mobile-title">
                                        <?php echo htmlspecialchars($item['nama_produk']); ?>
                                    </a>
                                    <a href="keranjang.php?action=remove&cart_id=<?php echo $cart_id; ?>" class="cart-mobile-del" title="Hapus item">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                                <div class="cart-variant-tags mb-2">
                                    <?php if(!empty($item['ukuran'])): ?>
                                        <span class="variant-pill">Size <?php echo htmlspecialchars($item['ukuran']); ?></span>
                                    <?php endif; ?>
                                    <?php if(!empty($item['warna'])): ?>
                                        <span class="variant-pill"><?php echo htmlspecialchars($item['warna']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="cart-mobile-bottom-row">
                                    <div class="cart-mobile-price">
                                        <span class="unit-price"><?php echo format_rupiah($item['harga']); ?></span>
                                        <span class="total-price"><?php echo format_rupiah($item_subtotal); ?></span>
                                    </div>
                                    <div class="cart-stepper-box">
                                        <a href="keranjang.php?action=update&cart_id=<?php echo $cart_id; ?>&qty=<?php echo max(1, $item['qty'] - 1); ?>" class="stepper-btn">-</a>
                                        <span class="stepper-val"><?php echo $item['qty']; ?></span>
                                        <a href="keranjang.php?action=update&cart_id=<?php echo $cart_id; ?>&qty=<?php echo min($item['stok'], $item['qty'] + 1); ?>" class="stepper-btn">+</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Back to Shop Link -->
                <div class="cart-continue-shop mt-3">
                    <a href="produk.php" class="continue-link">
                        <i class="fas fa-arrow-left"></i> Tambah Produk Lainnya
                    </a>
                </div>
            </div>
            
            <!-- Cart Order Summary Sidebar -->
            <div class="cart-summary-col">
                <div class="cart-summary-card">
                    <h3 class="summary-heading">Ringkasan Belanja</h3>
                    
                    <div class="summary-line-item">
                        <span class="text-muted">Total Harga (<?php echo $total_items_count; ?> barang)</span>
                        <span class="font-600"><?php echo format_rupiah($subtotal); ?></span>
                    </div>
                    
                    <div class="summary-line-item">
                        <span class="text-muted">Diskon Toko</span>
                        <span class="text-success font-600">- Rp 0</span>
                    </div>

                    <div class="summary-line-item">
                        <span class="text-muted">Estimasi Ongkos Kirim</span>
                        <span class="shipping-calc-tag">Dihitung saat checkout</span>
                    </div>
                    
                    <div class="summary-divider"></div>

                    <div class="summary-total-line">
                        <div>
                            <div class="total-label">Total Pembayaran</div>
                            <div class="total-note text-muted">Termasuk PPN jika berlaku</div>
                        </div>
                        <div class="total-grand-amount"><?php echo format_rupiah($subtotal); ?></div>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-primary btn-full btn-shimmer summary-checkout-btn">
                        <span>Lanjut ke Checkout</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>

                    <!-- Security & Trust Badges -->
                    <div class="cart-trust-features">
                        <div class="trust-pill-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Transaksi 100% Aman & Terenkripsi</span>
                        </div>
                        <div class="trust-pill-item">
                            <i class="fas fa-sync-alt"></i>
                            <span>Garansi Retur 7 Hari Produk Cacat</span>
                        </div>
                        <div class="trust-pill-item">
                            <i class="fas fa-shipping-fast"></i>
                            <span>Pengiriman Cepat Seluruh Indonesia</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Mobile Checkout Bar (Visible only on Mobile Screens) -->
        <div class="mobile-sticky-cart-bar" id="mobileStickyCartBar">
            <div class="mobile-sticky-cart-left">
                <div class="sticky-cart-label">Total (<?php echo $total_items_count; ?> item):</div>
                <div class="sticky-cart-total"><?php echo format_rupiah($subtotal); ?></div>
            </div>
            <a href="checkout.php" class="btn btn-primary mobile-sticky-checkout-btn">
                <span>Checkout</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
