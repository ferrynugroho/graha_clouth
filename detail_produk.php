<?php
require_once 'includes/header.php';
require_once 'includes/navbar.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT p.*, c.nama_kategori FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = :id AND p.status = 'aktif'");
$stmt->execute([':id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='container section'><div class='alert alert-danger'>Produk tidak ditemukan.</div></div>";
    require_once 'includes/footer.php';
    exit;
}

$ukura_array = array_filter(array_map('trim', explode(',', $product['ukuran'])));
$warna_array = array_filter(array_map('trim', explode(',', $product['warna'])));

// Hitung diskon
$discount_pct = 0;
if ($product['harga_promo'] && $product['harga'] > 0) {
    $discount_pct = round((1 - $product['harga_promo'] / $product['harga']) * 100);
}

// Get WhatsApp number
$whatsapp_number = '6281234567890';
try {
    $s = $conn->query("SELECT whatsapp_number FROM settings LIMIT 1");
    if ($s) { $cfg = $s->fetch(); if ($cfg) $whatsapp_number = $cfg['whatsapp_number'] ?? $whatsapp_number; }
} catch(Exception $e) {}

$wa_text = urlencode("Halo Graha Clouth, saya tertarik dengan produk ini:\n\nNama: {$product['nama_produk']}\nHarga: " . format_rupiah($product['harga_promo'] ?? $product['harga']) . "\n\nApakah masih tersedia?");
$wa_link = "https://wa.me/{$whatsapp_number}?text={$wa_text}";
?>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="index.php">Beranda</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:0.65rem;"></i></span>
            <a href="produk.php">Produk</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:0.65rem;"></i></span>
            <span class="current"><?php echo htmlspecialchars($product['nama_produk']); ?></span>
        </nav>
    </div>
</div>

<!-- Product Detail Section -->
<section class="dp-section">
    <div class="container">
        <div class="dp-grid">

            <!-- Left: Product Image -->
            <div class="dp-img-col" data-reveal>
                <div class="dp-img-main-wrap">
                    <?php if ($discount_pct >= 5): ?>
                    <div class="dp-discount-ribbon">
                        <i class="fas fa-tag"></i> HEMAT <?php echo $discount_pct; ?>%
                    </div>
                    <?php endif; ?>
                    <img src="<?php echo htmlspecialchars($product['gambar']); ?>"
                         alt="<?php echo htmlspecialchars($product['nama_produk']); ?>"
                         class="dp-img-main"
                         id="dpMainImage">
                    <div class="dp-img-overlay-actions">
                        <button class="dp-img-btn dp-wishlist-btn" title="Tambah ke Wishlist" id="dpWishlistBtn" onclick="toggleWishlist(this)">
                            <i class="far fa-heart"></i>
                        </button>
                        <button class="dp-img-btn" title="Bagikan Produk" onclick="shareProduct()">
                            <i class="fas fa-share-alt"></i>
                        </button>
                    </div>
                </div>
                <!-- Trust Badges Below Image -->
                <div class="dp-trust-row">
                    <div class="dp-trust-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Produk Original</span>
                    </div>
                    <div class="dp-trust-item">
                        <i class="fas fa-undo-alt"></i>
                        <span>Retur 7 Hari</span>
                    </div>
                    <div class="dp-trust-item">
                        <i class="fas fa-truck"></i>
                        <span>Gratis Ongkir</span>
                    </div>
                </div>
            </div>

            <!-- Right: Product Info -->
            <div class="dp-info-col" data-reveal data-delay="150">

                <!-- Category Badge -->
                <div class="dp-category-badge">
                    <i class="fas fa-tag"></i>
                    <?php echo htmlspecialchars($product['nama_kategori']); ?>
                </div>

                <!-- Product Name -->
                <h1 class="dp-product-name"><?php echo htmlspecialchars($product['nama_produk']); ?></h1>

                <!-- Rating Row -->
                <div class="dp-rating-row">
                    <div class="dp-stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <span class="dp-rating-text">4.8 (<?php echo rand(24, 158); ?> ulasan)</span>
                    <span class="dp-separator">|</span>
                    <span class="dp-sold-text"><i class="fas fa-fire" style="color:#EF4444;"></i> <?php echo rand(50, 300); ?>+ terjual</span>
                </div>

                <!-- Price Box -->
                <div class="dp-price-box">
                    <?php if ($product['harga_promo']): ?>
                        <div class="dp-price-current"><?php echo format_rupiah($product['harga_promo']); ?></div>
                        <div class="dp-price-meta">
                            <span class="dp-price-original"><?php echo format_rupiah($product['harga']); ?></span>
                            <span class="dp-price-discount-badge">-<?php echo $discount_pct; ?>%</span>
                        </div>
                        <div class="dp-price-saving">Hemat <?php echo format_rupiah($product['harga'] - $product['harga_promo']); ?></div>
                    <?php else: ?>
                        <div class="dp-price-current"><?php echo format_rupiah($product['harga']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Stock Indicator -->
                <div class="dp-stock-row">
                    <?php if ($product['stok'] > 10): ?>
                        <span class="dp-stock-badge dp-stock-available"><i class="fas fa-circle"></i> Tersedia</span>
                        <span class="dp-stock-count">Stok: <strong><?php echo $product['stok']; ?></strong> pcs</span>
                    <?php elseif ($product['stok'] > 0): ?>
                        <span class="dp-stock-badge dp-stock-low"><i class="fas fa-circle"></i> Stok Terbatas</span>
                        <span class="dp-stock-count">Sisa: <strong><?php echo $product['stok']; ?></strong> pcs</span>
                    <?php else: ?>
                        <span class="dp-stock-badge dp-stock-empty"><i class="fas fa-circle"></i> Habis</span>
                    <?php endif; ?>
                </div>

                <!-- Divider -->
                <div class="dp-divider"></div>

                <!-- Add to Cart Form -->
                <form action="keranjang.php" method="GET" class="dp-form">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">

                    <!-- Size Selection -->
                    <?php if (!empty($ukura_array)): ?>
                    <div class="dp-option-group">
                        <div class="dp-option-label">
                            Ukuran
                            <a href="#" class="dp-size-guide" onclick="return false;" title="Panduan Ukuran">
                                <i class="fas fa-ruler-combined"></i> Panduan Ukuran
                            </a>
                        </div>
                        <div class="dp-size-chips">
                            <?php foreach($ukura_array as $index => $uk): ?>
                            <input type="radio" name="ukuran" id="uk_<?php echo $index; ?>" value="<?php echo htmlspecialchars($uk); ?>" class="dp-chip-input" <?php echo $index === 0 ? 'checked' : ''; ?>>
                            <label for="uk_<?php echo $index; ?>" class="dp-size-chip"><?php echo htmlspecialchars($uk); ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Color Selection -->
                    <?php if (!empty($warna_array)): ?>
                    <div class="dp-option-group">
                        <div class="dp-option-label">
                            Warna
                            <span class="dp-selected-color" id="selectedColorLabel"><?php echo htmlspecialchars(reset($warna_array)); ?></span>
                        </div>
                        <div class="dp-color-chips">
                            <?php foreach($warna_array as $index => $wr): ?>
                            <input type="radio" name="warna" id="wr_<?php echo $index; ?>" value="<?php echo htmlspecialchars($wr); ?>" class="dp-chip-input" <?php echo $index === 0 ? 'checked' : ''; ?> onchange="document.getElementById('selectedColorLabel').textContent=this.value">
                            <label for="wr_<?php echo $index; ?>" class="dp-color-chip" title="<?php echo htmlspecialchars($wr); ?>">
                                <span><?php echo htmlspecialchars($wr); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Quantity -->
                    <div class="dp-option-group">
                        <div class="dp-option-label">Jumlah</div>
                        <div class="dp-qty-wrap">
                            <button type="button" class="dp-qty-btn" id="qtyMinus" onclick="changeQty(-1)">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" name="qty" id="qtyInput" class="dp-qty-input" value="1" min="1" max="<?php echo $product['stok']; ?>">
                            <button type="button" class="dp-qty-btn" id="qtyPlus" onclick="changeQty(1)">
                                <i class="fas fa-plus"></i>
                            </button>
                            <span class="dp-qty-max">Maks. <?php echo $product['stok']; ?></span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="dp-actions">
                        <button type="submit" class="dp-btn-cart btn-shimmer" <?php echo $product['stok'] <= 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-bag"></i>
                            <span><?php echo $product['stok'] <= 0 ? 'Stok Habis' : 'Tambah ke Keranjang'; ?></span>
                        </button>
                        <button type="button" class="dp-btn-chat" onclick="askProductInChat('<?php echo htmlspecialchars(addslashes($product['nama_produk'])); ?>', '<?php echo format_rupiah($product['harga_promo'] ?: $product['harga']); ?>', '<?php echo htmlspecialchars(addslashes($product['gambar'])); ?>', <?php echo $product['id']; ?>)">
                            <i class="fas fa-comment-dots"></i>
                            <span>Tanya CS</span>
                        </button>
                        <a href="<?php echo $wa_link; ?>" target="_blank" class="dp-btn-wa">
                            <i class="fab fa-whatsapp"></i>
                            <span>WhatsApp</span>
                        </a>
                    </div>
                </form>

                <!-- Description -->
                <div class="dp-desc-section">
                    <button class="dp-desc-toggle" id="dpDescToggle" onclick="toggleDesc()">
                        <span><i class="fas fa-align-left"></i> Deskripsi Produk</span>
                        <i class="fas fa-chevron-down dp-desc-arrow" id="dpDescArrow"></i>
                    </button>
                    <div class="dp-desc-body" id="dpDescBody">
                        <p><?php echo nl2br(htmlspecialchars($product['deskripsi'])); ?></p>
                        <div class="dp-spec-grid">
                            <div class="dp-spec-item">
                                <span class="dp-spec-label">Kategori</span>
                                <span class="dp-spec-val"><?php echo htmlspecialchars($product['nama_kategori']); ?></span>
                            </div>
                            <div class="dp-spec-item">
                                <span class="dp-spec-label">Stok</span>
                                <span class="dp-spec-val"><?php echo $product['stok']; ?> pcs</span>
                            </div>
                            <?php if (!empty($ukura_array)): ?>
                            <div class="dp-spec-item">
                                <span class="dp-spec-label">Ukuran</span>
                                <span class="dp-spec-val"><?php echo implode(', ', $ukura_array); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($warna_array)): ?>
                            <div class="dp-spec-item">
                                <span class="dp-spec-label">Warna</span>
                                <span class="dp-spec-val"><?php echo implode(', ', $warna_array); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div><!-- end dp-info-col -->
        </div><!-- end dp-grid -->
    </div>
</section>

<!-- Mobile Sticky Add to Cart Bar -->
<div class="dp-mobile-sticky" id="dpMobileSticky">
    <div class="dp-sticky-info">
        <div class="dp-sticky-name"><?php echo htmlspecialchars(substr($product['nama_produk'], 0, 28)) . (strlen($product['nama_produk']) > 28 ? '...' : ''); ?></div>
        <div class="dp-sticky-price">
            <?php echo format_rupiah($product['harga_promo'] ?: $product['harga']); ?>
        </div>
    </div>
    <a href="keranjang.php?action=add&id=<?php echo $product['id']; ?>&qty=1&ukuran=<?php echo htmlspecialchars($ukura_array ? reset($ukura_array) : ''); ?>&warna=<?php echo htmlspecialchars($warna_array ? reset($warna_array) : ''); ?>"
       class="dp-sticky-btn <?php echo $product['stok'] <= 0 ? 'disabled' : ''; ?>">
        <i class="fas fa-shopping-bag"></i> Beli Sekarang
    </a>
</div>

<style>
/* ===== DETAIL PRODUK PREMIUM STYLES ===== */
.dp-section {
    padding: 2.5rem 0 5rem;
    background: linear-gradient(135deg, #FEFEFE 0%, #F8FAFF 100%);
    min-height: 70vh;
}
.dp-grid {
    display: grid;
    grid-template-columns: 1fr 1.1fr;
    gap: 4rem;
    align-items: start;
}

/* Image Column */
.dp-img-col { position: sticky; top: 90px; }
.dp-img-main-wrap {
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    background: #F1F5F9;
    box-shadow: 0 20px 60px rgba(0,0,0,0.10);
    aspect-ratio: 3/4;
}
.dp-img-main {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.dp-img-main-wrap:hover .dp-img-main { transform: scale(1.04); }
.dp-discount-ribbon {
    position: absolute;
    top: 18px;
    left: -2px;
    background: linear-gradient(90deg, #EF4444, #DC2626);
    color: white;
    padding: 0.4rem 1rem 0.4rem 0.85rem;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.5px;
    border-radius: 0 20px 20px 0;
    z-index: 3;
    box-shadow: 0 4px 12px rgba(239,68,68,0.35);
}
.dp-img-overlay-actions {
    position: absolute;
    top: 14px;
    right: 14px;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    z-index: 3;
}
.dp-img-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.92);
    backdrop-filter: blur(8px);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    color: #475569;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    transition: all 0.2s ease;
}
.dp-img-btn:hover { background: white; color: var(--accent); transform: scale(1.1); }
.dp-img-btn.wishlisted { color: #EF4444; }

/* Trust Row */
.dp-trust-row {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    justify-content: space-between;
}
.dp-trust-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.3rem;
    background: white;
    border: 1px solid #E8EAED;
    border-radius: 12px;
    padding: 0.65rem 0.5rem;
    font-size: 0.72rem;
    color: #475569;
    font-weight: 500;
    text-align: center;
}
.dp-trust-item i { color: var(--accent); font-size: 1.1rem; }

/* Info Column */
.dp-info-col { padding-top: 0.5rem; }

.dp-category-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: var(--accent-light, #EFF6FF);
    color: var(--accent, #2563EB);
    border: 1px solid var(--accent-mid, #BFDBFE);
    border-radius: 999px;
    padding: 0.3rem 0.85rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 0.85rem;
}

.dp-product-name {
    font-size: 2rem;
    font-weight: 900;
    color: #0F172A;
    line-height: 1.2;
    margin-bottom: 0.85rem;
    letter-spacing: -0.5px;
}

/* Rating */
.dp-rating-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}
.dp-stars { color: #F59E0B; font-size: 0.82rem; display: flex; gap: 2px; }
.dp-rating-text { font-size: 0.82rem; color: #475569; font-weight: 500; }
.dp-separator { color: #CBD5E1; }
.dp-sold-text { font-size: 0.78rem; color: #64748B; display: flex; align-items: center; gap: 0.3rem; }

/* Price Box */
.dp-price-box {
    background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
    border: 1px solid #BFDBFE;
    border-radius: 18px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
}
.dp-price-current {
    font-size: 2.2rem;
    font-weight: 900;
    color: var(--accent, #2563EB);
    line-height: 1;
    letter-spacing: -1px;
}
.dp-price-meta { display: flex; align-items: center; gap: 0.65rem; margin-top: 0.4rem; }
.dp-price-original { font-size: 0.95rem; color: #94A3B8; text-decoration: line-through; font-weight: 500; }
.dp-price-discount-badge {
    background: #EF4444;
    color: white;
    font-size: 0.72rem;
    font-weight: 800;
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
}
.dp-price-saving { font-size: 0.78rem; color: #059669; font-weight: 600; margin-top: 0.3rem; }

/* Stock */
.dp-stock-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem; }
.dp-stock-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
}
.dp-stock-badge i { font-size: 0.45rem; }
.dp-stock-available { background: #D1FAE5; color: #065F46; }
.dp-stock-low { background: #FEF3C7; color: #92400E; }
.dp-stock-empty { background: #FEE2E2; color: #991B1B; }
.dp-stock-count { font-size: 0.82rem; color: #64748B; }

.dp-divider { height: 1px; background: #E2E8F0; margin: 1.25rem 0; }

/* Form Options */
.dp-form { display: flex; flex-direction: column; gap: 1.15rem; }

.dp-option-group { display: flex; flex-direction: column; gap: 0.6rem; }
.dp-option-label {
    font-size: 0.85rem;
    font-weight: 700;
    color: #334155;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.dp-size-guide {
    font-size: 0.75rem;
    color: var(--accent, #2563EB);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* Size Chips */
.dp-size-chips, .dp-color-chips { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.dp-chip-input { display: none; }

.dp-size-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 48px;
    height: 42px;
    padding: 0 1rem;
    border: 2px solid #E2E8F0;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 700;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
}
.dp-size-chip:hover { border-color: var(--accent); color: var(--accent); background: #EFF6FF; }
.dp-chip-input:checked + .dp-size-chip {
    background: var(--accent);
    border-color: var(--accent);
    color: white;
    box-shadow: 0 4px 12px rgba(37,99,235,0.3);
}

.dp-color-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.4rem 1rem;
    border: 2px solid #E2E8F0;
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
}
.dp-color-chip:hover { border-color: var(--accent); color: var(--accent); }
.dp-chip-input:checked + .dp-color-chip {
    border-color: var(--accent);
    color: var(--accent);
    background: #EFF6FF;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
}
.dp-selected-color { font-size: 0.78rem; color: #64748B; font-weight: 500; }

/* Quantity */
.dp-qty-wrap { display: flex; align-items: center; gap: 0; }
.dp-qty-btn {
    width: 40px;
    height: 40px;
    border: 1.5px solid #E2E8F0;
    background: white;
    color: #475569;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}
.dp-qty-btn:first-child { border-radius: 10px 0 0 10px; }
.dp-qty-btn:last-of-type { border-radius: 0 10px 10px 0; }
.dp-qty-btn:hover { background: var(--accent); color: white; border-color: var(--accent); }
.dp-qty-input {
    width: 56px;
    height: 40px;
    border: 1.5px solid #E2E8F0;
    border-left: none;
    border-right: none;
    text-align: center;
    font-size: 1rem;
    font-weight: 700;
    color: #0F172A;
    font-family: inherit;
    outline: none;
    background: white;
}
.dp-qty-max { font-size: 0.75rem; color: #94A3B8; margin-left: 0.75rem; }

/* Action Buttons */
.dp-actions { display: flex; gap: 0.65rem; margin-top: 0.5rem; flex-wrap: wrap; }

.dp-btn-cart {
    flex: 1;
    min-width: 180px;
    padding: 1rem 1.5rem;
    background: var(--accent, #2563EB);
    color: white;
    border: none;
    border-radius: 14px;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    transition: all 0.25s ease;
    box-shadow: 0 4px 16px rgba(37,99,235,0.3);
}
.dp-btn-cart:hover:not(:disabled) {
    background: #1D4ED8;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(37,99,235,0.4);
}
.dp-btn-cart:disabled { opacity: 0.5; cursor: not-allowed; }

.dp-btn-chat {
    padding: 1rem 1.1rem;
    background: #EFF6FF;
    color: var(--accent, #2563EB);
    border: 1.5px solid #BFDBFE;
    border-radius: 14px;
    font-size: 0.88rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.2s ease;
}
.dp-btn-chat:hover { background: #DBEAFE; border-color: var(--accent); transform: translateY(-1px); }

.dp-btn-wa {
    padding: 1rem 1.1rem;
    background: #25D366;
    color: white;
    border: none;
    border-radius: 14px;
    font-size: 0.88rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.2s ease;
    text-decoration: none;
}
.dp-btn-wa:hover { background: #1ebe5a; color: white; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(37,211,102,0.35); }

/* Description Section */
.dp-desc-section { margin-top: 1.5rem; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; }
.dp-desc-toggle {
    width: 100%;
    padding: 1rem 1.25rem;
    background: #F8FAFC;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.9rem;
    font-weight: 700;
    color: #0F172A;
    font-family: inherit;
    transition: background 0.2s ease;
}
.dp-desc-toggle:hover { background: #F1F5F9; }
.dp-desc-toggle span { display: flex; align-items: center; gap: 0.5rem; }
.dp-desc-toggle span i { color: var(--accent); }
.dp-desc-arrow { transition: transform 0.3s ease; color: #94A3B8; }
.dp-desc-arrow.open { transform: rotate(180deg); }
.dp-desc-body {
    padding: 1.25rem;
    background: white;
    font-size: 0.88rem;
    color: #475569;
    line-height: 1.8;
    display: none;
}
.dp-desc-body.open { display: block; }

.dp-spec-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem; margin-top: 1.25rem; }
.dp-spec-item {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    padding: 0.65rem 0.85rem;
}
.dp-spec-label { font-size: 0.72rem; color: #94A3B8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.15rem; display: block; }
.dp-spec-val { font-size: 0.85rem; color: #0F172A; font-weight: 600; }

/* Mobile Sticky Bar */
.dp-mobile-sticky {
    display: none;
    position: fixed;
    bottom: 64px;
    left: 0;
    right: 0;
    background: white;
    border-top: 1px solid #E2E8F0;
    padding: 0.75rem 1rem;
    z-index: 9990;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
    align-items: center;
    gap: 0.75rem;
}
.dp-sticky-info { flex: 1; min-width: 0; }
.dp-sticky-name { font-size: 0.8rem; font-weight: 600; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.dp-sticky-price { font-size: 1.1rem; font-weight: 900; color: var(--accent, #2563EB); }
.dp-sticky-btn {
    padding: 0.75rem 1.35rem;
    background: var(--accent, #2563EB);
    color: white;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 700;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.2s ease;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(37,99,235,0.3);
    flex-shrink: 0;
}
.dp-sticky-btn:hover { background: #1D4ED8; color: white; transform: translateY(-1px); }
.dp-sticky-btn.disabled { background: #CBD5E1; pointer-events: none; }

/* ===== RESPONSIVE ===== */
@media (max-width: 900px) {
    .dp-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    .dp-img-col { position: static; }
    .dp-img-main-wrap { max-width: 480px; margin: 0 auto; }
    .dp-product-name { font-size: 1.6rem; }
}

@media (max-width: 640px) {
    .dp-section { padding: 1.5rem 0 6rem; }
    .dp-product-name { font-size: 1.4rem; }
    .dp-price-current { font-size: 1.75rem; }
    .dp-price-box { padding: 1rem; }
    .dp-mobile-sticky { display: flex; }
    .dp-actions .dp-btn-cart { min-width: unset; }
    .dp-spec-grid { grid-template-columns: 1fr; }
    .dp-trust-row { gap: 0.4rem; }
    .dp-trust-item { font-size: 0.68rem; padding: 0.5rem 0.3rem; }
}
</style>

<script>
function changeQty(delta) {
    const inp = document.getElementById('qtyInput');
    let val = parseInt(inp.value) + delta;
    const max = parseInt(inp.getAttribute('max'));
    if (val < 1) val = 1;
    if (val > max) val = max;
    inp.value = val;
}
function toggleDesc() {
    const body = document.getElementById('dpDescBody');
    const arrow = document.getElementById('dpDescArrow');
    body.classList.toggle('open');
    arrow.classList.toggle('open');
}
function toggleWishlist(btn) {
    btn.classList.toggle('wishlisted');
    const icon = btn.querySelector('i');
    if (btn.classList.contains('wishlisted')) {
        icon.classList.replace('far', 'fas');
        icon.style.color = '#EF4444';
    } else {
        icon.classList.replace('fas', 'far');
        icon.style.color = '';
    }
}
function shareProduct() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo htmlspecialchars(addslashes($product['nama_produk'])); ?>',
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Link produk berhasil disalin!');
        });
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
