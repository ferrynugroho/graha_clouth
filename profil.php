<?php
require_once 'includes/header.php';
require_once 'includes/navbar.php';

if (!is_logged_in() || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $no_whatsapp = trim($_POST['no_whatsapp']);
    $alamat = trim($_POST['alamat']);
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
    $stmt_check->execute([':email' => $email, ':id' => $user_id]);
    if ($stmt_check->rowCount() > 0) {
        $error = "Email sudah digunakan oleh akun lain.";
    } else {
        $foto_query = "";
        $params = [':nama' => $nama, ':email' => $email, ':wa' => $no_whatsapp, ':alamat' => $alamat, ':id' => $user_id];

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $new_filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
                $upload_path = 'uploads/' . $new_filename;
                if (!is_dir('uploads')) mkdir('uploads', 0777, true);
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                    $foto_query = ", foto = :foto";
                    $params[':foto'] = $new_filename;
                }
            } else {
                $error = "Format foto tidak didukung (hanya JPG, PNG, WebP).";
            }
        }

        if (empty($error)) {
            if (!empty($password_baru) || !empty($konfirmasi_password)) {
                if ($password_baru !== $konfirmasi_password) {
                    $error = "Konfirmasi password baru tidak cocok.";
                } else {
                    $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
                    $params[':pass'] = $hashed_password;
                    $stmt = $conn->prepare("UPDATE users SET nama=:nama, email=:email, no_whatsapp=:wa, alamat=:alamat {$foto_query}, password=:pass WHERE id=:id");
                    if ($stmt->execute($params)) { $success = "Profil & password berhasil diperbarui."; $_SESSION['nama'] = $nama; }
                    else $error = "Gagal mengupdate profil.";
                }
            } else {
                $stmt = $conn->prepare("UPDATE users SET nama=:nama, email=:email, no_whatsapp=:wa, alamat=:alamat {$foto_query} WHERE id=:id");
                if ($stmt->execute($params)) { $success = "Profil berhasil diperbarui."; $_SESSION['nama'] = $nama; }
                else $error = "Gagal mengupdate profil.";
            }
        }
    }
}

// Refresh user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch();

// Orders
$stmt_orders = $conn->prepare("
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_details WHERE order_id = o.id) as item_count
    FROM orders o 
    WHERE o.user_id = :id 
    ORDER BY o.created_at DESC
");
$stmt_orders->execute([':id' => $user_id]);
$orders = $stmt_orders->fetchAll();

// Order items per order
foreach ($orders as &$order) {
    try {
        $stmt_items = $conn->prepare("
            SELECT oi.*, p.gambar, p.nama_produk 
            FROM order_details oi 
            LEFT JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = :oid
        ");
        $stmt_items->execute([':oid' => $order['id']]);
        $order['items'] = $stmt_items->fetchAll();
    } catch(Exception $e) {
        $order['items'] = [];
    }
}
unset($order);

// Stats
$total_orders   = count($orders);
$selesai_count  = count(array_filter($orders, fn($o) => $o['status'] === 'Selesai'));
$total_belanja  = array_sum(array_column($orders, 'total'));
$avatar_letter  = strtoupper(substr($user['nama'], 0, 1));
$has_photo      = !empty($user['foto']) && file_exists('uploads/' . $user['foto']);
?>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="index.php">Beranda</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:0.65rem;"></i></span>
            <span class="current">Profil Saya</span>
        </nav>
    </div>
</div>

<section class="prf-section">
    <div class="container">

        <!-- Alerts -->
        <?php if ($error): ?>
        <div class="prf-alert prf-alert-danger" data-auto-dismiss="5000">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="prf-alert prf-alert-success" data-auto-dismiss="4000">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>

        <div class="prf-grid">

            <!-- ========== LEFT SIDEBAR ========== -->
            <div class="prf-sidebar">

                <!-- Avatar Card -->
                <div class="prf-avatar-card">
                    <div class="prf-avatar-bg"></div>
                    <div class="prf-avatar-content">
                        <!-- Avatar -->
                        <div class="prf-avatar-wrap" id="avatarWrap">
                            <?php if ($has_photo): ?>
                                <img src="uploads/<?php echo htmlspecialchars($user['foto']); ?>"
                                     alt="Foto Profil"
                                     class="prf-avatar-img"
                                     id="avatarPreview">
                            <?php else: ?>
                                <div class="prf-avatar-initial" id="avatarPreview">
                                    <?php echo $avatar_letter; ?>
                                </div>
                            <?php endif; ?>
                            <!-- Camera overlay -->
                            <label for="foto" class="prf-avatar-cam" title="Ganti Foto">
                                <i class="fas fa-camera"></i>
                            </label>
                        </div>

                        <h2 class="prf-user-name"><?php echo htmlspecialchars($user['nama']); ?></h2>
                        <p class="prf-user-email">
                            <i class="fas fa-envelope"></i>
                            <?php echo htmlspecialchars($user['email']); ?>
                        </p>

                        <?php if (!empty($user['no_whatsapp'])): ?>
                        <p class="prf-user-wa">
                            <i class="fab fa-whatsapp"></i>
                            <?php echo htmlspecialchars($user['no_whatsapp']); ?>
                        </p>
                        <?php endif; ?>

                        <!-- Member Badge -->
                        <div class="prf-member-badge">
                            <i class="fas fa-award"></i>
                            Member Aktif
                        </div>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="prf-stats-row">
                    <div class="prf-stat-item">
                        <div class="prf-stat-num"><?php echo $total_orders; ?></div>
                        <div class="prf-stat-lbl">Pesanan</div>
                    </div>
                    <div class="prf-stat-divider"></div>
                    <div class="prf-stat-item">
                        <div class="prf-stat-num"><?php echo $selesai_count; ?></div>
                        <div class="prf-stat-lbl">Selesai</div>
                    </div>
                    <div class="prf-stat-divider"></div>
                    <div class="prf-stat-item">
                        <div class="prf-stat-num" style="font-size:0.9rem;">
                            <?php
                            if ($total_belanja >= 1000000) echo 'Rp '.number_format($total_belanja/1000000,1).'jt';
                            else echo format_rupiah($total_belanja);
                            ?>
                        </div>
                        <div class="prf-stat-lbl">Total Belanja</div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="prf-quick-links">
                    <a href="produk.php" class="prf-quick-link">
                        <i class="fas fa-tshirt"></i>
                        <span>Belanja Lagi</span>
                        <i class="fas fa-chevron-right prf-ql-arrow"></i>
                    </a>
                    <a href="keranjang.php" class="prf-quick-link">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Keranjang</span>
                        <i class="fas fa-chevron-right prf-ql-arrow"></i>
                    </a>
                    <a href="logout.php" class="prf-quick-link prf-quick-link-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Keluar</span>
                        <i class="fas fa-chevron-right prf-ql-arrow"></i>
                    </a>
                </div>

            </div><!-- end sidebar -->

            <!-- ========== RIGHT CONTENT ========== -->
            <div class="prf-main">

                <!-- ---- EDIT PROFILE FORM ---- -->
                <div class="prf-card">
                    <div class="prf-card-header">
                        <div class="prf-card-icon">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <div>
                            <div class="prf-card-title">Edit Profil</div>
                            <div class="prf-card-subtitle">Perbarui informasi akun Anda</div>
                        </div>
                    </div>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="update_profile" value="1">
                        <input type="file" id="foto" name="foto" style="display:none;" accept="image/*" onchange="previewFoto(this)">

                        <div class="prf-form-grid">
                            <div class="prf-form-group">
                                <label class="prf-label">
                                    <i class="fas fa-user"></i> Nama Lengkap
                                </label>
                                <input type="text" name="nama" class="prf-input"
                                       value="<?php echo htmlspecialchars($user['nama']); ?>" required
                                       placeholder="Masukkan nama lengkap">
                            </div>

                            <div class="prf-form-group">
                                <label class="prf-label">
                                    <i class="fas fa-envelope"></i> Alamat Email
                                </label>
                                <input type="email" name="email" class="prf-input"
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required
                                       placeholder="nama@email.com">
                            </div>

                            <div class="prf-form-group">
                                <label class="prf-label">
                                    <i class="fab fa-whatsapp"></i> Nomor WhatsApp
                                </label>
                                <input type="text" name="no_whatsapp" class="prf-input"
                                       value="<?php echo htmlspecialchars($user['no_whatsapp'] ?? ''); ?>"
                                       placeholder="Contoh: 08123456789">
                            </div>

                            <div class="prf-form-group prf-form-full">
                                <label class="prf-label">
                                    <i class="fas fa-map-marker-alt"></i> Alamat Lengkap
                                </label>
                                <textarea name="alamat" class="prf-input prf-textarea"
                                          placeholder="Jl. Nama Jalan No. X, Kelurahan, Kecamatan, Kota, Kode Pos"
                                          rows="3"><?php echo htmlspecialchars($user['alamat'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Password Change Section -->
                        <div class="prf-section-divider">
                            <span><i class="fas fa-lock"></i> Ganti Password</span>
                            <span class="prf-optional-tag">Opsional</span>
                        </div>

                        <div class="prf-form-grid">
                            <div class="prf-form-group">
                                <label class="prf-label">
                                    <i class="fas fa-key"></i> Password Baru
                                </label>
                                <div class="prf-input-icon-wrap">
                                    <input type="password" name="password_baru" class="prf-input prf-input-icon"
                                           placeholder="Biarkan kosong jika tidak diganti" id="passNew">
                                    <button type="button" class="prf-eye-btn" onclick="togglePass('passNew', this)">
                                        <i class="far fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="prf-form-group">
                                <label class="prf-label">
                                    <i class="fas fa-shield-alt"></i> Konfirmasi Password
                                </label>
                                <div class="prf-input-icon-wrap">
                                    <input type="password" name="konfirmasi_password" class="prf-input prf-input-icon"
                                           placeholder="Ulangi password baru" id="passConfirm">
                                    <button type="button" class="prf-eye-btn" onclick="togglePass('passConfirm', this)">
                                        <i class="far fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="prf-form-actions">
                            <button type="reset" class="prf-btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="submit" class="prf-btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ---- ORDER HISTORY ---- -->
                <div class="prf-card">
                    <div class="prf-card-header">
                        <div class="prf-card-icon" style="background:#FEF3C7;color:#D97706;">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div style="flex:1;">
                            <div class="prf-card-title">Riwayat Pesanan</div>
                            <div class="prf-card-subtitle">Semua transaksi belanja Anda</div>
                        </div>
                        <span class="prf-order-count-badge"><?php echo $total_orders; ?> Pesanan</span>
                    </div>

                    <?php if (!empty($orders)): ?>
                    <div class="prf-orders-list">
                        <?php foreach ($orders as $o):
                            // Status config
                            $sc = ['color'=>'#64748B','bg'=>'#F1F5F9','icon'=>'fa-clock','txt'=>$o['status']];
                            if ($o['status'] == 'Pesanan Baru')        { $sc = ['color'=>'#D97706','bg'=>'#FEF3C7','icon'=>'fa-clock','txt'=>'Menunggu Konfirmasi']; }
                            elseif (in_array($o['status'],['Diproses','Dikemas'])) { $sc = ['color'=>'#2563EB','bg'=>'#EFF6FF','icon'=>'fa-box','txt'=>'Sedang Dikemas']; }
                            elseif ($o['status'] == 'Dikirim')          { $sc = ['color'=>'#0284C7','bg'=>'#E0F2FE','icon'=>'fa-truck-fast','txt'=>'Dalam Pengiriman']; }
                            elseif ($o['status'] == 'Selesai')          { $sc = ['color'=>'#16A34A','bg'=>'#DCFCE7','icon'=>'fa-circle-check','txt'=>'Selesai']; }
                            elseif ($o['status'] == 'Dibatalkan')       { $sc = ['color'=>'#DC2626','bg'=>'#FEE2E2','icon'=>'fa-circle-xmark','txt'=>'Dibatalkan']; }
                        ?>
                        <div class="prf-order-card">
                            <!-- Order Header -->
                            <div class="prf-order-head">
                                <div class="prf-order-meta">
                                    <span class="prf-order-no">#<?php echo htmlspecialchars($o['nomor_order']); ?></span>
                                    <span class="prf-order-date">
                                        <i class="far fa-calendar-alt"></i>
                                        <?php echo date('d M Y', strtotime($o['created_at'])); ?>
                                    </span>
                                </div>
                                <span class="prf-status-badge" style="background:<?php echo $sc['bg']; ?>;color:<?php echo $sc['color']; ?>;">
                                    <i class="fas <?php echo $sc['icon']; ?>"></i>
                                    <?php echo $sc['txt']; ?>
                                </span>
                            </div>

                            <!-- Order Items -->
                            <div class="prf-order-items">
                                <?php if (!empty($o['items'])): ?>
                                    <?php foreach (array_slice($o['items'], 0, 2) as $item): ?>
                                    <div class="prf-order-item-row">
                                        <img src="<?php echo htmlspecialchars($item['gambar'] ?? ''); ?>"
                                             alt="<?php echo htmlspecialchars($item['nama_produk'] ?? 'Produk'); ?>"
                                             class="prf-item-thumb"
                                             onerror="this.src='https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=100&h=100&fit=crop';">
                                        <div class="prf-item-info">
                                            <div class="prf-item-name">
                                                <?php echo htmlspecialchars($item['nama_produk'] ?? 'Produk'); ?>
                                            </div>
                                            <div class="prf-item-tags">
                                                <?php if (!empty($item['ukuran'])): ?>
                                                <span class="prf-tag"><i class="fas fa-ruler"></i> <?php echo htmlspecialchars($item['ukuran']); ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($item['warna'])): ?>
                                                <span class="prf-tag"><i class="fas fa-circle" style="font-size:0.5rem;"></i> <?php echo htmlspecialchars($item['warna']); ?></span>
                                                <?php endif; ?>
                                                <span class="prf-tag"><?php echo ($item['jumlah'] ?? 1); ?>x</span>
                                            </div>
                                        </div>
                                        <div class="prf-item-price">
                                            <?php echo format_rupiah($item['subtotal'] ?? ($item['harga'] * ($item['jumlah'] ?? 1))); ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>

                                    <?php if (count($o['items']) > 2): ?>
                                    <div class="prf-more-items">
                                        +<?php echo count($o['items']) - 2; ?> produk lainnya
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="prf-no-items">Informasi produk tidak tersedia</p>
                                <?php endif; ?>
                            </div>

                            <!-- Order Footer -->
                            <div class="prf-order-foot">
                                <div class="prf-order-methods">
                                    <?php if (!empty($o['metode_pengiriman'])): ?>
                                    <span class="prf-method-tag">
                                        <i class="fas fa-truck"></i>
                                        <?php echo htmlspecialchars($o['metode_pengiriman']); ?>
                                    </span>
                                    <?php endif; ?>
                                    <?php if (!empty($o['metode_pembayaran'])): ?>
                                    <span class="prf-method-tag">
                                        <i class="fas fa-credit-card"></i>
                                        <?php echo htmlspecialchars($o['metode_pembayaran']); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <div class="prf-order-total-group">
                                    <div class="prf-order-total-wrap">
                                        <span class="prf-total-label">Total Pembayaran</span>
                                        <span class="prf-total-amount"><?php echo format_rupiah($o['total']); ?></span>
                                    </div>
                                    <a href="detail_pesanan.php?id=<?php echo $o['id']; ?>" class="prf-detail-btn">
                                        <i class="fas fa-file-invoice"></i> Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <?php else: ?>
                    <div class="prf-empty-orders">
                        <div class="prf-empty-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <h4>Belum Ada Pesanan</h4>
                        <p>Anda belum melakukan transaksi. Yuk mulai belanja!</p>
                        <a href="produk.php" class="prf-btn-primary" style="display:inline-flex;gap:0.4rem;">
                            <i class="fas fa-compass"></i> Jelajahi Produk
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

            </div><!-- end main -->
        </div><!-- end prf-grid -->
    </div>
</section>

<style>
/* =====================================================
   PROFIL PAGE – PREMIUM UI
   ===================================================== */
.prf-section {
    padding: 2.5rem 0 5rem;
    background: linear-gradient(135deg, #F8FAFF 0%, #F1F5F9 100%);
    min-height: 70vh;
}

/* Alert Messages */
.prf-alert {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 1rem 1.25rem;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    animation: alertSlide 0.4s ease both;
}
@keyframes alertSlide {
    from { opacity: 0; transform: translateY(-10px); }
    to   { opacity: 1; transform: translateY(0); }
}
.prf-alert-danger  { background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; }
.prf-alert-success { background: #DCFCE7; color: #166534; border: 1px solid #A7F3D0; }

/* Grid Layout */
.prf-grid {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 2rem;
    align-items: start;
}

/* ---- SIDEBAR ---- */
.prf-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    position: sticky;
    top: 90px;
}

/* Avatar Card */
.prf-avatar-card {
    background: white;
    border-radius: 24px;
    border: 1px solid #E2E8F0;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    overflow: hidden;
    position: relative;
}
.prf-avatar-bg {
    height: 90px;
    background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 50%, #7C3AED 100%);
    position: relative;
}
.prf-avatar-bg::after {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.06'%3E%3Ccircle cx='30' cy='30' r='20'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.prf-avatar-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0 1.75rem 1.75rem;
    text-align: center;
}

.prf-avatar-wrap {
    position: relative;
    margin-top: -48px;
    margin-bottom: 1rem;
}
.prf-avatar-img {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    display: block;
}
.prf-avatar-initial {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563EB, #7C3AED);
    color: white;
    font-size: 2.2rem;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 4px solid white;
    box-shadow: 0 8px 24px rgba(37,99,235,0.25);
    letter-spacing: -1px;
}
.prf-avatar-cam {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 32px;
    height: 32px;
    background: var(--accent, #2563EB);
    border-radius: 50%;
    border: 2.5px solid white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: white;
    font-size: 0.78rem;
    box-shadow: 0 4px 10px rgba(37,99,235,0.4);
    transition: all 0.2s ease;
}
.prf-avatar-cam:hover {
    background: #1D4ED8;
    transform: scale(1.1);
}

.prf-user-name {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0 0 0.3rem;
    letter-spacing: -0.3px;
}
.prf-user-email {
    font-size: 0.82rem;
    color: #64748B;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    margin: 0 0 0.25rem;
}
.prf-user-wa {
    font-size: 0.82rem;
    color: #059669;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    margin: 0 0 0.75rem;
}
.prf-member-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: linear-gradient(135deg, #FEF3C7, #FDE68A);
    color: #92400E;
    border: 1px solid #F59E0B;
    border-radius: 999px;
    padding: 0.3rem 0.85rem;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0.25rem;
}

/* Stats Row */
.prf-stats-row {
    background: white;
    border-radius: 16px;
    border: 1px solid #E2E8F0;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
.prf-stat-item {
    flex: 1;
    text-align: center;
}
.prf-stat-num {
    font-size: 1.35rem;
    font-weight: 900;
    color: var(--accent, #2563EB);
    line-height: 1;
    letter-spacing: -0.5px;
}
.prf-stat-lbl {
    font-size: 0.72rem;
    color: #64748B;
    font-weight: 500;
    margin-top: 0.2rem;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.prf-stat-divider {
    width: 1px;
    height: 36px;
    background: #E2E8F0;
}

/* Quick Links */
.prf-quick-links {
    background: white;
    border-radius: 16px;
    border: 1px solid #E2E8F0;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
.prf-quick-link {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.9rem 1.25rem;
    font-size: 0.88rem;
    font-weight: 600;
    color: #334155;
    border-bottom: 1px solid #F1F5F9;
    transition: all 0.2s ease;
    text-decoration: none;
}
.prf-quick-link:last-child { border-bottom: none; }
.prf-quick-link:hover {
    background: #F8FAFC;
    color: var(--accent, #2563EB);
    padding-left: 1.5rem;
}
.prf-quick-link i:first-child {
    width: 36px;
    height: 36px;
    background: #F1F5F9;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    color: #475569;
    flex-shrink: 0;
    transition: all 0.2s ease;
}
.prf-quick-link:hover i:first-child {
    background: #EFF6FF;
    color: var(--accent, #2563EB);
}
.prf-quick-link span { flex: 1; }
.prf-ql-arrow { font-size: 0.7rem; color: #CBD5E1; }
.prf-quick-link-danger { color: #DC2626; }
.prf-quick-link-danger i:first-child { color: #DC2626; }
.prf-quick-link-danger:hover { background: #FFF5F5; color: #DC2626; }
.prf-quick-link-danger:hover i:first-child { background: #FEE2E2; color: #DC2626; }

/* ---- MAIN CONTENT ---- */
.prf-main {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Card */
.prf-card {
    background: white;
    border-radius: 24px;
    border: 1px solid #E2E8F0;
    box-shadow: 0 4px 24px rgba(0,0,0,0.05);
    overflow: hidden;
}
.prf-card-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem 1.75rem;
    border-bottom: 1px solid #F1F5F9;
    background: #FAFBFD;
}
.prf-card-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: #EFF6FF;
    color: var(--accent, #2563EB);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.prf-card-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0F172A;
    margin-bottom: 0.1rem;
}
.prf-card-subtitle {
    font-size: 0.8rem;
    color: #64748B;
}
.prf-order-count-badge {
    background: #EFF6FF;
    color: var(--accent, #2563EB);
    border-radius: 999px;
    padding: 0.3rem 0.85rem;
    font-size: 0.8rem;
    font-weight: 800;
    white-space: nowrap;
    margin-left: auto;
}

/* Form inside card */
form { padding: 1.75rem; }

.prf-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.1rem;
    margin-bottom: 1.25rem;
}
.prf-form-full { grid-column: 1 / -1; }
.prf-form-group { display: flex; flex-direction: column; gap: 0.4rem; }

.prf-label {
    font-size: 0.82rem;
    font-weight: 700;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.prf-label i { color: var(--accent, #2563EB); width: 14px; text-align: center; }

.prf-input {
    width: 100%;
    padding: 0.7rem 1rem;
    border: 1.5px solid #E2E8F0;
    border-radius: 12px;
    font-size: 0.9rem;
    color: #0F172A;
    font-family: inherit;
    background: white;
    transition: all 0.2s ease;
    outline: none;
}
.prf-input:focus {
    border-color: var(--accent, #2563EB);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
.prf-textarea {
    resize: none;
    line-height: 1.6;
}

/* Password input with eye toggle */
.prf-input-icon-wrap { position: relative; }
.prf-input-icon { padding-right: 3rem; }
.prf-eye-btn {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #94A3B8;
    font-size: 0.95rem;
    transition: color 0.2s ease;
    padding: 0.25rem;
}
.prf-eye-btn:hover { color: var(--accent, #2563EB); }

/* Section divider */
.prf-section-divider {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 0;
    margin-bottom: 1rem;
    border-top: 1.5px dashed #E2E8F0;
    font-size: 0.82rem;
    font-weight: 700;
    color: #64748B;
}
.prf-section-divider span:first-child { display: flex; align-items: center; gap: 0.4rem; }
.prf-section-divider i { color: var(--accent, #2563EB); }
.prf-optional-tag {
    background: #F1F5F9;
    color: #94A3B8;
    border-radius: 999px;
    padding: 0.15rem 0.6rem;
    font-size: 0.72rem;
    font-weight: 600;
}

/* Form Actions */
.prf-form-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
    padding-top: 1.25rem;
    border-top: 1px solid #F1F5F9;
    margin-top: 0.5rem;
}
.prf-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.8rem 1.75rem;
    background: var(--accent, #2563EB);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.25s ease;
    box-shadow: 0 4px 14px rgba(37,99,235,0.3);
    text-decoration: none;
    justify-content: center;
}
.prf-btn-primary:hover {
    background: #1D4ED8;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37,99,235,0.4);
    color: white;
}
.prf-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.8rem 1.35rem;
    background: #F8FAFC;
    color: #475569;
    border: 1.5px solid #E2E8F0;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.2s ease;
}
.prf-btn-secondary:hover { background: #F1F5F9; border-color: #CBD5E1; }

/* ---- ORDER CARDS ---- */
.prf-orders-list {
    padding: 1.25rem 1.75rem;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.prf-order-card {
    border: 1.5px solid #E2E8F0;
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.25s ease;
}
.prf-order-card:hover {
    border-color: rgba(37,99,235,0.25);
    box-shadow: 0 8px 24px rgba(37,99,235,0.08);
    transform: translateY(-2px);
}

/* Order Head */
.prf-order-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.85rem 1.25rem;
    background: #F8FAFC;
    border-bottom: 1px solid #E2E8F0;
}
.prf-order-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.prf-order-no {
    font-family: monospace;
    font-size: 0.88rem;
    font-weight: 800;
    color: #0F172A;
    background: white;
    border: 1px solid #CBD5E1;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
}
.prf-order-date {
    font-size: 0.78rem;
    color: #64748B;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}
.prf-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.85rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 800;
}

/* Order Items */
.prf-order-items {
    padding: 1rem 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.prf-order-item-row {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #F8FAFC;
}
.prf-order-item-row:last-of-type { border-bottom: none; padding-bottom: 0; }
.prf-item-thumb {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    object-fit: cover;
    border: 1px solid #E2E8F0;
    background: #F8FAFC;
    flex-shrink: 0;
}
.prf-item-info { flex: 1; min-width: 0; }
.prf-item-name {
    font-size: 0.88rem;
    font-weight: 700;
    color: #0F172A;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 0.35rem;
}
.prf-item-tags { display: flex; flex-wrap: wrap; gap: 0.3rem; }
.prf-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: #F1F5F9;
    color: #475569;
    padding: 0.1rem 0.45rem;
    border-radius: 5px;
    font-size: 0.72rem;
    font-weight: 600;
}
.prf-item-price {
    font-size: 0.9rem;
    font-weight: 800;
    color: #0F172A;
    white-space: nowrap;
}
.prf-more-items {
    font-size: 0.78rem;
    color: var(--accent, #2563EB);
    font-weight: 600;
    padding: 0.4rem 0;
}
.prf-no-items { font-size: 0.82rem; color: #94A3B8; font-style: italic; }

/* Order Foot */
.prf-order-foot {
    padding: 0.85rem 1.25rem;
    background: #FAFBFD;
    border-top: 1px solid #E2E8F0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
}
.prf-order-methods {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.prf-method-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    background: white;
    border: 1px solid #E2E8F0;
    border-radius: 6px;
    padding: 0.2rem 0.6rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #475569;
}
.prf-order-total-group {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.prf-order-total-wrap { text-align: right; }
.prf-total-label { font-size: 0.72rem; color: #64748B; display: block; }
.prf-total-amount {
    font-size: 1.1rem;
    font-weight: 900;
    color: var(--accent, #2563EB);
    letter-spacing: -0.3px;
}
.prf-detail-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.55rem 1.1rem;
    background: white;
    border: 1.5px solid var(--accent, #2563EB);
    color: var(--accent, #2563EB);
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
    white-space: nowrap;
}
.prf-detail-btn:hover {
    background: var(--accent, #2563EB);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37,99,235,0.25);
}

/* Empty State */
.prf-empty-orders {
    text-align: center;
    padding: 3.5rem 2rem;
}
.prf-empty-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #EFF6FF;
    color: var(--accent, #2563EB);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.2rem;
    margin: 0 auto 1.25rem;
}
.prf-empty-orders h4 { font-weight: 800; color: #0F172A; margin-bottom: 0.5rem; }
.prf-empty-orders p { color: #64748B; font-size: 0.9rem; margin-bottom: 1.5rem; }

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
    .prf-grid {
        grid-template-columns: 280px 1fr;
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    .prf-section { padding: 1.5rem 0 5rem; }
    .prf-grid {
        grid-template-columns: 1fr;
    }
    .prf-sidebar { position: static; }
    .prf-avatar-card { margin-bottom: 0; }
    .prf-form-grid {
        grid-template-columns: 1fr;
    }
    .prf-form-full { grid-column: 1; }
    .prf-form-actions { justify-content: stretch; }
    .prf-form-actions button, .prf-form-actions .prf-btn-primary { flex: 1; justify-content: center; }
    form { padding: 1.25rem; }
    .prf-orders-list { padding: 1rem 1.1rem; }
    .prf-order-foot {
        flex-direction: column;
        align-items: flex-start;
    }
    .prf-order-total-group { width: 100%; justify-content: space-between; }
}

@media (max-width: 480px) {
    .prf-card-header { padding: 1.25rem; }
    .prf-card-icon { width: 40px; height: 40px; font-size: 1rem; border-radius: 10px; }
}
</style>

<script>
// Preview foto saat dipilih
function previewFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const wrap = document.getElementById('avatarWrap');
            // Replace preview with img
            let img = wrap.querySelector('.prf-avatar-img, .prf-avatar-initial');
            if (!img || img.tagName !== 'IMG') {
                img = document.createElement('img');
                img.className = 'prf-avatar-img';
                img.id = 'avatarPreview';
                const existing = wrap.querySelector('.prf-avatar-initial');
                if (existing) existing.replaceWith(img);
                else wrap.prepend(img);
            }
            img.src = e.target.result;
            img.style.animation = 'avatarPop 0.4s ease both';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Toggle password visibility
function togglePass(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Auto dismiss alerts
document.querySelectorAll('.prf-alert[data-auto-dismiss]').forEach(el => {
    const delay = parseInt(el.dataset.autoDismiss) || 4000;
    setTimeout(() => {
        el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        el.style.opacity = '0';
        el.style.transform = 'translateY(-8px)';
        setTimeout(() => el.remove(), 420);
    }, delay);
});
</script>

<style>
@keyframes avatarPop {
    from { opacity: 0; transform: scale(0.85); }
    to   { opacity: 1; transform: scale(1); }
}
</style>

<?php require_once 'includes/footer.php'; ?>
