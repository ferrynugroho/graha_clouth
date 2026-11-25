<?php
require_once 'includes/header.php';

if (is_logged_in()) { header("Location: index.php"); exit; }

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama        = trim($_POST['nama']);
    $email       = trim($_POST['email']);
    $no_whatsapp = trim($_POST['no_whatsapp']);
    $password    = $_POST['password'];
    $konfirmasi  = $_POST['konfirmasi_password'];

    if ($password !== $konfirmasi) {
        $error = "Konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt_check->execute([':email' => $email]);
        if ($stmt_check->rowCount() > 0) {
            $error = "Email sudah terdaftar. Silakan gunakan email lain atau login.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $conn->prepare("INSERT INTO users (nama, email, no_whatsapp, password, role) VALUES (:nama, :email, :wa, :pass, 'customer')");
            if ($stmt->execute([':nama'=>$nama, ':email'=>$email, ':wa'=>$no_whatsapp, ':pass'=>$hashed])) {
                $success = "Pendaftaran berhasil!";
            } else {
                $error = "Terjadi kesalahan. Silakan coba lagi.";
            }
        }
    }
}
?>
<style>
body { margin: 0; padding: 0; background: linear-gradient(135deg, #FEFEFE 0%, #FDF8F3 50%, #EFF6FF 100%) !important; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
.auth-wrap { width: 100%; display: flex; align-items: center; justify-content: center; padding: 2rem; min-height: 100vh; }
.auth-card { width: 100%; max-width: 500px; background: #fff; border-radius: 24px; box-shadow: 0 16px 48px rgba(0,0,0,0.12); padding: 2.75rem; border: 1px solid #E8EAED; }
.auth-logo-link { display: block; text-align: center; font-size: 1.6rem; font-weight: 800; color: #1A1A2E; text-decoration: none; letter-spacing: -0.5px; margin-bottom: 0.25rem; }
.auth-logo-link span { color: #2563EB; }
.auth-logo-sub { text-align: center; color: #6B7280; font-size: 0.8rem; margin-bottom: 2rem; }
.auth-title { font-size: 1.5rem; font-weight: 800; color: #1A1A2E; margin-bottom: 0.4rem; }
.auth-subtitle { color: #6B7280; font-size: 0.875rem; margin-bottom: 1.5rem; }
.form-group { margin-bottom: 1.1rem; }
.form-label { display: block; font-weight: 600; font-size: 0.875rem; color: #1A1A2E; margin-bottom: 0.45rem; }
.form-control { width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #E8EAED; border-radius: 12px; font-family: 'Inter', sans-serif; font-size: 0.875rem; color: #1A1A2E; background: #fff; transition: all 0.25s ease; outline: none; box-sizing: border-box; }
.form-control:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.12); }
.form-control::placeholder { color: #9CA3AF; }
.form-hint { font-size: 0.75rem; color: #9CA3AF; margin-top: 0.3rem; }
.pw-wrap { position: relative; }
.pw-toggle { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9CA3AF; cursor: pointer; font-size: 0.875rem; }
.btn-main { display: flex; width: 100%; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.9rem 1.75rem; background: #2563EB; color: #fff; border: 2px solid #2563EB; border-radius: 9999px; font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: all 0.25s ease; font-family: 'Inter', sans-serif; text-decoration: none; }
.btn-main:hover { background: #1D4ED8; border-color: #1D4ED8; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37,99,235,0.35); }
.btn-secondary { display: flex; width: 100%; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.9rem 1.75rem; background: transparent; color: #2563EB; border: 2px solid #2563EB; border-radius: 9999px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.25s ease; font-family: 'Inter', sans-serif; text-decoration: none; margin-top: 0.75rem; }
.btn-secondary:hover { background: #2563EB; color: #fff; transform: translateY(-2px); }
.alert-err { background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; border-radius: 12px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 500; display: flex; align-items: center; gap: 0.5rem; }
.alert-ok { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; border-radius: 12px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 500; display: flex; align-items: center; gap: 0.5rem; }
.divider { display: flex; align-items: center; gap: 1rem; margin: 1.25rem 0; color: #9CA3AF; font-size: 0.82rem; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #E8EAED; }
.auth-footer-text { text-align: center; margin-top: 1.5rem; font-size: 0.8rem; color: #9CA3AF; }
.auth-footer-text a { color: #2563EB; font-weight: 600; }
</style>

<div class="auth-wrap">
    <div class="auth-card">
        <a href="index.php" class="auth-logo-link">GRAHA<span>CLOUTH</span></a>
        <p class="auth-logo-sub">Fashion Modern untuk Semua</p>

        <h1 class="auth-title">Buat Akun Baru</h1>
        <p class="auth-subtitle">Daftar sekarang dan nikmati pengalaman belanja premium</p>

        <?php if ($error): ?>
        <div class="alert-err"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert-ok">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            <a href="login.php" style="font-weight:700;margin-left:0.4rem;color:#065F46;">Login →</a>
        </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label class="form-label" for="nama"><i class="fas fa-user" style="color:#2563EB;margin-right:0.3rem;font-size:0.8rem;"></i> Nama Lengkap</label>
                <input type="text" id="nama" name="nama" class="form-control" required placeholder="Nama lengkap Anda" value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="email"><i class="fas fa-envelope" style="color:#2563EB;margin-right:0.3rem;font-size:0.8rem;"></i> Alamat Email</label>
                <input type="email" id="email" name="email" class="form-control" required placeholder="contoh@email.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="wa"><i class="fab fa-whatsapp" style="color:#2563EB;margin-right:0.3rem;font-size:0.8rem;"></i> Nomor WhatsApp</label>
                <input type="text" id="wa" name="no_whatsapp" class="form-control" required placeholder="08123456789" value="<?php echo htmlspecialchars($_POST['no_whatsapp'] ?? ''); ?>">
                <span class="form-hint">Untuk notifikasi status pesanan</span>
            </div>
            <div class="form-group">
                <label class="form-label" for="password"><i class="fas fa-lock" style="color:#2563EB;margin-right:0.3rem;font-size:0.8rem;"></i> Password</label>
                <div class="pw-wrap">
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Minimal 6 karakter" minlength="6" style="padding-right:3rem;">
                    <button type="button" class="pw-toggle" onclick="togglePw('password',this)"><i class="fas fa-eye"></i></button>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:1.75rem;">
                <label class="form-label" for="konfirmasi"><i class="fas fa-shield-alt" style="color:#2563EB;margin-right:0.3rem;font-size:0.8rem;"></i> Konfirmasi Password</label>
                <div class="pw-wrap">
                    <input type="password" id="konfirmasi" name="konfirmasi_password" class="form-control" required placeholder="Ulangi password Anda" minlength="6" style="padding-right:3rem;">
                    <button type="button" class="pw-toggle" onclick="togglePw('konfirmasi',this)"><i class="fas fa-eye"></i></button>
                </div>
            </div>
            <button type="submit" class="btn-main"><i class="fas fa-user-plus"></i> Daftar Sekarang</button>
        </form>

        <div class="divider">sudah punya akun?</div>
        <a href="login.php" class="btn-secondary"><i class="fas fa-sign-in-alt"></i> Login di Sini</a>

        <div class="auth-footer-text">
            Dengan mendaftar, Anda menyetujui <a href="#">Syarat & Ketentuan</a> dan <a href="#">Kebijakan Privasi</a>.
        </div>
    </div>
</div>

<script>
function togglePw(id, btn) {
    var input = document.getElementById(id);
    var icon  = btn.querySelector('i');
    if (input.type === 'password') { input.type = 'text'; icon.className = 'fas fa-eye-slash'; }
    else { input.type = 'password'; icon.className = 'fas fa-eye'; }
}
</script>
<script src="assets/js/script.js"></script>
</body>
</html>
