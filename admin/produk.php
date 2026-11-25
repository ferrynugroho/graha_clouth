<?php
require_once 'includes/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Get image to delete
    $stmt = $conn->prepare("SELECT gambar FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $img = $stmt->fetchColumn();
    
    // Delete from DB
    $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
    if ($stmt->execute([':id' => $id])) {
        // Delete image file if it's a local upload
        if ($img && strpos($img, 'http') === false && file_exists("../" . $img)) {
            unlink("../" . $img);
        }
        echo "<script>alert('Produk berhasil dihapus.'); window.location.href='produk.php';</script>";
        exit;
    }
}

// Get all categories with product count for filter tabs & dropdown
$stmt_cats = $conn->query("
    SELECT c.*, COUNT(p.id) as total_produk 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.nama_kategori ASC
");
$all_categories = $stmt_cats->fetchAll();

// Total count of all products
$stmt_total = $conn->query("SELECT COUNT(*) FROM products");
$total_all_products = $stmt_total->fetchColumn();

// Filter parameters
$kategori_filter = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$stok_filter = isset($_GET['stok']) ? trim($_GET['stok']) : '';

// Build SQL query dynamically
$where_clauses = [];
$params = [];

if ($kategori_filter > 0) {
    $where_clauses[] = "p.category_id = :cat_id";
    $params[':cat_id'] = $kategori_filter;
}

if (!empty($search)) {
    $where_clauses[] = "(p.nama_produk LIKE :q OR p.deskripsi LIKE :q)";
    $params[':q'] = "%$search%";
}

if (!empty($status_filter)) {
    $where_clauses[] = "p.status = :status";
    $params[':status'] = $status_filter;
}

if ($stok_filter === 'menipis') {
    $where_clauses[] = "p.stok <= 5 AND p.stok > 0";
} elseif ($stok_filter === 'habis') {
    $where_clauses[] = "p.stok = 0";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

$stmt = $conn->prepare("
    SELECT p.*, c.nama_kategori 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    $where_sql 
    ORDER BY p.id DESC
");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Find active category name if filtered
$active_category_name = '';
if ($kategori_filter > 0) {
    foreach ($all_categories as $ac) {
        if ($ac['id'] == $kategori_filter) {
            $active_category_name = $ac['nama_kategori'];
            break;
        }
    }
}
?>

<div class="flex justify-between items-center mb-4">
    <div>
        <h2 style="font-size: 1.5rem; color: var(--dark); font-weight: 700; margin-bottom: 0.25rem;">Manajemen Produk</h2>
        <p style="color: var(--muted); font-size: 0.88rem;">Kelola katalog produk, harga, stok, dan filter per kategori</p>
    </div>
    <div style="display: flex; gap: 0.75rem; align-items: center;">
        <span style="background: var(--light); color: var(--dark); padding: 0.45rem 0.9rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; border: 1px solid var(--border);">
            Total: <?php echo count($products); ?> / <?php echo $total_all_products; ?> Produk
        </span>
        <a href="tambah_produk.php" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1.25rem;">
            <i class="fas fa-plus"></i> Tambah Produk
        </a>
    </div>
</div>

<!-- Category Quick Filter Chips / Tabs -->
<div class="card" style="background: var(--white); border-radius: 12px; border: 1px solid var(--border); box-shadow: var(--shadow-sm); padding: 1.25rem; margin-bottom: 1.5rem;">
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
        <i class="fas fa-filter text-accent"></i>
        <span style="font-weight: 700; font-size: 0.9rem; color: var(--dark);">Filter Kategori Cepat:</span>
    </div>
    
    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
        <!-- All Categories Chip -->
        <a href="produk.php<?php echo !empty($search) ? '?q='.urlencode($search) : ''; ?>" 
           style="padding: 0.45rem 1rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: all 0.2s ease; <?php echo ($kategori_filter == 0) ? 'background: var(--accent); color: white; border: 1.5px solid var(--accent);' : 'background: var(--light); color: var(--dark); border: 1.5px solid var(--border);'; ?>">
            Semua (<?php echo $total_all_products; ?>)
        </a>

        <!-- Category Chips -->
        <?php foreach($all_categories as $cat): 
            $is_active = ($kategori_filter == $cat['id']);
            $chip_url = 'produk.php?kategori=' . $cat['id'];
            if (!empty($search)) $chip_url .= '&q=' . urlencode($search);
            if (!empty($status_filter)) $chip_url .= '&status=' . urlencode($status_filter);
        ?>
            <a href="<?php echo $chip_url; ?>" 
               style="padding: 0.45rem 1rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; transition: all 0.2s ease; <?php echo $is_active ? 'background: var(--accent); color: white; border: 1.5px solid var(--accent); box-shadow: 0 4px 12px rgba(37,99,235,0.25);' : 'background: var(--light); color: var(--dark); border: 1.5px solid var(--border);'; ?>">
                <span><?php echo htmlspecialchars($cat['nama_kategori']); ?></span>
                <span style="background: <?php echo $is_active ? 'rgba(255,255,255,0.25)' : 'rgba(0,0,0,0.06)'; ?>; padding: 0.1rem 0.45rem; border-radius: 9999px; font-size: 0.75rem;">
                    <?php echo $cat['total_produk']; ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Advanced Filter Bar (Search, Dropdowns) -->
    <form action="produk.php" method="GET" style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border); display: grid; grid-template-columns: 2fr 1.5fr 1.2fr 1.2fr auto; gap: 0.75rem; align-items: center;">
        
        <!-- Search Query -->
        <div style="position: relative;">
            <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="Cari nama produk..." 
                   class="form-control" 
                   style="width: 100%; padding: 0.55rem 0.75rem 0.55rem 2.25rem; border-radius: 8px; border: 1px solid var(--border); font-size: 0.88rem;">
            <i class="fas fa-search" style="position: absolute; left: 0.8rem; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 0.85rem;"></i>
        </div>

        <!-- Dropdown Kategori -->
        <div>
            <select name="kategori" class="form-control" style="width: 100%; padding: 0.55rem 0.75rem; border-radius: 8px; border: 1px solid var(--border); font-size: 0.88rem;">
                <option value="">-- Semua Kategori --</option>
                <?php foreach($all_categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($kategori_filter == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nama_kategori']); ?> (<?php echo $cat['total_produk']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Status Filter -->
        <div>
            <select name="status" class="form-control" style="width: 100%; padding: 0.55rem 0.75rem; border-radius: 8px; border: 1px solid var(--border); font-size: 0.88rem;">
                <option value="">-- Status --</option>
                <option value="aktif" <?php echo ($status_filter == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                <option value="tidak_aktif" <?php echo ($status_filter == 'tidak_aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
            </select>
        </div>

        <!-- Stok Filter -->
        <div>
            <select name="stok" class="form-control" style="width: 100%; padding: 0.55rem 0.75rem; border-radius: 8px; border: 1px solid var(--border); font-size: 0.88rem;">
                <option value="">-- Stok --</option>
                <option value="menipis" <?php echo ($stok_filter == 'menipis') ? 'selected' : ''; ?>>Menipis (≤ 5)</option>
                <option value="habis" <?php echo ($stok_filter == 'habis') ? 'selected' : ''; ?>>Habis (0)</option>
            </select>
        </div>

        <!-- Submit & Reset Buttons -->
        <div style="display: flex; gap: 0.4rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0.55rem 1rem; font-size: 0.85rem; font-weight: 600;">
                <i class="fas fa-search"></i> Filter
            </button>
            <?php if(!empty($search) || $kategori_filter > 0 || !empty($status_filter) || !empty($stok_filter)): ?>
                <a href="produk.php" class="btn btn-ghost" style="padding: 0.55rem 0.8rem; font-size: 0.85rem;" title="Reset Filter">
                    <i class="fas fa-undo"></i> Reset
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Active Filter Notice -->
<?php if(!empty($search) || $kategori_filter > 0 || !empty($status_filter) || !empty($stok_filter)): ?>
    <div style="margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between; background: var(--accent-light, #EFF6FF); padding: 0.6rem 1rem; border-radius: 8px; border: 1px solid var(--accent-mid, #BFDBFE); font-size: 0.85rem;">
        <div style="color: var(--dark);">
            <i class="fas fa-info-circle text-accent"></i> Filter aktif: 
            <?php if($kategori_filter > 0): ?>
                <strong>Kategori: <?php echo htmlspecialchars($active_category_name); ?></strong> | 
            <?php endif; ?>
            <?php if(!empty($search)): ?>
                <strong>Pencarian: "<?php echo htmlspecialchars($search); ?>"</strong> | 
            <?php endif; ?>
            <?php if(!empty($status_filter)): ?>
                <strong>Status: <?php echo ucfirst($status_filter); ?></strong> | 
            <?php endif; ?>
            <?php if(!empty($stok_filter)): ?>
                <strong>Stok: <?php echo ucfirst($stok_filter); ?></strong> | 
            <?php endif; ?>
            <span>Ditemukan <strong><?php echo count($products); ?></strong> produk.</span>
        </div>
        <a href="produk.php" style="color: var(--danger); font-weight: 600; text-decoration: none;">
            <i class="fas fa-times-circle"></i> Hapus Filter
        </a>
    </div>
<?php endif; ?>

<!-- Product Table Card -->
<div class="card" style="background: var(--white); border-radius: 12px; border: 1px solid var(--border); box-shadow: var(--shadow-sm); padding: 1.5rem;">
    <div class="table-responsive">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: var(--light); border-bottom: 2px solid var(--border);">
                    <th style="padding: 0.75rem 1rem; font-size: 0.85rem; font-weight: 600; color: var(--muted); width: 60px;">Foto</th>
                    <th style="padding: 0.75rem 1rem; font-size: 0.85rem; font-weight: 600; color: var(--muted);">Nama Produk</th>
                    <th style="padding: 0.75rem 1rem; font-size: 0.85rem; font-weight: 600; color: var(--muted);">Kategori</th>
                    <th style="padding: 0.75rem 1rem; font-size: 0.85rem; font-weight: 600; color: var(--muted);">Harga</th>
                    <th style="padding: 0.75rem 1rem; font-size: 0.85rem; font-weight: 600; color: var(--muted); text-align: center;">Stok</th>
                    <th style="padding: 0.75rem 1rem; font-size: 0.85rem; font-weight: 600; color: var(--muted); text-align: center;">Status</th>
                    <th style="padding: 0.75rem 1rem; font-size: 0.85rem; font-weight: 600; color: var(--muted); text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($products)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem 1rem; color: var(--muted);">
                            <i class="fas fa-box-open" style="font-size: 2.5rem; margin-bottom: 0.75rem; color: var(--border); display: block;"></i>
                            <div style="font-weight: 600; font-size: 1rem; margin-bottom: 0.25rem;">Tidak ada produk ditemukan</div>
                            <p style="font-size: 0.85rem;">Coba ubah kata kunci pencarian atau reset filter kategori.</p>
                            <a href="produk.php" class="btn btn-sm btn-outline" style="margin-top: 0.75rem;">Reset Semua Filter</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($products as $p): ?>
                    <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s ease;">
                        <td style="padding: 0.75rem 1rem;">
                            <img src="<?php echo strpos($p['gambar'], 'http') === 0 ? $p['gambar'] : '../'.$p['gambar']; ?>" 
                                 alt="<?php echo htmlspecialchars($p['nama_produk']); ?>" 
                                 style="width: 52px; height: 52px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border); background: var(--light);">
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            <div style="font-weight: 600; font-size: 0.95rem; color: var(--dark); max-width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($p['nama_produk']); ?>">
                                <?php echo htmlspecialchars($p['nama_produk']); ?>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--muted); margin-top: 0.15rem;">
                                ID: #<?php echo $p['id']; ?> | Ukuran: <?php echo htmlspecialchars($p['ukuran'] ?? '-'); ?>
                            </div>
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            <a href="produk.php?kategori=<?php echo $p['category_id']; ?>" 
                               style="display: inline-block; background: var(--light); color: var(--dark); padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; border: 1px solid var(--border); text-decoration: none;" 
                               title="Filter berdasarkan kategori ini">
                                <?php echo htmlspecialchars($p['nama_kategori']); ?>
                            </a>
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            <?php if($p['harga_promo']): ?>
                                <div style="font-weight: 700; color: var(--accent); font-size: 0.95rem;"><?php echo format_rupiah($p['harga_promo']); ?></div>
                                <div style="font-size: 0.78rem; text-decoration: line-through; color: var(--muted);"><?php echo format_rupiah($p['harga']); ?></div>
                            <?php else: ?>
                                <div style="font-weight: 700; font-size: 0.95rem; color: var(--dark);"><?php echo format_rupiah($p['harga']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: center;">
                            <?php if($p['stok'] == 0): ?>
                                <span style="background: #FEE2E2; color: #DC2626; padding: 0.25rem 0.55rem; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">Habis</span>
                            <?php elseif($p['stok'] <= 5): ?>
                                <span style="background: #FEF3C7; color: #D97706; padding: 0.25rem 0.55rem; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                                    <?php echo $p['stok']; ?> (Menipis)
                                </span>
                            <?php else: ?>
                                <span style="font-weight: 600; color: var(--dark); font-size: 0.9rem;"><?php echo $p['stok']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: center;">
                            <?php if($p['status'] == 'aktif'): ?>
                                <span style="background: #D1FAE5; color: #059669; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; display: inline-block;">Aktif</span>
                            <?php else: ?>
                                <span style="background: var(--light); color: var(--muted); padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; display: inline-block; border: 1px solid var(--border);">Tidak Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right;">
                            <div style="display: inline-flex; gap: 0.35rem; justify-content: flex-end;">
                                <a href="edit_produk.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline" style="padding: 0.35rem 0.65rem; font-size: 0.8rem;" title="Edit Produk">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="produk.php?delete=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger" style="padding: 0.35rem 0.65rem; font-size: 0.8rem;" title="Hapus Produk" onclick="return confirm('Yakin ingin menghapus produk <?php echo addslashes(htmlspecialchars($p['nama_produk'])); ?>?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
