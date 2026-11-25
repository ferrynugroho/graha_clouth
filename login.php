<?php
require_once 'includes/header.php';

if (is_logged_in()) { header("Location: index.php"); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT id, nama, password, role, status FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        if (isset($user['status']) && $user['status'] === 'Nonaktif') {
            $error = "Akun Anda telah dinonaktifkan. Silakan hubungi admin.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];
            if (isset($_SESSION['cart']) && !empty($_SESSION['cart']) && $user['role'] == 'customer') {
                foreach ($_SESSION['cart'] as $item) {
                    $check = $conn->prepare("SELECT id, qty FROM carts WHERE user_id=? AND product_id=? AND ukuran=? AND warna=?");
                    $check->execute([$user['id'], $item['product_id'], $item['ukuran'], $item['warna']]);
                    $exist = $check->fetch();
                    if ($exist) {
                        $conn->prepare("UPDATE carts SET qty=qty+? WHERE id=?")->execute([$item['qty'], $exist['id']]);
                    } else {
                        $conn->prepare("INSERT INTO carts(user_id,product_id,ukuran,warna,qty) VALUES(?,?,?,?,?)")
                             ->execute([$user['id'], $item['product_id'], $item['ukuran'], $item['warna'], $item['qty']]);
                    }
                }
                unset($_SESSION['cart']);
            }
            if (isset($_SESSION['redirect_after_login'])) {
                $r = $_SESSION['redirect_after_login']; unset($_SESSION['redirect_after_login']);
                header("Location: $r");
            } else {
                header("Location: " . ($user['role']=='admin' ? "admin/index.php" : "index.php"));
            }
            exit;
        }
    } else {
        $error = "Email atau password salah. Silakan coba lagi.";
    }
}
?>
<style>
body {
    margin: 0;
    padding: 0;
    min-height: 100vh;
    background: radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(245, 158, 11, 0.08) 0%, transparent 40%),
                linear-gradient(135deg, #F8FAFC 0%, #EFF6FF 50%, #F1F5F9 100%) !important;
    font-family: 'Inter', sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
}
.auth-wrap {
    width: 100%;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    box-sizing: border-box;
}
.auth-card {
    width: 100%;
    max-width: 450px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 28px;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08), 0 1px 3px rgba(0, 0, 0, 0.04);
    padding: 2.75rem 2.25rem;
    border: 1px solid rgba(255, 255, 255, 0.8);
    box-sizing: border-box;
    animation: loginFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes loginFadeUp {
    from { opacity: 0; transform: translateY(20px) scale(0.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
.auth-logo-link {
    display: block;
    text-align: center;
    font-size: 1.75rem;
    font-weight: 900;
    color: #0F172A;
    text-decoration: none;
    letter-spacing: -0.5px;
    margin-bottom: 0.25rem;
}
.auth-logo-link span { color: var(--accent, #2563EB); }
.auth-logo-sub {
    text-align: center;
    color: #64748B;
    font-size: 0.82rem;
    font-weight: 500;
    margin-bottom: 1.75rem;
}
.auth-title {
    font-size: 1.4rem;
    font-weight: 800;
    color: #0F172A;
    margin-bottom: 0.35rem;
    text-align: center;
}
.auth-subtitle {
    color: #64748B;
    font-size: 0.88rem;
    margin-bottom: 1.75rem;
    text-align: center;
}
.form-group { margin-bottom: 1.25rem; }
.form-label {
    display: block;
    font-weight: 600;
    font-size: 0.84rem;
    color: #1E293B;
    margin-bottom: 0.45rem;
}
.input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.input-wrap .input-icon {
    position: absolute;
    left: 1rem;
    color: #94A3B8;
    font-size: 0.95rem;
    pointer-events: none;
    transition: color 0.2s ease;
}
.form-control {
    width: 100%;
    padding: 0.85rem 1rem 0.85rem 2.75rem;
    border: 1.5px solid #E2E8F0;
    border-radius: 14px;
    font-family: 'Inter', sans-serif;
    font-size: 0.92rem;
    color: #0F172A;
    background: #F8FAFC;
    transition: all 0.25s ease;
    outline: none;
    box-sizing: border-box;
}
.form-control:focus {
    background: #FFFFFF;
    border-color: var(--accent, #2563EB);
    box-shadow: 0 0 0 4px rgba(37,99,235,0.12);
}
.input-wrap:focus-within .input-icon { color: var(--accent, #2563EB); }
.pw-toggle {
    position: absolute;
    right: 0.85rem;
    background: none;
    border: none;
    color: #94A3B8;
    cursor: pointer;
    padding: 0.4rem;
    font-size: 0.9rem;
    border-radius: 8px;
    transition: color 0.2s ease;
}
.pw-toggle:hover { color: #0F172A; }
.btn-main {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    padding: 0.9rem 1.5rem;
    background: var(--accent, #2563EB);
    color: #FFFFFF;
    border: none;
    border-radius: 14px;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
    text-decoration: none;
}
.btn-main:hover {
    background: #1D4ED8;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
}
.btn-secondary {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    padding: 0.85rem 1.5rem;
    background: #FFFFFF;
    color: var(--accent, #2563EB);
    border: 1.5px solid #BFDBFE;
    border-radius: 14px;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.25s ease;
    text-decoration: none;
    box-sizing: border-box;
}
.btn-secondary:hover {
    background: #EFF6FF;
    border-color: var(--accent, #2563EB);
    transform: translateY(-2px);
}
.alert-err {
    background: #FEF2F2;
    color: #991B1B;
    border: 1px solid #FECACA;
    border-radius: 14px;
    padding: 0.85rem 1rem;
    margin-bottom: 1.25rem;
    font-size: 0.85rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    animation: alertShake 0.35s ease-in-out;
}
@keyframes alertShake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-5px); }
    40%, 80% { transform: translateX(5px); }
}
.divider {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin: 1.5rem 0;
    color: #94A3B8;
    font-size: 0.82rem;
    font-weight: 500;
}
.divider::before, .divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #E2E8F0;
}
.auth-footer-text {
    text-align: center;
    margin-top: 1.75rem;
    font-size: 0.8rem;
    color: #94A3B8;
    line-height: 1.5;
}
.auth-footer-text a { color: var(--accent, #2563EB); font-weight: 600; }
.back-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    color: #64748B;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    margin-top: 1rem;
    width: 100%;
    transition: color 0.2s ease;
}
.back-link:hover { color: var(--accent, #2563EB); }
</style>

<div class="auth-wrap">
    <div class="auth-card">
        <a href="index.php" class="auth-logo-link">GRAHA<span>CLOUTH</span></a>
        <p class="auth-logo-sub">Fashion Modern & Terpercaya</p>

        <h1 class="auth-title">Selamat Datang Kembali</h1>
        <p class="auth-subtitle">Masuk ke akun Anda untuk melanjutkan belanja</p>

        <?php if ($error): ?>
        <div class="alert-err">
            <i class="fas fa-circle-exclamation" style="font-size:1.1rem; color:#DC2626;"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label class="form-label" for="email">Alamat Email</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" class="form-control"
                           required placeholder="nama@email.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           autocomplete="email" autofocus>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Kata Sandi</label>
                <div class="input-wrap">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" class="form-control"
                           required placeholder="••••••••" style="padding-right: 2.75rem;"
                           autocomplete="current-password">
                    <button type="button" class="pw-toggle" onclick="togglePw('password',this)" title="Lihat/Sembunyikan password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-main" style="margin-top: 0.5rem;">
                <i class="fas fa-arrow-right-to-bracket"></i> Masuk Sekarang
            </button>
        </form>

        <div class="divider">Belum punya akun?</div>

        <a href="register.php" class="btn-secondary">
            <i class="fas fa-user-plus"></i> Daftar Akun Baru
        </a>

        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
        </a>

        <div class="auth-footer-text">
            Dengan masuk, Anda menyetujui <a href="tentang.php">Syarat & Ketentuan</a> GrahaClouth.
        </div>
    </div>
</div>

<script>
function togglePw(id, btn) {
    var input = document.getElementById(id);
    var icon  = btn.querySelector('i');
    if (input.type === 'password') { 
        input.type = 'text'; 
        icon.className = 'fas fa-eye-slash'; 
    } else { 
        input.type = 'password'; 
        icon.className = 'fas fa-eye'; 
    }
}
</script>

<script src="assets/js/script.js"></script>
</body>
</html>
