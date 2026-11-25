<?php
require_once 'includes/header.php';
require_once 'includes/navbar.php';

// Get Latest Products
$stmt = $conn->prepare("SELECT p.*, c.nama_kategori FROM products p JOIN categories c ON p.category_id = c.id WHERE p.status = 'aktif' ORDER BY p.created_at DESC LIMIT 8");
$stmt->execute();
$latest_products = $stmt->fetchAll();

// Get Featured Products (with promo)
$stmt = $conn->prepare("SELECT p.*, c.nama_kategori FROM products p JOIN categories c ON p.category_id = c.id WHERE p.status = 'aktif' AND p.harga_promo IS NOT NULL ORDER BY p.created_at DESC LIMIT 4");
$stmt->execute();
$featured_products = $stmt->fetchAll();

// Get All Active Products for interactive tabs
$stmt = $conn->prepare("SELECT p.*, c.nama_kategori FROM products p JOIN categories c ON p.category_id = c.id WHERE p.status = 'aktif' ORDER BY p.created_at DESC LIMIT 12");
$stmt->execute();
$all_showcase_products = $stmt->fetchAll();

// Get Categories with product count and latest product sample image
$stmt = $conn->query("
    SELECT c.*, COUNT(p.id) as total_produk,
           (SELECT gambar FROM products WHERE category_id = c.id AND status = 'aktif' AND gambar IS NOT NULL AND gambar != '' ORDER BY id DESC LIMIT 1) as sample_gambar
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id AND p.status = 'aktif' 
    GROUP BY c.id 
    ORDER BY total_produk DESC 
    LIMIT 4
");
$categories = $stmt->fetchAll();

// Get all categories for filter chips
$stmt_all_cats = $conn->query("SELECT * FROM categories ORDER BY nama_kategori ASC");
$all_categories_list = $stmt_all_cats->fetchAll();
?>

<!-- ===== HERO SECTION (HAUTE FASHION 2026) ===== -->
<section class="hero" style="position: relative; overflow: hidden; padding: 4.5rem 0 3.5rem;">
    <div class="container">
        <div class="hero-container" style="align-items: center;">

            <!-- Hero Content -->
            <div class="hero-content" data-reveal>
                <div class="hero-badge" style="background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%); color: var(--accent); border: 1px solid var(--accent-mid); display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.45rem 1rem; border-radius: 9999px; font-weight: 700; font-size: 0.82rem; margin-bottom: 1.5rem; letter-spacing: 0.5px;">
                    <i class="fas fa-crown" style="color: #F59E0B;"></i>
                    KOLEKSI EKSKLUSIF 2026
                </div>

                <h1 class="hero-title" style="font-size: 3.4rem; font-weight: 900; line-height: 1.15; letter-spacing: -1px; margin-bottom: 1.25rem;">
                    Tampil Berkelas dengan
                    <span class="accent-word" style="background: linear-gradient(135deg, var(--accent) 0%, #1D4ED8 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Fashion Modern</span>
                </h1>

                <p class="hero-subtitle" style="font-size: 1.08rem; line-height: 1.7; color: var(--muted); max-width: 540px; margin-bottom: 2rem;">
                    Eksplorasi pakaian berkualitas premium dengan siluet modern, material katun combed pilihan, dan kenyamanan maksimal untuk menyempurnakan setiap penampilanmu.
                </p>

                <div class="hero-cta" style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 2.5rem;">
                    <a href="produk.php" class="btn btn-primary btn-xl btn-shimmer" style="box-shadow: 0 8px 24px rgba(37, 99, 235, 0.35);">
                        <i class="fas fa-shopping-bag"></i> Belanja Sekarang
                    </a>
                    <a href="produk.php?promo=1" class="btn btn-ghost btn-xl" style="background: var(--white); border: 1.5px solid var(--border); box-shadow: var(--shadow-sm);">
                        <i class="fas fa-fire text-accent" style="color: #EF4444;"></i> Promo Spesial
                    </a>
                </div>

                <!-- Credibility Stats -->
                <div class="hero-stats" style="padding-top: 1.5rem; border-top: 1px solid var(--border); display: flex; gap: 2.5rem;">
                    <div>
                        <div class="hero-stat-value" style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">2.5K+</div>
                        <div class="hero-stat-label" style="font-size: 0.8rem; color: var(--muted);">Model Pilihan</div>
                    </div>
                    <div>
                        <div class="hero-stat-value" style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">15K+</div>
                        <div class="hero-stat-label" style="font-size: 0.8rem; color: var(--muted);">Pelanggan Puas</div>
                    </div>
                    <div>
                        <div class="hero-stat-value" style="font-size: 1.6rem; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: 0.25rem;">
                            4.9 <i class="fas fa-star" style="color: #F59E0B; font-size: 1.1rem;"></i>
                        </div>
                        <div class="hero-stat-label" style="font-size: 0.8rem; color: var(--muted);">Rating Kepuasan</div>
                    </div>
                </div>
            </div>

            <!-- Hero Main Visual -->
            <div class="hero-image" data-reveal data-delay="200">
                <div class="hero-img-wrap" style="position: relative;">
                    <div class="hero-glow" style="position: absolute; inset: -15px; background: radial-gradient(circle, rgba(37,99,235,0.2) 0%, rgba(37,99,235,0) 70%); border-radius: 36px; z-index: 0;"></div>
                    
                    <img src="https://images.unsplash.com/photo-1509631179647-0177331693ae?w=800&h=1000&fit=crop&crop=top"
                         alt="GrahaClouth Luxury Fashion" 
                         class="hero-main-img" 
                         style="border-radius: 28px; box-shadow: 0 20px 50px rgba(0,0,0,0.14); position: relative; z-index: 1;">

                    <!-- Floating Card 1: Kualitas Premium -->
                    <div class="hero-float-card card-1" style="z-index: 3; background: rgba(255,255,255,0.92); backdrop-filter: blur(10px); border-radius: 16px; border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 12px 28px rgba(0,0,0,0.12);">
                        <div class="hero-float-icon" style="background: #EFF6FF; color: var(--accent);"><i class="fas fa-shield-alt"></i></div>
                        <div>
                            <div style="font-size: 0.85rem; font-weight: 700; color: var(--dark);">100% Premium Cotton</div>
                            <div style="font-size: 0.74rem; color: var(--muted);">Combed 30s Halus & Adem</div>
                        </div>
                    </div>

                    <!-- Floating Card 2: Gratis Ongkir -->
                    <div class="hero-float-card card-2" style="z-index: 3; background: rgba(255,255,255,0.92); backdrop-filter: blur(10px); border-radius: 16px; border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 12px 28px rgba(0,0,0,0.12);">
                        <div class="hero-float-icon" style="background: #FEF3C7; color: #D97706;"><i class="fas fa-truck-fast"></i></div>
                        <div>
                            <div style="font-size: 0.85rem; font-weight: 700; color: var(--dark);">Gratis Ongkir Express</div>
                            <div style="font-size: 0.74rem; color: var(--muted);">Seluruh Indonesia</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ===== INFINITE LUXURY MARQUEE ===== -->
<div class="luxury-marquee-wrap">
    <div class="luxury-marquee-track">
        <div class="marquee-item"><span>NEW COLLECTION 2026</span> <i class="fas fa-sparkles"></i></div>
        <div class="marquee-item"><span>100% ORIGINAL APPAREL</span> <i class="fas fa-star"></i></div>
        <div class="marquee-item"><span>PREMIUM COMBED COTTON 30S</span> <i class="fas fa-sparkles"></i></div>
        <div class="marquee-item"><span>FREE ONGKIR EXPRESS</span> <i class="fas fa-star"></i></div>
        <div class="marquee-item"><span>GARANSI 7 HARI RETUR</span> <i class="fas fa-sparkles"></i></div>
        <div class="marquee-item"><span>BAYAR DI TEMPAT (COD) READY</span> <i class="fas fa-star"></i></div>
        <!-- Duplicated for continuous loop -->
        <div class="marquee-item"><span>NEW COLLECTION 2026</span> <i class="fas fa-sparkles"></i></div>
        <div class="marquee-item"><span>100% ORIGINAL APPAREL</span> <i class="fas fa-star"></i></div>
        <div class="marquee-item"><span>PREMIUM COMBED COTTON 30S</span> <i class="fas fa-sparkles"></i></div>
        <div class="marquee-item"><span>FREE ONGKIR EXPRESS</span> <i class="fas fa-star"></i></div>
        <div class="marquee-item"><span>GARANSI 7 HARI RETUR</span> <i class="fas fa-sparkles"></i></div>
        <div class="marquee-item"><span>BAYAR DI TEMPAT (COD) READY</span> <i class="fas fa-star"></i></div>
    </div>
</div>

<!-- ===== BELANJA BERDASARKAN KATEGORI (BENTO GRID) ===== -->
<section class="section">
    <div class="container">
        <div class="section-header text-center" data-reveal style="margin-bottom: 3rem;">
            <span class="section-label">Koleksi Terpopuler</span>
            <h2 class="section-title">Belanja Berdasarkan <span class="text-accent">Kategori</span></h2>
            <p class="section-subtitle" style="max-width: 520px; margin: 0 auto;">Pilih ragam busana fashion modern yang sesuai dengan karakter gaya harian Anda</p>
        </div>

        <div class="category-bento-grid">
            <?php
            if (empty($categories)):
                $dummy_cats = [
                    ['id'=>1,'nama_kategori'=>'Kaos','total_produk'=>0],
                    ['id'=>2,'nama_kategori'=>'Kemeja','total_produk'=>0],
                    ['id'=>3,'nama_kategori'=>'Celana','total_produk'=>0],
                    ['id'=>4,'nama_kategori'=>'Jaket','total_produk'=>0],
                ];
                $categories = $dummy_cats;
            endif;
            foreach($categories as $idx => $cat):
                $img = getCategoryRealImage($cat['nama_kategori'], $cat['sample_gambar'] ?? null);
            ?>
            <a href="produk.php?kategori=<?php echo $cat['id']; ?>" class="category-bento-card" data-reveal data-delay="<?php echo $idx * 100; ?>">
                <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($cat['nama_kategori']); ?>" class="cat-bg-img" loading="lazy">
                <div class="cat-gradient-overlay"></div>
                
                <div class="category-bento-content">
                    <span class="category-bento-tag">
                        <?php echo $cat['total_produk'] > 0 ? $cat['total_produk'] . ' Produk' : 'Koleksi Baru'; ?>
                    </span>
                    <h3 class="category-bento-title"><?php echo htmlspecialchars($cat['nama_kategori']); ?></h3>
                    <div class="category-bento-action">
                        <span>Lihat Koleksi</span>
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== SHOWCASE PRODUK TERBARU & TABS INTERAKTIF ===== -->
<section class="section" style="background: var(--light);">
    <div class="container">
        <div class="section-header text-center" data-reveal style="margin-bottom: 2rem;">
            <span class="section-label">Rilisan Terkini</span>
            <h2 class="section-title">Produk <span class="text-accent">Terbaru & Pilihan</span></h2>
            <p class="section-subtitle" style="max-width: 500px; margin: 0 auto;">Tren fashion mutakhir dengan potongan presisi dan material premium</p>
        </div>

        <!-- Filter Category Tabs -->
        <div class="filter-tabs-container" data-reveal>
            <button class="filter-tab-btn active" onclick="filterProducts('all', this)">
                <i class="fas fa-th-large" style="margin-right: 0.35rem;"></i> Semua Produk
            </button>
            <?php foreach($all_categories_list as $c_tab): ?>
                <button class="filter-tab-btn" onclick="filterProducts('<?php echo $c_tab['id']; ?>', this)">
                    <?php echo htmlspecialchars($c_tab['nama_kategori']); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($all_showcase_products)): ?>
        <div class="product-grid" id="showcaseProductGrid">
            <?php foreach($all_showcase_products as $idx => $product):
                $discount_pct = '';
                if ($product['harga_promo'] && $product['harga'] > 0) {
                    $disc = round((1 - $product['harga_promo'] / $product['harga']) * 100);
                    $discount_pct = $disc . '%';
                }
            ?>
            <div class="product-card product-card-premium" 
                 data-category="<?php echo $product['category_id']; ?>"
                 data-reveal 
                 data-delay="<?php echo ($idx % 4) * 80; ?>">
                 
                <div class="product-img-wrapper" style="position: relative; overflow: hidden; background: #F1F5F9; aspect-ratio: 1/1.15;">
                    <!-- Badges -->
                    <div class="product-badges" style="position: absolute; top: 0.85rem; left: 0.85rem; z-index: 4; display: flex; flex-direction: column; gap: 0.35rem;">
                        <?php if ($product['harga_promo']): ?>
                            <span class="badge badge-promo" style="background: #EF4444; color: white; font-weight: 700; border-radius: 6px; padding: 0.25rem 0.6rem; font-size: 0.75rem;">
                                <?php echo $discount_pct ? '-'.$discount_pct : 'PROMO'; ?>
                            </span>
                        <?php else: ?>
                            <span class="badge badge-new" style="background: var(--dark); color: white; font-weight: 700; border-radius: 6px; padding: 0.25rem 0.6rem; font-size: 0.75rem;">BARU</span>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Action Buttons -->
                    <div class="product-action-bar">
                        <!-- Wishlist Toggle -->
                        <button class="action-round-btn wishlist-btn" 
                                onclick="toggleWishlist(this, '<?php echo htmlspecialchars(addslashes($product['nama_produk'])); ?>')"
                                title="Tambah ke Favorit">
                            <i class="far fa-heart"></i>
                        </button>
                        <!-- Quick View -->
                        <button class="action-round-btn" 
                                onclick="openQuickView(<?php echo htmlspecialchars(json_encode($product)); ?>)"
                                title="Lihat Cepat">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <!-- Product Image -->
                    <a href="detail_produk.php?id=<?php echo $product['id']; ?>" style="display: block; width: 100%; height: 100%;">
                        <img src="<?php echo htmlspecialchars($product['gambar']); ?>"
                             alt="<?php echo htmlspecialchars($product['nama_produk']); ?>"
                             class="product-img" 
                             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;"
                             loading="lazy">
                    </a>

                    <!-- Quick Add to Cart Bar -->
                    <div class="product-quick-add">
                        <a href="keranjang.php?action=add&id=<?php echo $product['id']; ?>&qty=1" 
                           class="btn-quick-add"
                           onclick="triggerCartToast('<?php echo htmlspecialchars(addslashes($product['nama_produk'])); ?>');">
                            <i class="fas fa-shopping-bag"></i> Tambah ke Keranjang
                        </a>
                    </div>
                </div>

                <div class="product-info" style="padding: 1.25rem;">
                    <div class="product-category" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--accent); margin-bottom: 0.35rem; letter-spacing: 0.5px;">
                        <?php echo htmlspecialchars($product['nama_kategori']); ?>
                    </div>

                    <a href="detail_produk.php?id=<?php echo $product['id']; ?>">
                        <h3 class="product-name" style="font-size: 1rem; font-weight: 700; color: var(--dark); margin-bottom: 0.5rem; line-height: 1.35; height: 2.7rem; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                            <?php echo htmlspecialchars($product['nama_produk']); ?>
                        </h3>
                    </a>

                    <!-- Rating Stars -->
                    <div class="product-rating" style="display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.6rem;">
                        <div class="stars" style="color: #F59E0B; font-size: 0.8rem;">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                        </div>
                        <span class="rating-count" style="font-size: 0.75rem; color: var(--muted);">(<?php echo rand(24, 120); ?>)</span>
                    </div>

                    <!-- Price Tag -->
                    <div class="product-price" style="display: flex; align-items: baseline; gap: 0.6rem;">
                        <?php if ($product['harga_promo']): ?>
                            <span class="current-price" style="font-size: 1.15rem; font-weight: 800; color: var(--accent);"><?php echo format_rupiah($product['harga_promo']); ?></span>
                            <span class="old-price" style="font-size: 0.85rem; color: var(--muted); text-decoration: line-through;"><?php echo format_rupiah($product['harga']); ?></span>
                        <?php else: ?>
                            <span class="current-price" style="font-size: 1.15rem; font-weight: 800; color: var(--dark);"><?php echo format_rupiah($product['harga']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state" style="text-align: center; padding: 4rem 1rem;">
            <div class="empty-state-icon" style="font-size: 3rem; color: var(--muted); margin-bottom: 1rem;"><i class="fas fa-box-open"></i></div>
            <h3>Belum ada produk</h3>
            <p style="color: var(--muted);">Produk akan segera tersedia.</p>
        </div>
        <?php endif; ?>

        <div class="text-center mt-6" data-reveal>
            <a href="produk.php" class="btn btn-outline btn-lg" style="background: var(--white); border: 2px solid var(--dark); color: var(--dark); font-weight: 700;">
                Eksplor Semua Koleksi <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
            </a>
        </div>
    </div>
</section>

<!-- ===== FLASH SALE BANNER WITH LIVE COUNTDOWN TIMER ===== -->
<section class="section-sm">
    <div class="container">
        <div class="flash-sale-luxe" data-reveal>
            <div class="flash-sale-wrap">
                <!-- Info & Countdown -->
                <div>
                    <span style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(239, 68, 68, 0.2); color: #F87171; border: 1px solid rgba(239, 68, 68, 0.4); padding: 0.35rem 0.9rem; border-radius: 9999px; font-weight: 700; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">
                        <i class="fas fa-bolt" style="color: #F59E0B;"></i> FLASH SALE TERBATAS
                    </span>

                    <h2 style="font-size: 2.4rem; font-weight: 900; line-height: 1.2; margin-bottom: 1rem;">
                        Hemat Hingga <span style="color: #F59E0B;">30%</span> untuk Koleksi Pilihan
                    </h2>

                    <p style="color: #CBD5E1; font-size: 1rem; line-height: 1.6; max-width: 480px; margin-bottom: 1.5rem;">
                        Penawaran terbatas untuk item terfavorit minggu ini. Dapatkan potongan harga eksklusif sebelum waktu berakhir!
                    </p>

                    <!-- Interactive Countdown Timer -->
                    <div class="timer-boxes">
                        <div class="timer-unit">
                            <div class="timer-num" id="cd-hours">08</div>
                            <div class="timer-lbl">Jam</div>
                        </div>
                        <div class="timer-unit">
                            <div class="timer-num" id="cd-minutes">45</div>
                            <div class="timer-lbl">Menit</div>
                        </div>
                        <div class="timer-unit">
                            <div class="timer-num" id="cd-seconds">30</div>
                            <div class="timer-lbl">Detik</div>
                        </div>
                    </div>

                    <!-- Stock Progress -->
                    <div style="max-width: 380px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: #94A3B8; font-weight: 600;">
                            <span>🔥 Terjual: 82%</span>
                            <span>Sisa 18 Pcs</span>
                        </div>
                        <div class="stock-progress-wrap">
                            <div class="stock-progress-bar"></div>
                        </div>
                    </div>

                    <div style="margin-top: 1.5rem;">
                        <a href="produk.php?promo=1" class="btn btn-primary btn-lg btn-shimmer" style="background: #F59E0B; border-color: #F59E0B; color: #0F172A; font-weight: 800; box-shadow: 0 8px 24px rgba(245, 158, 11, 0.4);">
                            <i class="fas fa-shopping-bag"></i> Ambil Diskon Sekarang
                        </a>
                    </div>
                </div>

                <!-- Showcase Promo Items -->
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                    <?php 
                    $promo_preview = array_slice($featured_products, 0, 2);
                    foreach($promo_preview as $p_item):
                        $disc_p = round((1 - $p_item['harga_promo'] / $p_item['harga']) * 100);
                    ?>
                    <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: 18px; padding: 1rem; backdrop-filter: blur(8px); text-align: center;">
                        <img src="<?php echo htmlspecialchars($p_item['gambar']); ?>" alt="<?php echo htmlspecialchars($p_item['nama_produk']); ?>" style="width: 100%; height: 160px; object-fit: cover; border-radius: 12px; margin-bottom: 0.75rem;">
                        <h4 style="font-size: 0.9rem; font-weight: 700; color: #FFFFFF; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 0.25rem;">
                            <?php echo htmlspecialchars($p_item['nama_produk']); ?>
                        </h4>
                        <div style="color: #F59E0B; font-weight: 800; font-size: 0.95rem;">
                            <?php echo format_rupiah($p_item['harga_promo']); ?>
                        </div>
                        <div style="color: #94A3B8; text-decoration: line-through; font-size: 0.78rem;">
                            <?php echo format_rupiah($p_item['harga']); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== CURATED LOOKBOOK / "SHOP THE LOOK" ===== -->
<section class="section" style="background: var(--white);">
    <div class="container">
        <div class="section-header text-center" data-reveal style="margin-bottom: 3rem;">
            <span class="section-label">Style Editorial</span>
            <h2 class="section-title">Inspirasi <span class="text-accent">Lookbook 2026</span></h2>
            <p class="section-subtitle" style="max-width: 500px; margin: 0 auto;">Padu padan gaya street-casual modern untuk aktivitas harian yang penuh percaya diri</p>
        </div>

        <div class="lookbook-wrap" data-reveal>
            <!-- Large Editorial Image -->
            <div class="lookbook-main-visual">
                <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1000&h=1200&fit=crop" alt="Lookbook Streetwear GrahaClouth">
                <div class="lookbook-glass-card">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                        <span style="background: var(--accent); color: white; font-size: 0.72rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 9999px;">OUTFIT OF THE WEEK</span>
                    </div>
                    <h3 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.25rem;">Urban Minimalist Ensemble</h3>
                    <p style="font-size: 0.88rem; color: #E2E8F0; margin: 0;">Perpaduan Basic T-Shirt, Chino Pants, & Trucker Jacket untuk gaya kasual yang rapi.</p>
                </div>
            </div>

            <!-- Companion Lookbook Items -->
            <div class="lookbook-cards-list">
                <div style="margin-bottom: 0.5rem;">
                    <h4 style="font-size: 1.25rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">Item Terkait dalam Look Ini:</h4>
                    <p style="font-size: 0.85rem; color: var(--muted); margin: 0;">Beli paket rekomendasi untuk mendapatkan gaya terbaik</p>
                </div>

                <?php 
                $lookbook_items = array_slice($all_showcase_products, 0, 3);
                foreach($lookbook_items as $lb_item):
                ?>
                <div class="lookbook-item-card">
                    <img src="<?php echo htmlspecialchars($lb_item['gambar']); ?>" alt="<?php echo htmlspecialchars($lb_item['nama_produk']); ?>" class="lookbook-item-thumb">
                    <div style="flex: 1;">
                        <span style="font-size: 0.72rem; font-weight: 700; color: var(--accent); text-transform: uppercase;">
                            <?php echo htmlspecialchars($lb_item['nama_kategori']); ?>
                        </span>
                        <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--dark); margin: 0.15rem 0 0.35rem;">
                            <?php echo htmlspecialchars($lb_item['nama_produk']); ?>
                        </h4>
                        <div style="font-weight: 800; color: var(--dark); font-size: 0.92rem;">
                            <?php echo format_rupiah($lb_item['harga_promo'] ?: $lb_item['harga']); ?>
                        </div>
                    </div>
                    <div>
                        <a href="detail_produk.php?id=<?php echo $lb_item['id']; ?>" class="btn btn-sm btn-primary" style="border-radius: 9999px; padding: 0.45rem 0.9rem;">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ===== WHY CHOOSE US (4 GLASS GUARANTEE CARDS) ===== -->
<section class="section" style="background: var(--light); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
    <div class="container">
        <div class="section-header text-center" data-reveal style="margin-bottom: 3rem;">
            <span class="section-label">Keunggulan Kami</span>
            <h2 class="section-title">Mengapa Memilih <span class="text-accent">GrahaClouth?</span></h2>
            <p class="section-subtitle" style="max-width: 500px; margin: 0 auto;">Komitmen kami memberikan pengalaman berbelanja busana terbaik untuk Anda</p>
        </div>

        <div class="trust-cards-grid">
            <div class="trust-glass-card" data-reveal>
                <div class="trust-icon-box"><i class="fas fa-tshirt"></i></div>
                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--dark); margin-bottom: 0.4rem;">Bahan Premium Pilihan</h3>
                    <p style="font-size: 0.88rem; color: var(--muted); line-height: 1.6; margin: 0;">Katun Combed 30s berkualitas tinggi yang lembut di kulit, tahan lama, dan sejuk digunakan seharian.</p>
                </div>
            </div>

            <div class="trust-glass-card" data-reveal data-delay="100">
                <div class="trust-icon-box"><i class="fas fa-truck-fast"></i></div>
                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--dark); margin-bottom: 0.4rem;">Pengiriman Super Cepat</h3>
                    <p style="font-size: 0.88rem; color: var(--muted); line-height: 1.6; margin: 0;">Pesanan diproses dan dikirim dalam 1x24 jam dengan opsi pengiriman kilat ke seluruh pelosok Indonesia.</p>
                </div>
            </div>

            <div class="trust-glass-card" data-reveal data-delay="200">
                <div class="trust-icon-box"><i class="fas fa-rotate-left"></i></div>
                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--dark); margin-bottom: 0.4rem;">Garansi 7 Hari Tukar Size</h3>
                    <p style="font-size: 0.88rem; color: var(--muted); line-height: 1.6; margin: 0;">Ukuran kurang pas? Nikmati layanan tukar ukuran atau pengembalian produk dengan proses mudah tanpa ribet.</p>
                </div>
            </div>

            <div class="trust-glass-card" data-reveal data-delay="300">
                <div class="trust-icon-box"><i class="fas fa-handshake"></i></div>
                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--dark); margin-bottom: 0.4rem;">Layanan 24/7 & COD</h3>
                    <p style="font-size: 0.88rem; color: var(--muted); line-height: 1.6; margin: 0;">Tim customer support siap membantu via WhatsApp kapan pun, serta mendukung pembayaran tunai saat barang sampai (COD).</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== SOCIAL PROOF / CUSTOMER TESTIMONIALS ===== -->
<section class="section" style="background: var(--white);">
    <div class="container">
        <div class="section-header text-center" data-reveal style="margin-bottom: 3rem;">
            <span class="section-label">Ulasan Pembeli</span>
            <h2 class="section-title">Apa Kata <span class="text-accent">Pelanggan Kami?</span></h2>
            <p class="section-subtitle" style="max-width: 500px; margin: 0 auto;">Testimoni asli dari ribuan pecinta fashion yang telah berbelanja di GrahaClouth</p>
        </div>

        <div class="testimonials-grid">
            <div class="testimonial-card" data-reveal>
                <div>
                    <div style="color: #F59E0B; margin-bottom: 0.85rem; font-size: 0.95rem;">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p style="font-size: 0.95rem; line-height: 1.65; color: var(--body); font-style: italic;">
                        "Kualitas bahan t-shirtnya luar biasa bagus! Combed 30s-nya adem banget dipakai di cuaca panas. Jahitan rapi dan warna tidak pudar setelah dicuci berkali-kali."
                    </p>
                </div>
                <div class="testimonial-author">
                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&h=100&fit=crop" alt="Rina S." class="author-avatar">
                    <div>
                        <div style="font-weight: 700; color: var(--dark); font-size: 0.95rem;">Rina Safitri</div>
                        <div style="font-size: 0.78rem; color: var(--success); font-weight: 600;">
                            <i class="fas fa-check-circle"></i> Verified Buyer (Jakarta)
                        </div>
                    </div>
                </div>
            </div>

            <div class="testimonial-card" data-reveal data-delay="100">
                <div>
                    <div style="color: #F59E0B; margin-bottom: 0.85rem; font-size: 0.95rem;">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p style="font-size: 0.95rem; line-height: 1.65; color: var(--body); font-style: italic;">
                        "Jaket dan hoodie-nya punya cuttingan yang pas banget di badan. Pengirimannya juga cepet banget, pesen siang besoknya udah nyampe. Bakal langganan terus di GrahaClouth!"
                    </p>
                </div>
                <div class="testimonial-author">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop" alt="Dimas A." class="author-avatar">
                    <div>
                        <div style="font-weight: 700; color: var(--dark); font-size: 0.95rem;">Dimas Anggara</div>
                        <div style="font-size: 0.78rem; color: var(--success); font-weight: 600;">
                            <i class="fas fa-check-circle"></i> Verified Buyer (Bandung)
                        </div>
                    </div>
                </div>
            </div>

            <div class="testimonial-card" data-reveal data-delay="200">
                <div>
                    <div style="color: #F59E0B; margin-bottom: 0.85rem; font-size: 0.95rem;">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p style="font-size: 0.95rem; line-height: 1.65; color: var(--body); font-style: italic;">
                        "Suka banget sama celana chino-nya, bahannya stretch nyaman buat kerja maupun nongkrong. Pelayanannya ramah banget via WhatsApp pas nanya panduan size chart."
                    </p>
                </div>
                <div class="testimonial-author">
                    <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?w=100&h=100&fit=crop" alt="Budi P." class="author-avatar">
                    <div>
                        <div style="font-weight: 700; color: var(--dark); font-size: 0.95rem;">Budi Pratama</div>
                        <div style="font-size: 0.78rem; color: var(--success); font-weight: 600;">
                            <i class="fas fa-check-circle"></i> Verified Buyer (Surabaya)
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== VIP NEWSLETTER & VOUCHER POPUP ===== -->
<section class="section" style="background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%); border-top: 1px solid var(--accent-mid);">
    <div class="container">
        <div class="text-center" data-reveal style="max-width: 620px; margin: 0 auto;">
            <span class="section-label" style="background: var(--white); padding: 0.3rem 0.85rem; border-radius: 9999px; border: 1px solid var(--accent-mid);">VIP CLUB REWARDS</span>
            <h2 class="section-title" style="margin: 1rem 0 0.75rem;">
                Dapatkan Voucher Diskon <span class="text-accent">15%</span> Sekarang
            </h2>
            <p class="section-subtitle" style="margin-bottom: 2rem; color: var(--muted);">
                Daftarkan email Anda untuk menjadi member VIP dan dapatkan akses eksklusif ke flash sale rahasia serta voucher belanja pertama Anda.
            </p>

            <form id="vipNewsletterForm" onsubmit="handleNewsletterSubmit(event);" style="display: flex; gap: 0.75rem; max-width: 480px; margin: 0 auto; flex-wrap: wrap;">
                <input type="email" id="vipEmailInput" placeholder="Masukkan alamat email Anda..." required
                       class="form-control" style="flex: 1; min-width: 240px; border-radius: 9999px; padding: 0.85rem 1.4rem; border: 1.5px solid var(--accent-mid); background: var(--white);">
                <button type="submit" class="btn btn-primary btn-shimmer" style="border-radius: 9999px; padding: 0.85rem 1.75rem; flex-shrink: 0; font-weight: 700;">
                    <i class="fas fa-gift"></i> Klaim Voucher
                </button>
            </form>

            <div id="voucherRevealBox" style="display: none; margin-top: 1.5rem; background: var(--white); padding: 1.25rem 1.75rem; border-radius: 16px; border: 2px dashed var(--accent); box-shadow: var(--shadow-md);">
                <div style="font-size: 0.85rem; color: var(--muted); margin-bottom: 0.25rem;">🎉 Selamat! Kode Voucher Belanja Anda:</div>
                <div style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; margin-top: 0.5rem;">
                    <span style="font-family: monospace; font-size: 1.4rem; font-weight: 900; color: var(--accent); background: #EFF6FF; padding: 0.4rem 1rem; border-radius: 8px; letter-spacing: 2px;">
                        GRAHAVIP15
                    </span>
                    <button onclick="navigator.clipboard.writeText('GRAHAVIP15'); triggerToast('Kode voucher berhasil disalin!', 'toast-success');" class="btn btn-sm btn-outline" style="border-radius: 8px;">
                        <i class="far fa-copy"></i> Salin
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== QUICK VIEW MODAL ===== -->
<div class="modal-overlay" id="quickViewModal" onclick="if(event.target === this) closeQuickView();">
    <div class="quickview-container">
        <button class="quickview-close-btn" onclick="closeQuickView();">
            <i class="fas fa-times"></i>
        </button>
        <div class="quickview-body">
            <!-- Product Modal Visual -->
            <div style="border-radius: 16px; overflow: hidden; background: #F8FAFC; aspect-ratio: 1/1.15;">
                <img id="qv-img" src="" alt="Product" style="width: 100%; height: 100%; object-fit: cover;">
            </div>

            <!-- Product Modal Details -->
            <div style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <span id="qv-category" style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--accent); letter-spacing: 1px;">KATEGORI</span>
                    <h2 id="qv-title" style="font-size: 1.4rem; font-weight: 800; color: var(--dark); margin: 0.35rem 0 0.75rem; line-height: 1.3;">Nama Produk</h2>
                    
                    <div style="display: flex; align-items: baseline; gap: 0.75rem; margin-bottom: 1rem;">
                        <span id="qv-price-current" style="font-size: 1.5rem; font-weight: 800; color: var(--accent);">Rp 0</span>
                        <span id="qv-price-old" style="font-size: 1rem; color: var(--muted); text-decoration: line-through; display: none;">Rp 0</span>
                    </div>

                    <p id="qv-desc" style="font-size: 0.88rem; color: var(--muted); line-height: 1.6; margin-bottom: 1.25rem;">
                        Deskripsi produk lengkap akan ditampilkan di sini.
                    </p>

                    <!-- Sizes Selector -->
                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--dark); display: block; margin-bottom: 0.4rem;">PILIHAN UKURAN:</label>
                        <div id="qv-sizes" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <span class="filter-tab-btn" style="padding: 0.35rem 0.8rem; font-size: 0.8rem;">All Size</span>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--border); display: flex; gap: 0.6rem; flex-wrap: wrap;">
                    <a id="qv-add-cart-btn" href="#" class="btn btn-primary btn-shimmer" style="flex: 1; min-width: 160px; border-radius: 12px; font-weight: 700; justify-content: center;">
                        <i class="fas fa-shopping-bag"></i> Tambah Keranjang
                    </a>
                    <button type="button" id="qv-chat-btn" class="btn btn-outline" style="border-radius: 12px; font-weight: 700; border-color: var(--accent); color: var(--accent); background: var(--accent-light, #EFF6FF);" title="Tanya Produk via Chat">
                        <i class="fas fa-comments"></i> Tanya CS
                    </button>
                    <a id="qv-detail-link" href="#" class="btn btn-outline" style="border-radius: 12px;" title="Lihat Halaman Lengkap">
                        <i class="fas fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== FLOATING TOAST CONTAINER ===== -->
<div class="toast-container" id="toastContainer"></div>

<!-- ===== JAVASCRIPT LOGIC ===== -->
<script>
// Filter Products by Category in Showcase
function filterProducts(categoryId, btn) {
    // Update active tab button style
    document.querySelectorAll('.filter-tab-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const cards = document.querySelectorAll('#showcaseProductGrid .product-card');
    cards.forEach(card => {
        const itemCat = card.getAttribute('data-category');
        if (categoryId === 'all' || itemCat === categoryId) {
            card.style.display = 'flex';
            card.style.opacity = '0';
            setTimeout(() => {
                card.style.transition = 'opacity 0.35s ease';
                card.style.opacity = '1';
            }, 30);
        } else {
            card.style.display = 'none';
        }
    });
}

// Quick View Modal
function openQuickView(product) {
    document.getElementById('qv-img').src = product.gambar;
    document.getElementById('qv-title').textContent = product.nama_produk;
    document.getElementById('qv-category').textContent = product.nama_kategori || 'FASHION';
    document.getElementById('qv-desc').textContent = product.deskripsi || 'Produk fashion berkualitas tinggi dengan bahan premium.';
    
    // Price
    if (product.harga_promo && parseFloat(product.harga_promo) > 0) {
        document.getElementById('qv-price-current').textContent = 'Rp ' + Number(product.harga_promo).toLocaleString('id-ID');
        document.getElementById('qv-price-old').textContent = 'Rp ' + Number(product.harga).toLocaleString('id-ID');
        document.getElementById('qv-price-old').style.display = 'inline';
    } else {
        document.getElementById('qv-price-current').textContent = 'Rp ' + Number(product.harga).toLocaleString('id-ID');
        document.getElementById('qv-price-old').style.display = 'none';
    }

    // Sizes
    const sizesContainer = document.getElementById('qv-sizes');
    sizesContainer.innerHTML = '';
    const sizes = product.ukuran ? product.ukuran.split(',') : ['S', 'M', 'L', 'XL'];
    sizes.forEach((s, idx) => {
        const span = document.createElement('span');
        span.className = 'filter-tab-btn' + (idx === 0 ? ' active' : '');
        span.style.padding = '0.35rem 0.85rem';
        span.style.fontSize = '0.8rem';
        span.textContent = s.trim();
        span.onclick = function() {
            sizesContainer.querySelectorAll('span').forEach(el => el.classList.remove('active'));
            this.classList.add('active');
        };
        sizesContainer.appendChild(span);
    });

    // Links
    document.getElementById('qv-add-cart-btn').href = 'keranjang.php?action=add&id=' + product.id + '&qty=1';
    document.getElementById('qv-detail-link').href = 'detail_produk.php?id=' + product.id;
    
    // Chat inquiry
    document.getElementById('qv-chat-btn').onclick = function() {
        closeQuickView();
        const priceDisplay = (product.harga_promo && parseFloat(product.harga_promo) > 0)
            ? 'Rp ' + Number(product.harga_promo).toLocaleString('id-ID')
            : 'Rp ' + Number(product.harga).toLocaleString('id-ID');
        if (typeof window.askProductInChat === 'function') {
            window.askProductInChat(product.nama_produk, priceDisplay, product.gambar, product.id);
        }
    };

    document.getElementById('quickViewModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeQuickView() {
    document.getElementById('quickViewModal').classList.remove('active');
    document.body.style.overflow = '';
}

// Toast notification helper
function triggerToast(message, type = 'toast-success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = 'toast-bubble ' + type;
    
    let icon = '<i class="fas fa-check-circle"></i>';
    if (type === 'toast-heart') {
        icon = '<i class="fas fa-heart"></i>';
    }
    
    toast.innerHTML = icon + '<span>' + message + '</span>';
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(15px)';
        setTimeout(() => toast.remove(), 300);
    }, 3200);
}

function triggerCartToast(productName) {
    triggerToast(productName + ' berhasil ditambahkan ke keranjang!', 'toast-success');
}

// Toggle Wishlist
function toggleWishlist(btn, productName) {
    const icon = btn.querySelector('i');
    if (icon.classList.contains('far')) {
        icon.classList.remove('far');
        icon.classList.add('fas');
        btn.classList.add('active');
        triggerToast(productName + ' ditambahkan ke wishlist!', 'toast-heart');
    } else {
        icon.classList.remove('fas');
        icon.classList.add('far');
        btn.classList.remove('active');
        triggerToast(productName + ' dihapus dari wishlist.', 'toast-success');
    }
}

// VIP Newsletter Submit
function handleNewsletterSubmit(e) {
    e.preventDefault();
    const email = document.getElementById('vipEmailInput').value;
    if (email) {
        document.getElementById('voucherRevealBox').style.display = 'block';
        triggerToast('Terima kasih! Voucher Anda siap digunakan.', 'toast-success');
    }
}

// Live Flash Sale Countdown Timer
function initCountdown() {
    let hours = 8, minutes = 45, seconds = 30;
    setInterval(() => {
        if (seconds > 0) {
            seconds--;
        } else {
            seconds = 59;
            if (minutes > 0) {
                minutes--;
            } else {
                minutes = 59;
                if (hours > 0) hours--;
            }
        }
        document.getElementById('cd-hours').textContent = String(hours).padStart(2, '0');
        document.getElementById('cd-minutes').textContent = String(minutes).padStart(2, '0');
        document.getElementById('cd-seconds').textContent = String(seconds).padStart(2, '0');
    }, 1000);
}
initCountdown();
</script>

<?php require_once 'includes/footer.php'; ?>
