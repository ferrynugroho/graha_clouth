<?php
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute([':id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<script>alert('Produk tidak ditemukan.'); window.location.href='produk.php';</script>";
    exit;
}

// Get Categories
$stmt = $conn->query("SELECT * FROM categories ORDER BY nama_kategori ASC");
$categories = $stmt->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama_produk']);
    $kategori = $_POST['category_id'];
    $harga = str_replace(['Rp', '.', ' '], '', $_POST['harga']);
    $harga_promo = !empty($_POST['harga_promo']) ? str_replace(['Rp', '.', ' '], '', $_POST['harga_promo']) : null;
    $stok = (int)$_POST['stok'];
    $ukuran = $_POST['ukuran'];
    $warna = $_POST['warna'];
    $deskripsi = $_POST['deskripsi'];
    $status = $_POST['status'];
    
    $gambar = $product['gambar']; // keep old image by default
    
    // Handle File Upload if exists
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['gambar']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        $filesize = $_FILES['gambar']['size'];
        
        if (in_array(strtolower($filetype), $allowed)) {
            if ($filesize <= 2000000) { // 2MB max
                $new_filename = uniqid() . '.' . $filetype;
                $upload_path = '../uploads/products/' . $new_filename;
                
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_path)) {
                    // Delete old image if it was local
                    if ($product['gambar'] && strpos($product['gambar'], 'http') === false && file_exists("../" . $product['gambar'])) {
                        unlink("../" . $product['gambar']);
                    }
                    $gambar = 'uploads/products/' . $new_filename;
                } else {
                    $error = "Gagal mengunggah gambar baru.";
                }
            } else {
                $error = "Ukuran gambar terlalu besar. Maksimal 2MB.";
            }
        } else {
            $error = "Format gambar tidak didukung (Hanya JPG, JPEG, PNG, WEBP).";
        }
    }
    
    if (empty($error)) {
        try {
            $stmt = $conn->prepare("UPDATE products SET category_id = :cat, nama_produk = :nama, deskripsi = :desc, harga = :harga, harga_promo = :promo, stok = :stok, gambar = :img, ukuran = :ukur, warna = :warn, status = :status WHERE id = :id");
            $stmt->execute([
                ':cat' => $kategori,
                ':nama' => $nama,
                ':desc' => $deskripsi,
                ':harga' => $harga,
                ':promo' => $harga_promo,
                ':stok' => $stok,
                ':img' => $gambar,
                ':ukur' => $ukuran,
                ':warn' => $warna,
                ':status' => $status,
                ':id' => $id
            ]);
            $success = "Produk berhasil diperbarui.";
            // Refresh data
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $product = $stmt->fetch();
        } catch (PDOException $e) {
            $error = "Gagal memperbarui produk: " . $e->getMessage();
        }
    }
}
?>

<div class="flex justify-between items-center mb-4">
    <h2 style="font-size: 1.5rem; color: var(--primary);">Edit Produk</h2>
    <a href="produk.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card" style="max-width: 800px;">
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label class="form-label">Nama Produk *</label>
            <input type="text" name="nama_produk" class="form-control" required value="<?php echo htmlspecialchars($product['nama_produk']); ?>">
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Kategori *</label>
                <select name="category_id" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $product['category_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['nama_kategori']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Stok *</label>
                <input type="number" name="stok" class="form-control" required min="0" value="<?php echo $product['stok']; ?>">
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Harga (Rp) *</label>
                <input type="number" name="harga" class="form-control" required min="0" value="<?php echo intval($product['harga']); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Harga Promo (Rp) - Opsional</label>
                <input type="number" name="harga_promo" class="form-control" min="0" value="<?php echo $product['harga_promo'] ? intval($product['harga_promo']) : ''; ?>">
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Ukuran (Pisahkan dengan koma)</label>
                <input type="text" name="ukuran" class="form-control" value="<?php echo htmlspecialchars($product['ukuran']); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Warna (Pisahkan dengan koma)</label>
                <input type="text" name="warna" class="form-control" value="<?php echo htmlspecialchars($product['warna']); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Deskripsi *</label>
            <textarea name="deskripsi" class="form-control" rows="5" required><?php echo htmlspecialchars($product['deskripsi']); ?></textarea>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Foto Produk Saat Ini</label>
                <div class="mb-2">
                    <img src="<?php echo strpos($product['gambar'], 'http') === 0 ? $product['gambar'] : '../'.$product['gambar']; ?>" style="max-width: 150px; border-radius: 8px;">
                </div>
                <label class="form-label">Ganti Foto (Biarkan kosong jika tidak ingin ganti)</label>
                <input type="file" name="gambar" class="form-control" accept="image/jpeg, image/png, image/webp">
            </div>
            <div class="form-group">
                <label class="form-label">Status *</label>
                <select name="status" class="form-control" required>
                    <option value="aktif" <?php echo $product['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="tidak_aktif" <?php echo $product['status'] == 'tidak_aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
                </select>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Update Produk</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
