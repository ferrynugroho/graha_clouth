<?php
require_once 'includes/header.php';
require_once 'includes/navbar.php';

$search   = isset($_GET['q'])       ? trim($_GET['q'])   : '';
$kategori = isset($_GET['kategori'])? (int)$_GET['kategori'] : 0;
$promo    = isset($_GET['promo'])    ? 1 : 0;
$sort     = isset($_GET['sort'])     ? $_GET['sort'] : 'terbaru';

$where_clauses = ["p.status = 'aktif'"];
$params = [];
if ($search)   { $where_clauses[] = "(p.nama_produk LIKE :search OR p.deskripsi LIKE :search)"; $params[':search'] = "%$search%"; }
if ($kategori) { $where_clauses[] = "p.category_id = :kategori"; $params[':kategori'] = $kategori; }
if ($promo)    { $where_clauses[] = "p.harga_promo IS NOT NULL"; }
$where_sql = implode(' AND ', $where_clauses);
if ($sort == 'termurah') {
    $order_sql = "COALESCE(p.harga_promo, p.harga) ASC";
} elseif ($sort == 'termahal') {
    $order_sql = "COALESCE(p.harga_promo, p.harga) DESC";
} else {
    $order_sql = "p.created_at DESC";
}

$stmt = $conn->prepare("SELECT p.*, c.nama_kategori FROM products p JOIN categories c ON p.category_id = c.id WHERE $where_sql ORDER BY $order_sql");
$stmt->execute($params);
$products = $stmt->fetchAll();

$stmt_cat = $conn->query("SELECT * FROM categories ORDER BY nama_kategori ASC");
$categories = $stmt_cat->fetchAll();

$page_title = $search ? "Pencarian: \"$search\"" : ($promo ? "Produk Promo" : "Semua Produk");

// Count active filters for badge
$active_filter_count = 0;
if ($search) $active_filter_count++;
if ($kategori) $active_filter_count++;
if ($promo) $active_filter_count++;
?>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="index.php">Beranda</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:0.65rem;"></i></span>
            <span class="current"><?php echo htmlspecialchars($page_title); ?></span>
        </nav>
    </div>
</div>

<!-- Filter backdrop for mobile -->
<div class="produk-filter-backdrop" id="filterBackdrop" onclick="closeFilterSidebar()"></div>

<div class="section" style="background:var(--light); padding-top: 2.5rem;">
    <div class="container">

        <!-- Page Title & Mobile Filter Button -->
        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem;">
            <div>
                <h1 style="font-size:1.75rem;font-weight:800;color:var(--dark);letter-spacing:-0.5px;">
                    <?php echo htmlspecialchars($page_title); ?>
                </h1>
                <p style="color:var(--muted);font-size:0.875rem;margin-top:0.25rem;">
                    Menampilkan <strong><?php echo count($products); ?></strong> produk
                    <?php if ($kategori): ?> di kategori ini<?php endif; ?>
                </p>
                <!-- Active filter chips -->
                <?php if ($search || $kategori || $promo): ?>
                <div style="display:flex;flex-wrap:wrap;gap:0.4rem;margin-top:0.75rem;">
                    <?php if ($search): ?>
                    <a href="produk.php?<?php echo http_build_query(array_filter(['kategori'=>$kategori,'promo'=>$promo,'sort'=>$sort])); ?>" class="active-filter-chip">
                        <i class="fas fa-search" style="font-size:0.7rem;"></i> <?php echo htmlspecialchars($search); ?> <i class="fas fa-times" style="font-size:0.7rem;"></i>
                    </a>
                    <?php endif; ?>
                    <?php if ($promo): ?>
                    <a href="produk.php?<?php echo http_build_query(array_filter(['q'=>$search,'kategori'=>$kategori,'sort'=>$sort])); ?>" class="active-filter-chip">
                        <i class="fas fa-percent" style="font-size:0.7rem;"></i> Promo <i class="fas fa-times" style="font-size:0.7rem;"></i>
                    </a>
                    <?php endif; ?>
                    <a href="produk.php" style="font-size:0.75rem;color:var(--danger);font-weight:600;align-self:center;">Reset Semua</a>
                </div>
                <?php endif; ?>
            </div>

            <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                <!-- Mobile Filter Button -->
                <button class="mobile-filter-toggle-btn" onclick="openFilterSidebar()" id="mobileFilterBtn">
                    <i class="fas fa-sliders-h"></i> Filter
                    <?php if ($active_filter_count > 0): ?>
                    <span class="filter-count-badge"><?php echo $active_filter_count; ?></span>
                    <?php endif; ?>
                </button>
                <!-- Sort -->
                <form action="produk.php" method="GET" style="display:flex;align-items:center;gap:0.5rem;">
                    <?php if ($search):   ?><input type="hidden" name="q" value="<?php echo htmlspecialchars($search); ?>"><?php endif; ?>
                    <?php if ($kategori): ?><input type="hidden" name="kategori" value="<?php echo $kategori; ?>"><?php endif; ?>
                    <?php if ($promo):    ?><input type="hidden" name="promo" value="1"><?php endif; ?>
                    <label style="font-size:0.82rem;color:var(--muted);font-weight:500;white-space:nowrap;">Urutkan:</label>
                    <select name="sort" class="form-control form-select" style="width:auto;padding-right:2.25rem;" onchange="this.form.submit()">
                        <option value="terbaru"  <?php echo $sort=='terbaru' ?'selected':''; ?>>Terbaru</option>
                        <option value="termurah" <?php echo $sort=='termurah'?'selected':''; ?>>Termurah</option>
                        <option value="termahal" <?php echo $sort=='termahal'?'selected':''; ?>>Termahal</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="produk-page-layout" style="display:flex;gap:1.75rem;align-items:flex-start;">

            <!-- Sidebar Filter -->
            <aside class="produk-filter-sidebar card" id="filterSidebar"
                   style="width:240px;flex-shrink:0;position:sticky;top:90px; background:white;">

                <!-- Close button (mobile only) -->
                <div class="filter-close-btn" style="display:none;justify-content:space-between;align-items:center;margin-bottom:1rem;padding-bottom:0.75rem;border-bottom:1px solid var(--border);">
                    <span style="font-size:1rem;font-weight:700;color:var(--dark);">
                        <i class="fas fa-sliders-h text-accent"></i> Filter Produk
                    </span>
                    <button onclick="closeFilterSidebar()" style="background:var(--light);border:none;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:1rem;color:var(--muted);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;" class="filter-desktop-header">
                    <h3 style="font-size:0.95rem;font-weight:700;color:var(--dark);">
                        <i class="fas fa-sliders-h text-accent" style="margin-right:0.5rem;"></i>Filter
                    </h3>
                    <a href="produk.php" style="font-size:0.75rem;color:var(--muted);">Reset</a>
                </div>

                <form action="produk.php" method="GET" id="filterForm">
                    <!-- Search -->
                    <div class="form-group">
                        <label class="form-label">Cari Produk</label>
                        <div style="position:relative;">
                            <input type="text" name="q" class="form-control"
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   placeholder="Nama produk...">
                        </div>
                    </div>

                    <!-- Kategori -->
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <div style="display:flex;flex-direction:column;gap:0.4rem;">
                            <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.85rem;cursor:pointer;padding:0.4rem 0;">
                                <input type="radio" name="kategori" value=""
                                       <?php echo !$kategori?'checked':''; ?>
                                       style="accent-color:var(--accent);">
                                Semua Kategori
                            </label>
                            <?php foreach($categories as $cat): ?>
                            <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.85rem;cursor:pointer;padding:0.4rem 0;">
                                <input type="radio" name="kategori" value="<?php echo $cat['id']; ?>"
                                       <?php echo $kategori==$cat['id']?'checked':''; ?>
                                       style="accent-color:var(--accent);">
                                <?php echo htmlspecialchars($cat['nama_kategori']); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Promo toggle -->
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:0.6rem;font-size:0.875rem;cursor:pointer;padding:0.75rem 1rem;background:var(--light);border-radius:var(--r-sm);border:1.5px solid <?php echo $promo?'var(--accent)':'var(--border)'; ?>;">
                            <input type="checkbox" name="promo" value="1"
                                   <?php echo $promo?'checked':''; ?>
                                   style="accent-color:var(--accent);width:16px;height:16px;">
                            <span style="font-weight:500;<?php echo $promo?'color:var(--accent)':''; ?>">
                                <i class="fas fa-percent" style="margin-right:0.25rem;"></i> Hanya Promo
                            </span>
                        </label>
                    </div>

                    <!-- Sort hidden -->
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">

                    <button type="submit" class="btn btn-primary btn-full">
                        <i class="fas fa-search"></i> Terapkan Filter
                    </button>

                    <!-- Mobile apply btn -->
                    <button type="submit" class="btn btn-primary btn-full filter-apply-btn-mobile" style="display:none;margin-top:0.5rem;" onclick="closeFilterSidebar()">
                        <i class="fas fa-check"></i> Terapkan & Tutup
                    </button>
                </form>
            </aside>

            <!-- Product Grid -->
            <div style="flex:1;min-width:0;">
                <?php if (!empty($products)): ?>
                <div class="product-grid" style="grid-template-columns:repeat(3,1fr);">
                    <?php foreach($products as $idx => $product):
                        $discount_pct = '';
                        if ($product['harga_promo'] && $product['harga'] > 0) {
                            $disc = round((1 - $product['harga_promo'] / $product['harga']) * 100);
                            $discount_pct = $disc . '%';
                        }
                    ?>
                    <div class="product-card" data-reveal data-delay="<?php echo ($idx % 3) * 75; ?>">
                        <div class="product-img-wrapper">
                            <div class="product-badges">
                                <?php if ($product['harga_promo']): ?>
                                    <span class="badge badge-promo">
                                        <?php echo $discount_pct ? '-'.$discount_pct : 'PROMO'; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <button class="product-wishlist"><i class="far fa-heart"></i></button>
                            <a href="detail_produk.php?id=<?php echo $product['id']; ?>">
                                <img src="<?php echo htmlspecialchars($product['gambar']); ?>"
                                     alt="<?php echo htmlspecialchars($product['nama_produk']); ?>"
                                     class="product-img" loading="lazy">
                            </a>
                            <div class="product-quick-add">
                                <a href="keranjang.php?action=add&id=<?php echo $product['id']; ?>&qty=1"
                                   class="btn-quick-add">
                                    <i class="fas fa-shopping-bag"></i> Tambah ke Keranjang
                                </a>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-category"><?php echo htmlspecialchars($product['nama_kategori']); ?></div>
                            <a href="detail_produk.php?id=<?php echo $product['id']; ?>">
                                <h3 class="product-name"><?php echo htmlspecialchars($product['nama_produk']); ?></h3>
                            </a>
                            <div class="product-rating">
                                <div class="stars">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="rating-count">(<?php echo rand(10,120); ?>)</span>
                            </div>
                            <div class="product-price">
                                <?php if ($product['harga_promo']): ?>
                                    <span class="current-price"><?php echo format_rupiah($product['harga_promo']); ?></span>
                                    <span class="old-price"><?php echo format_rupiah($product['harga']); ?></span>
                                <?php else: ?>
                                    <span class="current-price"><?php echo format_rupiah($product['harga']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-actions">
                                <a href="detail_produk.php?id=<?php echo $product['id']; ?>" class="btn btn-ghost btn-sm">Detail</a>
                                <a href="keranjang.php?action=add&id=<?php echo $product['id']; ?>&qty=1" class="btn btn-primary btn-sm">
                                    <i class="fas fa-cart-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state" style="background:var(--white);border-radius:var(--r-lg);border:1px solid var(--border);">
                    <div class="empty-state-icon"><i class="fas fa-box-open"></i></div>
                    <h3>Produk tidak ditemukan</h3>
                    <p>Coba sesuaikan filter atau kata kunci pencarian Anda.</p>
                    <a href="produk.php" class="btn btn-primary mt-3">Lihat Semua Produk</a>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
function openFilterSidebar() {
    document.getElementById('filterSidebar').classList.add('open');
    document.getElementById('filterBackdrop').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeFilterSidebar() {
    document.getElementById('filterSidebar').classList.remove('open');
    document.getElementById('filterBackdrop').classList.remove('open');
    document.body.style.overflow = '';
}
</script>

<?php require_once 'includes/footer.php'; ?>
