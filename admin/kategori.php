<?php
require_once 'includes/header.php';

// Handle Insert/Update/Delete
$error = '';
$success = '';

// Check if editing via GET
$edit_category = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $stmt_edit = $conn->prepare("SELECT * FROM categories WHERE id = :id");
    $stmt_edit->execute([':id' => $edit_id]);
    $edit_category = $stmt_edit->fetch();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $nama = trim($_POST['nama_kategori']);
        if (!empty($nama)) {
            // Check if already exists
            $check = $conn->prepare("SELECT id FROM categories WHERE LOWER(nama_kategori) = LOWER(:nama)");
            $check->execute([':nama' => $nama]);
            if ($check->fetch()) {
                $error = "Kategori dengan nama \"$nama\" sudah ada!";
            } else {
                $stmt = $conn->prepare("INSERT INTO categories (nama_kategori) VALUES (:nama)");
                if ($stmt->execute([':nama' => $nama])) {
                    $success = "Kategori \"$nama\" berhasil ditambahkan.";
                } else {
                    $error = "Gagal menambah kategori.";
                }
            }
        } else {
            $error = "Nama kategori tidak boleh kosong.";
        }
    } elseif (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        $nama = trim($_POST['nama_kategori']);
        if (!empty($nama) && $id > 0) {
            // Check if name already used by other category
            $check = $conn->prepare("SELECT id FROM categories WHERE LOWER(nama_kategori) = LOWER(:nama) AND id != :id");
            $check->execute([':nama' => $nama, ':id' => $id]);
            if ($check->fetch()) {
                $error = "Kategori dengan nama \"$nama\" sudah digunakan oleh kategori lain!";
            } else {
                $stmt = $conn->prepare("UPDATE categories SET nama_kategori = :nama WHERE id = :id");
                if ($stmt->execute([':nama' => $nama, ':id' => $id])) {
                    $success = "Kategori ID #$id berhasil diperbarui menjadi \"$nama\".";
                    $edit_category = null; // reset edit mode
                } else {
                    $error = "Gagal mengupdate kategori.";
                }
            }
        } else {
            $error = "Nama kategori tidak boleh kosong.";
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = :id");
    if ($stmt->execute([':id' => $id])) {
        echo "<script>alert('Kategori berhasil dihapus.'); window.location.href='kategori.php';</script>";
        exit;
    }
}

// Get Categories with product count and latest product sample image
$stmt = $conn->query("
    SELECT c.*, COUNT(p.id) as total_produk,
           (SELECT gambar FROM products WHERE category_id = c.id AND status = 'aktif' AND gambar IS NOT NULL AND gambar != '' ORDER BY id DESC LIMIT 1) as sample_gambar
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.nama_kategori ASC
");
$categories = $stmt->fetchAll();
?>

<div class="flex justify-between items-center mb-4">
    <div>
        <h2 style="font-size: 1.5rem; color: var(--dark); font-weight: 700; margin-bottom: 0.25rem;">Manajemen Kategori</h2>
        <p style="color: var(--muted); font-size: 0.88rem;">Kelola, tambah, dan edit kategori produk toko Anda</p>
    </div>
    <div>
        <span style="background: var(--accent-light, #EFF6FF); color: var(--accent); padding: 0.4rem 0.9rem; border-radius: var(--r-full, 9999px); font-size: 0.85rem; font-weight: 600; border: 1px solid var(--accent-mid, #BFDBFE);">
            Total: <?php echo count($categories); ?> Kategori
        </span>
    </div>
</div>

<?php if($error): ?>
    <div class="alert alert-danger" style="background: #FEE2E2; color: #DC2626; border: 1px solid #FCA5A5; padding: 0.85rem 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if($success): ?>
    <div class="alert alert-success" style="background: #D1FAE5; color: #059669; border: 1px solid #6EE7B7; padding: 0.85rem 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 340px 1fr; gap: 2rem; align-items: start;">
    
    <!-- Form Tambah / Edit Kategori -->
    <div class="card" style="background: var(--white); border-radius: 12px; border: 1px solid var(--border); box-shadow: var(--shadow-sm); padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border);">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--dark); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas <?php echo $edit_category ? 'fa-edit text-accent' : 'fa-plus-circle text-accent'; ?>"></i>
                <?php echo $edit_category ? 'Edit Kategori' : 'Tambah Kategori'; ?>
            </h3>
            <?php if($edit_category): ?>
                <a href="kategori.php" class="btn btn-sm btn-ghost" style="padding: 0.25rem 0.6rem; font-size: 0.75rem;">
                    <i class="fas fa-times"></i> Batal
                </a>
            <?php endif; ?>
        </div>
        
        <form action="kategori.php" method="POST" id="form-main-category">
            <?php if($edit_category): ?>
                <input type="hidden" name="edit" value="1">
                <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
            <?php else: ?>
                <input type="hidden" name="add" value="1">
            <?php endif; ?>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label class="form-label" style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 0.4rem;">Nama Kategori <span style="color: var(--danger);">*</span></label>
                <input type="text" name="nama_kategori" class="form-control" 
                       value="<?php echo htmlspecialchars($edit_category ? $edit_category['nama_kategori'] : ''); ?>" 
                       required placeholder="Contoh: Kaos, Kemeja, Hoodie..." 
                       style="width: 100%; padding: 0.65rem 0.9rem; border-radius: 8px; border: 1.5px solid var(--border); font-size: 0.95rem;"
                       autofocus>
                <small style="color: var(--muted); font-size: 0.78rem; display: block; margin-top: 0.35rem;">
                    Nama kategori akan muncul pada menu navigasi & filter produk.
                </small>
            </div>

            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 0.75rem; font-weight: 600; justify-content: center;">
                    <i class="fas <?php echo $edit_category ? 'fa-save' : 'fa-plus'; ?>"></i>
                    <?php echo $edit_category ? 'Simpan Perubahan' : 'Tambah Kategori'; ?>
                </button>
                <?php if($edit_category): ?>
                    <a href="kategori.php" class="btn btn-outline" style="padding: 0.75rem 1rem;">
                        Batal
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Table Kategori -->
    <div class="card" style="background: var(--white); border-radius: 12px; border: 1px solid var(--border); box-shadow: var(--shadow-sm); padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--dark); margin: 0;">Daftar Kategori</h3>
            
            <!-- Quick Search Input -->
            <div style="position: relative; min-width: 220px;">
                <input type="text" id="searchCategoryInput" onkeyup="filterCategoryTable()" 
                       placeholder="Cari kategori..." 
                       style="width: 100%; padding: 0.45rem 0.75rem 0.45rem 2rem; border-radius: 8px; border: 1px solid var(--border); font-size: 0.85rem;">
                <i class="fas fa-search" style="position: absolute; left: 0.7rem; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 0.8rem;"></i>
            </div>
        </div>

        <div class="table-responsive">
            <table id="categoryTable" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: var(--light); border-bottom: 2px solid var(--border);">
                        <th style="padding: 0.75rem 1rem; font-size: 0.85rem; font-weight: 600; color: var(--muted); width: 60px;">Foto</th>
                        <th style="padding: 0.75rem 1rem; font-size: 0.85rem; font-weight: 600; color: var(--muted);">Nama Kategori</th>
                        <th style="padding: 0.75rem 1rem; font-size: 0.85rem; font-weight: 600; color: var(--muted); width: 140px; text-align: center;">Total Produk</th>
                        <th style="padding: 0.75rem 1rem; font-size: 0.85rem; font-weight: 600; color: var(--muted); width: 160px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($categories)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 2rem; color: var(--muted);">
                                Belum ada kategori produk.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($categories as $cat): 
                            $cat_thumb = getCategoryRealImage($cat['nama_kategori'], $cat['sample_gambar'] ?? null, true);
                            $is_current_editing = ($edit_category && $edit_category['id'] == $cat['id']);
                        ?>
                        <tr style="border-bottom: 1px solid var(--border); <?php echo $is_current_editing ? 'background: #EFF6FF;' : ''; ?> transition: background 0.2s ease;">
                            <td style="padding: 0.75rem 1rem;">
                                <div style="width: 44px; height: 44px; border-radius: 50%; overflow: hidden; border: 2px solid var(--border); background: var(--light);">
                                    <img src="<?php echo htmlspecialchars($cat_thumb); ?>" alt="<?php echo htmlspecialchars($cat['nama_kategori']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            </td>
                            <td style="padding: 0.75rem 1rem;">
                                <div style="font-weight: 600; font-size: 0.95rem; color: var(--dark);">
                                    <?php echo htmlspecialchars($cat['nama_kategori']); ?>
                                </div>
                                <span style="font-size: 0.75rem; color: var(--muted);">ID: #<?php echo $cat['id']; ?></span>
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: center;">
                                <a href="produk.php?kategori=<?php echo $cat['id']; ?>" style="display: inline-block; background: var(--light); color: var(--dark); padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.82rem; font-weight: 600; border: 1px solid var(--border); transition: all 0.2s ease;" onmouseover="this.style.background='var(--accent-light)';this.style.color='var(--accent)';" onmouseout="this.style.background='var(--light)';this.style.color='var(--dark)';">
                                    <?php echo $cat['total_produk']; ?> Produk
                                </a>
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right;">
                                <div style="display: inline-flex; gap: 0.4rem; justify-content: flex-end;">
                                    <!-- Tombol Edit -->
                                    <button type="button" 
                                            class="btn btn-sm btn-primary" 
                                            onclick="openEditCategoryModal(<?php echo $cat['id']; ?>, '<?php echo addslashes(htmlspecialchars($cat['nama_kategori'])); ?>')"
                                            style="padding: 0.4rem 0.75rem; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.35rem;"
                                            title="Edit Kategori Ini">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>

                                    <!-- Tombol Hapus -->
                                    <a href="kategori.php?delete=<?php echo $cat['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       style="padding: 0.4rem 0.65rem; font-size: 0.8rem;" 
                                       title="Hapus Kategori" 
                                       onclick="return confirm('Yakin ingin menghapus kategori <?php echo addslashes(htmlspecialchars($cat['nama_kategori'])); ?>? Semua produk di dalamnya juga akan terhapus!');">
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
</div>

<!-- Modal Edit Kategori (Quick Edit) -->
<div id="modalEditCategory" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: var(--white); width: 100%; max-width: 440px; border-radius: 16px; box-shadow: var(--shadow-xl); overflow: hidden; animation: popIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);">
        
        <!-- Modal Header -->
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--dark); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-edit text-accent"></i> Edit Nama Kategori
            </h3>
            <button type="button" onclick="closeEditCategoryModal()" style="background: none; border: none; font-size: 1.25rem; color: var(--muted); cursor: pointer; padding: 0.25rem;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <form action="kategori.php" method="POST" style="padding: 1.5rem;">
            <input type="hidden" name="edit" value="1">
            <input type="hidden" name="id" id="modal_category_id" value="">

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 0.4rem;">Nama Kategori</label>
                <input type="text" name="nama_kategori" id="modal_nama_kategori" class="form-control" required
                       style="width: 100%; padding: 0.75rem 1rem; border-radius: 8px; border: 1.5px solid var(--border); font-size: 1rem;"
                       placeholder="Masukkan nama kategori baru">
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeEditCategoryModal()" class="btn btn-ghost" style="padding: 0.65rem 1.25rem;">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.5rem; font-weight: 600;">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes popIn {
    from { opacity: 0; transform: scale(0.92); }
    to { opacity: 1; transform: scale(1); }
}
</style>

<script>
function openEditCategoryModal(id, currentName) {
    document.getElementById('modal_category_id').value = id;
    document.getElementById('modal_nama_kategori').value = currentName;
    const modal = document.getElementById('modalEditCategory');
    modal.style.display = 'flex';
    setTimeout(() => {
        document.getElementById('modal_nama_kategori').focus();
        document.getElementById('modal_nama_kategori').select();
    }, 50);
}

function closeEditCategoryModal() {
    document.getElementById('modalEditCategory').style.display = 'none';
}

// Realtime Category Table Search Filter
function filterCategoryTable() {
    const input = document.getElementById('searchCategoryInput');
    const filter = input.value.toLowerCase().trim();
    const table = document.getElementById('categoryTable');
    const trs = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    for (let i = 0; i < trs.length; i++) {
        const td = trs[i].getElementsByTagName('td')[1]; // Nama Kategori column
        if (td) {
            const txtValue = td.textContent || td.innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                trs[i].style.display = '';
            } else {
                trs[i].style.display = 'none';
            }
        }
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
