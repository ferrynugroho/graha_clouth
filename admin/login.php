<?php
require_once 'includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, nama, password, role FROM users WHERE email = :email AND role = 'admin'");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        
        header("Location: index.php");
        exit;
    } else {
        $error = "Email atau password salah, atau Anda bukan admin.";
    }
}
?>

<style>
/* Reset page for admin login */
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

.admin-login-wrapper {
    width: 100%;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    box-sizing: border-box;
}

.admin-login-card {
    width: 100%;
    max-width: 440px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 28px;
    padding: 2.75rem 2.25rem;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08),
                0 1px 3px rgba(0, 0, 0, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.8);
    position: relative;
    box-sizing: border-box;
    animation: loginFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes loginFadeUp {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.admin-brand {
    text-align: center;
    margin-bottom: 2rem;
}

.admin-brand-logo {
    font-size: 1.75rem;
    font-weight: 900;
    letter-spacing: -0.5px;
    color: #0F172A;
    text-decoration: none;
    display: inline-block;
}

.admin-brand-logo span {
    color: var(--accent, #2563EB);
}

.admin-portal-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: #EFF6FF;
    color: var(--accent, #2563EB);
    border: 1px solid #BFDBFE;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    margin-top: 0.5rem;
}

.admin-login-header {
    text-align: center;
    margin-bottom: 1.75rem;
}

.admin-login-title {
    font-size: 1.35rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0 0 0.35rem;
}

.admin-login-subtitle {
    font-size: 0.875rem;
    color: #64748B;
    margin: 0;
}

.custom-form-group {
    margin-bottom: 1.25rem;
    text-align: left;
}

.custom-form-label {
    display: block;
    font-size: 0.84rem;
    font-weight: 600;
    color: #1E293B;
    margin-bottom: 0.45rem;
}

.input-icon-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon-wrap .icon-prefix {
    position: absolute;
    left: 1rem;
    color: #94A3B8;
    font-size: 0.95rem;
    pointer-events: none;
    transition: color 0.2s ease;
}

.custom-input {
    width: 100%;
    padding: 0.85rem 1rem 0.85rem 2.75rem;
    border: 1.5px solid #E2E8F0;
    border-radius: 14px;
    font-size: 0.92rem;
    color: #0F172A;
    background: #F8FAFC;
    transition: all 0.25s ease;
    box-sizing: border-box;
    font-family: inherit;
}

.custom-input:focus {
    outline: none;
    background: #FFFFFF;
    border-color: var(--accent, #2563EB);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

.custom-input:focus + .icon-prefix,
.input-icon-wrap:focus-within .icon-prefix {
    color: var(--accent, #2563EB);
}

.pw-toggle-btn {
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

.pw-toggle-btn:hover {
    color: #0F172A;
}

.alert-admin-error {
    background: #FEF2F2;
    border: 1px solid #FECACA;
    color: #991B1B;
    padding: 0.85rem 1rem;
    border-radius: 14px;
    font-size: 0.85rem;
    font-weight: 500;
    margin-bottom: 1.5rem;
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

.btn-admin-submit {
    width: 100%;
    padding: 0.9rem 1.5rem;
    background: var(--accent, #2563EB);
    color: #FFFFFF;
    border: none;
    border-radius: 14px;
    font-size: 0.95rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
    margin-top: 0.5rem;
}

.btn-admin-submit:hover {
    background: #1D4ED8;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
}

.btn-admin-submit:active {
    transform: translateY(0);
}

.admin-login-footer {
    margin-top: 2rem;
    padding-top: 1.25rem;
    border-top: 1px solid #F1F5F9;
    text-align: center;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.back-to-store-link {
    color: #64748B;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    transition: all 0.2s ease;
}

.back-to-store-link:hover {
    color: var(--accent, #2563EB);
    transform: translateX(-3px);
}

.security-note {
    font-size: 0.75rem;
    color: #94A3B8;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
}
</style>

<div class="admin-login-wrapper">
    <div class="admin-login-card">
        
        <!-- Brand Logo & Badge -->
        <div class="admin-brand">
            <a href="../index.php" class="admin-brand-logo">GRAHA<span>CLOUTH</span></a>
            <div>
                <span class="admin-portal-badge">
                    <i class="fas fa-shield-alt"></i> PANEL ADMIN
                </span>
            </div>
        </div>

        <!-- Header -->
        <div class="admin-login-header">
            <h1 class="admin-login-title">Masuk ke Dashboard</h1>
            <p class="admin-login-subtitle">Akses manajemen produk, pesanan, & pelanggan</p>
        </div>
        
        <!-- Error Alert -->
        <?php if($error): ?>
            <div class="alert-admin-error">
                <i class="fas fa-circle-exclamation" style="font-size: 1.1rem; color: #DC2626;"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form action="" method="POST">
            <div class="custom-form-group">
                <label class="custom-form-label" for="adminEmail">Email Admin</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-envelope icon-prefix"></i>
                    <input type="email" 
                           id="adminEmail" 
                           name="email" 
                           class="custom-input" 
                           placeholder="admin@grahaclouth.com" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           required 
                           autocomplete="email"
                           autofocus>
                </div>
            </div>
            
            <div class="custom-form-group">
                <label class="custom-form-label" for="adminPassword">Kata Sandi</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-lock icon-prefix"></i>
                    <input type="password" 
                           id="adminPassword" 
                           name="password" 
                           class="custom-input" 
                           placeholder="••••••••" 
                           style="padding-right: 2.75rem;" 
                           required 
                           autocomplete="current-password">
                    <button type="button" class="pw-toggle-btn" onclick="toggleAdminPw()" title="Lihat/Sembunyikan password">
                        <i class="fas fa-eye" id="pwIcon"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="btn-admin-submit">
                <i class="fas fa-arrow-right-to-bracket"></i> Masuk Sekarang
            </button>
        </form>

        <!-- Footer Note & Back to Store -->
        <div class="admin-login-footer">
            <a href="../index.php" class="back-to-store-link">
                <i class="fas fa-arrow-left"></i> Kembali ke Toko
            </a>
            <div class="security-note">
                <i class="fas fa-lock"></i> Sesi terenkripsi & dilindungi sistem
            </div>
        </div>

    </div>
</div>

<script>
function toggleAdminPw() {
    const pwInput = document.getElementById('adminPassword');
    const pwIcon = document.getElementById('pwIcon');
    if (pwInput.type === 'password') {
        pwInput.type = 'text';
        pwIcon.className = 'fas fa-eye-slash';
    } else {
        pwInput.type = 'password';
        pwIcon.className = 'fas fa-eye';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
