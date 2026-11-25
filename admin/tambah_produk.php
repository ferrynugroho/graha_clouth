<?php
require_once 'includes/header.php';

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
    
    // Handle File Upload
    $gambar = '';
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
                    $gambar = 'uploads/products/' . $new_filename;
                } else {
                    $error = "Gagal mengunggah gambar.";
                }
            } else {
                $error = "Ukuran gambar terlalu besar. Maksimal 2MB.";
            }
        } else {
            $error = "Format gambar tidak didukung (Hanya JPG, JPEG, PNG, WEBP).";
        }
    } else {
        $error = "Gambar produk wajib diunggah.";
    }
    
    if (empty($error)) {
        try {
            $stmt = $conn->prepare("INSERT INTO products (category_id, nama_produk, deskripsi, harga, harga_promo, stok, gambar, ukuran, warna, status) VALUES (:cat, :nama, :desc, :harga, :promo, :stok, :img, :ukur, :warn, :status)");
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
                ':status' => $status
            ]);
            $success = "Produk berhasil ditambahkan.";
        } catch (PDOException $e) {
            $error = "Gagal menyimpan produk: " . $e->getMessage();
        }
    }
}
?>

<div class="flex justify-between items-center mb-4">
    <h2 style="font-size: 1.5rem; color: var(--primary);">Tambah Produk Baru</h2>
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
            <input type="text" name="nama_produk" class="form-control" required>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Kategori *</label>
                <select name="category_id" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nama_kategori']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Stok *</label>
                <input type="number" name="stok" class="form-control" required min="0" value="0">
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Harga (Rp) *</label>
                <input type="number" name="harga" class="form-control" required min="0">
            </div>
            <div class="form-group">
                <label class="form-label">Harga Promo (Rp) - Opsional</label>
                <input type="number" name="harga_promo" class="form-control" min="0">
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Ukuran (Pisahkan dengan koma)</label>
                <input type="text" name="ukuran" class="form-control" placeholder="Contoh: S,M,L,XL" value="S,M,L,XL">
            </div>
            <div class="form-group">
                <label class="form-label">Warna (Pisahkan dengan koma)</label>
                <input type="text" name="warna" class="form-control" placeholder="Contoh: Hitam,Putih" value="Hitam,Putih">
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Deskripsi *</label>
            <textarea name="deskripsi" class="form-control" rows="5" required></textarea>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Foto Produk *</label>
                <input type="file" name="gambar" class="form-control" required accept="image/jpeg, image/png, image/webp">
                <small class="text-secondary">Maks. 2MB. Format JPG, PNG, WEBP.</small>
            </div>
            <div class="form-group">
                <label class="form-label">Status *</label>
                <select name="status" class="form-control" required>
                    <option value="aktif">Aktif</option>
                    <option value="tidak_aktif">Tidak Aktif</option>
                </select>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Simpan Produk</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
