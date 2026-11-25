<?php
require_once 'includes/header.php';
require_once 'includes/navbar.php';

// Get categories with product count and latest product image sample
$stmt = $conn->query("
    SELECT c.*, COUNT(p.id) as total_produk,
           (SELECT gambar FROM products WHERE category_id = c.id AND status = 'aktif' AND gambar IS NOT NULL AND gambar != '' ORDER BY id DESC LIMIT 1) as sample_gambar
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id AND p.status = 'aktif'
    GROUP BY c.id 
    ORDER BY c.nama_kategori ASC
");
$categories = $stmt->fetchAll();
?>

<div class="container section">
    <div class="section-header" style="text-align: center; margin-bottom: 3rem;">
        <span class="section-label" style="display: inline-block; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; color: var(--accent); margin-bottom: 0.5rem;">Koleksi Kami</span>
        <h1 class="section-title" style="font-size: 2.25rem; font-weight: 800; color: var(--dark); margin-bottom: 0.75rem;">Kategori Produk</h1>
        <p class="section-subtitle" style="color: var(--muted); font-size: 1rem; max-width: 500px; margin: 0 auto;">Pilih kategori pakaian dan fashion yang sesuai dengan gaya & kebutuhanmu</p>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem;">
        <?php foreach($categories as $cat): 
            $cat_img = getCategoryRealImage($cat['nama_kategori'], $cat['sample_gambar']);
        ?>
            <a href="produk.php?kategori=<?php echo $cat['id']; ?>" class="category-item-card" style="background: var(--white); border-radius: var(--r-xl, 20px); padding: 2.25rem 1.5rem; text-align: center; box-shadow: var(--shadow-sm); border: 1px solid var(--border); transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; overflow: hidden; text-decoration: none;">
                
                <!-- Real Product Image Icon Circle -->
                <div class="cat-img-wrap" style="width: 110px; height: 110px; border-radius: 50%; overflow: hidden; margin: 0 auto 1.5rem; border: 4px solid var(--white); box-shadow: 0 6px 18px rgba(0,0,0,0.1); position: relative; transition: all 0.35s ease; background: var(--light);">
                    <img src="<?php echo htmlspecialchars($cat_img); ?>" 
                         alt="<?php echo htmlspecialchars($cat['nama_kategori']); ?>" 
                         class="cat-real-img"
                         style="width: 100%; height: 100%; object-fit: cover; object-position: center; transition: transform 0.4s ease;"
                         loading="lazy">
                </div>

                <h3 class="cat-title" style="font-size: 1.35rem; font-weight: 700; color: var(--dark); margin-bottom: 0.35rem; transition: color 0.2s ease;"><?php echo htmlspecialchars($cat['nama_kategori']); ?></h3>
                <p style="color: var(--muted); font-size: 0.9rem; font-weight: 500; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                    <span><?php echo $cat['total_produk']; ?> Produk</span>
                    <i class="fas fa-arrow-right text-accent" style="font-size: 0.75rem; opacity: 0; transform: translateX(-4px); transition: all 0.3s ease;"></i>
                </p>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<style>
.category-item-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 16px 36px rgba(37, 99, 235, 0.14);
    border-color: var(--accent-mid, #BFDBFE) !important;
}
.category-item-card:hover .cat-img-wrap {
    transform: scale(1.08);
    box-shadow: 0 8px 24px rgba(37, 99, 235, 0.28);
    border-color: var(--accent) !important;
}
.category-item-card:hover .cat-real-img {
    transform: scale(1.12);
}
.category-item-card:hover .cat-title {
    color: var(--accent) !important;
}
.category-item-card:hover p i {
    opacity: 1 !important;
    transform: translateX(0) !important;
}
</style>

<?php require_once 'includes/footer.php'; ?>


