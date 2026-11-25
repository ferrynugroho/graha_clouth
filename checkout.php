<?php
require_once 'includes/header.php';
require_once 'includes/navbar.php';

if (!is_logged_in()) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    echo "<script>alert('Silakan login terlebih dahulu untuk melanjutkan checkout.'); window.location.href='login.php';</script>";
    exit;
}

$is_user = is_logged_in() && $_SESSION['role'] == 'customer';
$user_id = $is_user ? $_SESSION['user_id'] : null;

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
} else {
    if (isset($_SESSION['cart'])) {
        $cart_items = $_SESSION['cart'];
    }
}

if (empty($cart_items)) {
    header("Location: keranjang.php");
    exit;
}

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += ($item['harga'] * $item['qty']);
}

// Get user data for auto-fill
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch();
$total_items_count = 0;
foreach ($cart_items as $item) {
    $total_items_count += $item['qty'];
}
?>

<div class="container section-sm">

    <!-- Progress Checkout Stepper -->
    <div class="checkout-stepper-wrap">
        <a href="keranjang.php" class="checkout-step-node completed">
            <div class="step-num"><i class="fas fa-check"></i></div>
            <span class="step-txt">Keranjang</span>
        </a>
        <div class="step-divider completed"></div>
        <div class="checkout-step-node active">
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
        <h1 class="page-main-heading">Informasi Checkout</h1>
        <p class="page-sub-heading text-muted">Lengkapi alamat pengiriman dan pilih metode pembayaran favorit Anda.</p>
    </div>
    
    <form action="proses_checkout.php" method="POST" id="checkoutForm">
        <input type="hidden" name="checkout" value="1">
        
        <div class="checkout-layout-grid">
            <!-- Left Details Column -->
            <div class="checkout-forms-col">
                
                <!-- Section 1: Alamat Pengiriman -->
                <div class="checkout-card-panel">
                    <div class="panel-header-row">
                        <div class="panel-icon-circle"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <h3 class="panel-title">1. Alamat Pengiriman</h3>
                            <p class="panel-subtitle">Pastikan data penerima dan alamat pengiriman sudah lengkap & tepat</p>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Nama Lengkap Penerima <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($user['nama']); ?>" placeholder="Nama lengkap penerima paket" required>
                    </div>
                    
                    <div class="form-row-grid mb-3">
                        <div class="form-group">
                            <label class="form-label">Nomor WhatsApp Aktif <span class="text-danger">*</span></label>
                            <input type="tel" name="no_whatsapp" class="form-control" value="<?php echo htmlspecialchars($user['no_whatsapp']); ?>" placeholder="08xxxxxxxxxx" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alamat Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly style="background-color: #F8FAFC; color: #64748B;">
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Alamat Lengkap (Jalan, RT/RW, No. Rumah, Patokan) <span class="text-danger">*</span></label>
                        <textarea name="alamat" class="form-control" rows="3" placeholder="Contoh: Jl. Diponegoro No. 45, RT 02/RW 03, Dekat Masjid Al-Ikhlas" required></textarea>
                    </div>
                    
                    <div class="form-row-grid mb-3">
                        <div class="form-group">
                            <label class="form-label">Provinsi <span class="text-danger">*</span></label>
                            <input type="text" name="provinsi" class="form-control" placeholder="Contoh: Jawa Tengah" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kabupaten / Kota <span class="text-danger">*</span></label>
                            <input type="text" name="kabupaten" class="form-control" placeholder="Contoh: Boyolali" required>
                        </div>
                    </div>
                    
                    <div class="form-row-grid">
                        <div class="form-group">
                            <label class="form-label">Kecamatan <span class="text-danger">*</span></label>
                            <input type="text" name="kecamatan" class="form-control" placeholder="Contoh: Mojosongo" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kode Pos <span class="text-danger">*</span></label>
                            <input type="text" name="kode_pos" class="form-control" placeholder="Contoh: 57322" required>
                        </div>
                    </div>
                </div>
                
                <!-- Section 2: Metode Pengiriman -->
                <div class="checkout-card-panel">
                    <div class="panel-header-row">
                        <div class="panel-icon-circle"><i class="fas fa-truck-fast"></i></div>
                        <div>
                            <h3 class="panel-title">2. Jasa Ekspedisi / Pengiriman</h3>
                            <p class="panel-subtitle">Pilih kurir pengiriman terpercaya untuk pesanan Anda</p>
                        </div>
                    </div>
                    
                    <div class="selection-cards-grid">
                        <label class="selection-card">
                            <input type="radio" name="metode_pengiriman" value="J&T" checked onchange="updateShipping(20000)">
                            <div class="selection-card-content">
                                <div class="selection-icon-brand"><i class="fas fa-shipping-fast"></i></div>
                                <div class="selection-meta">
                                    <div class="selection-name">J&T Express</div>
                                    <div class="selection-desc">Estimasi 1-3 hari kerja</div>
                                </div>
                                <div class="selection-price">Rp 20.000</div>
                            </div>
                        </label>

                        <label class="selection-card">
                            <input type="radio" name="metode_pengiriman" value="JNE" onchange="updateShipping(20000)">
                            <div class="selection-card-content">
                                <div class="selection-icon-brand"><i class="fas fa-box"></i></div>
                                <div class="selection-meta">
                                    <div class="selection-name">JNE Reguler</div>
                                    <div class="selection-desc">Estimasi 2-4 hari kerja</div>
                                </div>
                                <div class="selection-price">Rp 20.000</div>
                            </div>
                        </label>

                        <label class="selection-card">
                            <input type="radio" name="metode_pengiriman" value="SiCepat" onchange="updateShipping(22000)">
                            <div class="selection-card-content">
                                <div class="selection-icon-brand"><i class="fas fa-bolt"></i></div>
                                <div class="selection-meta">
                                    <div class="selection-name">SiCepat REG</div>
                                    <div class="selection-desc">Estimasi 1-3 hari kerja</div>
                                </div>
                                <div class="selection-price">Rp 22.000</div>
                            </div>
                        </label>

                        <label class="selection-card">
                            <input type="radio" name="metode_pengiriman" value="Ambil di toko" onchange="updateShipping(0)">
                            <div class="selection-card-content">
                                <div class="selection-icon-brand"><i class="fas fa-store"></i></div>
                                <div class="selection-meta">
                                    <div class="selection-name">Ambil Langsung di Toko</div>
                                    <div class="selection-desc">SMK Karya Nugraha Boyolali</div>
                                </div>
                                <div class="selection-price free-tag">GRATIS</div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Section 3: Metode Pembayaran -->
                <div class="checkout-card-panel">
                    <div class="panel-header-row">
                        <div class="panel-icon-circle"><i class="fas fa-credit-card"></i></div>
                        <div>
                            <h3 class="panel-title">3. Metode Pembayaran</h3>
                            <p class="panel-subtitle">Pilih metode transfer, QRIS, atau bayar di tempat (COD)</p>
                        </div>
                    </div>
                    
                    <div class="selection-cards-grid">
                        <label class="selection-card">
                            <input type="radio" name="metode_pembayaran" value="QRIS" checked>
                            <div class="selection-card-content">
                                <div class="selection-icon-brand"><i class="fas fa-qrcode"></i></div>
                                <div class="selection-meta">
                                    <div class="selection-name">QRIS Instant</div>
                                    <div class="selection-desc">BCA, Mandiri, GoPay, OVO, Dana, ShopeePay</div>
                                </div>
                                <span class="instant-pill">Instan</span>
                            </div>
                        </label>

                        <label class="selection-card">
                            <input type="radio" name="metode_pembayaran" value="Transfer Bank">
                            <div class="selection-card-content">
                                <div class="selection-icon-brand"><i class="fas fa-university"></i></div>
                                <div class="selection-meta">
                                    <div class="selection-name">Transfer Bank Manual</div>
                                    <div class="selection-desc">BCA, Mandiri, BRI (Konfirmasi via Admin)</div>
                                </div>
                            </div>
                        </label>

                        <label class="selection-card">
                            <input type="radio" name="metode_pembayaran" value="E-Wallet">
                            <div class="selection-card-content">
                                <div class="selection-icon-brand"><i class="fas fa-wallet"></i></div>
                                <div class="selection-meta">
                                    <div class="selection-name">E-Wallet Direct</div>
                                    <div class="selection-desc">GoPay / ShopeePay / Dana / OVO</div>
                                </div>
                            </div>
                        </label>

                        <label class="selection-card">
                            <input type="radio" name="metode_pembayaran" value="COD">
                            <div class="selection-card-content">
                                <div class="selection-icon-brand"><i class="fas fa-hand-holding-usd"></i></div>
                                <div class="selection-meta">
                                    <div class="selection-name">COD (Bayar di Tempat)</div>
                                    <div class="selection-desc">Bayar tunai ke kurir saat paket tiba</div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary Sticky Sidebar -->
            <div class="checkout-summary-col">
                <div class="checkout-summary-card">
                    <h3 class="summary-heading">Ringkasan Pesanan (<?php echo $total_items_count; ?>)</h3>
                    
                    <!-- Products Item List Preview -->
                    <div class="checkout-items-list">
                        <?php foreach($cart_items as $item): ?>
                            <div class="checkout-item-row">
                                <img src="<?php echo htmlspecialchars($item['gambar']); ?>" alt="<?php echo htmlspecialchars($item['nama_produk']); ?>" class="checkout-item-thumb" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=100&h=100&fit=crop';">
                                <div class="checkout-item-meta">
                                    <div class="checkout-item-name"><?php echo htmlspecialchars($item['nama_produk']); ?></div>
                                    <div class="checkout-item-sub text-muted">
                                        <?php echo $item['qty']; ?>x @ <?php echo format_rupiah($item['harga']); ?>
                                        <?php if($item['ukuran']) echo " | " . htmlspecialchars($item['ukuran']); ?>
                                    </div>
                                </div>
                                <div class="checkout-item-price">
                                    <?php echo format_rupiah($item['harga'] * $item['qty']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-line-item">
                        <span class="text-muted">Subtotal Produk</span>
                        <span class="font-600"><?php echo format_rupiah($subtotal); ?></span>
                    </div>
                    
                    <div class="summary-line-item">
                        <span class="text-muted">Ongkos Kirim</span>
                        <span class="font-600" id="ongkir_display">Rp 20.000</span>
                    </div>

                    <div class="summary-line-item">
                        <span class="text-muted">Biaya Layanan</span>
                        <span class="text-success font-600">GRATIS</span>
                    </div>
                    
                    <div class="summary-divider"></div>

                    <div class="summary-total-line">
                        <div>
                            <div class="total-label">Total Pembayaran</div>
                            <div class="total-note text-muted">Sudah termasuk ongkir</div>
                        </div>
                        <div class="total-grand-amount" id="total_display"><?php echo format_rupiah($subtotal + 20000); ?></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-full btn-shimmer checkout-submit-btn">
                        <i class="fas fa-lock" style="margin-right: 0.4rem;"></i>
                        <span>Bayar & Buat Pesanan</span>
                    </button>

                    <p class="text-center text-muted mt-3" style="font-size: 0.78rem; line-height: 1.4;">
                        Dengan menekan tombol di atas, Anda menyetujui Ketentuan Layanan & Kebijakan Privasi GrahaClouth.
                    </p>
                </div>
            </div>
        </div>

        <!-- Sticky Mobile Bottom Checkout Trigger Bar -->
        <div class="mobile-sticky-cart-bar" id="mobileStickyCheckoutBar">
            <div class="mobile-sticky-cart-left">
                <div class="sticky-cart-label">Total Tagihan:</div>
                <div class="sticky-cart-total" id="mobile_sticky_total"><?php echo format_rupiah($subtotal + 20000); ?></div>
            </div>
            <button type="submit" class="btn btn-primary mobile-sticky-checkout-btn">
                <span>Bayar Sekarang</span>
                <i class="fas fa-check"></i>
            </button>
        </div>
    </form>
</div>

<script>
const baseSubtotal = <?php echo (float)$subtotal; ?>;
let currentOngkir = 20000;

function updateShipping(cost) {
    currentOngkir = cost;
    const total = baseSubtotal + currentOngkir;
    
    document.getElementById('ongkir_display').textContent = (currentOngkir === 0) ? 'GRATIS' : 'Rp ' + currentOngkir.toLocaleString('id-ID');
    document.getElementById('total_display').textContent = 'Rp ' + total.toLocaleString('id-ID');
    const mobileSticky = document.getElementById('mobile_sticky_total');
    if (mobileSticky) mobileSticky.textContent = 'Rp ' + total.toLocaleString('id-ID');
}
</script>

<?php require_once 'includes/footer.php'; ?>
