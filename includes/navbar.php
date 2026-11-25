<?php
// Cart count
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt_cart = $conn->prepare("SELECT SUM(qty) as total FROM carts WHERE user_id = ?");
        $stmt_cart->execute([$_SESSION['user_id']]);
        $cart_count = (int)($stmt_cart->fetch()['total'] ?? 0);
    } catch(Exception $e) {
        $cart_count = 0;
    }
} elseif (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += ($item['qty'] ?? 1);
    }
}
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Search Overlay -->
<div class="search-overlay" id="searchOverlay">
    <div class="search-box-wrap">
        <form action="produk.php" method="GET">
            <div class="search-input-wrap">
                <i class="fas fa-search"></i>
                <input type="text" name="q" id="searchMainInput" class="search-input-main"
                       placeholder="Cari produk fashion..." autocomplete="off">
                <button type="button" class="search-close" id="searchClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Navbar -->
<nav class="navbar" id="navbar">
    <div class="container nav-container">

        <!-- Logo -->
        <a href="index.php" class="nav-logo">GRAHA<span>CLOUTH</span></a>

        <!-- Desktop Menu -->
        <ul class="nav-menu">
            <li><a href="index.php"    class="<?php echo $current_page=='index.php'   ?'active':''; ?>">Beranda</a></li>
            <li><a href="produk.php"   class="<?php echo $current_page=='produk.php'  ?'active':''; ?>">Produk</a></li>
            <li><a href="kategori.php" class="<?php echo $current_page=='kategori.php'?'active':''; ?>">Kategori</a></li>
            <li><a href="produk.php?promo=1" class="<?php echo isset($_GET['promo'])  ?'active':''; ?>">Promo</a></li>
            <li><a href="tentang.php"  class="<?php echo $current_page=='tentang.php' ?'active':''; ?>">Tentang Kami</a></li>
            <li><a href="kontak.php"   class="<?php echo $current_page=='kontak.php'  ?'active':''; ?>">Kontak</a></li>
        </ul>

        <!-- Nav Actions -->
        <div class="nav-actions">
            <!-- Search -->
            <button class="nav-action-btn" id="searchTrigger" title="Cari Produk" aria-label="Cari">
                <i class="fas fa-search"></i>
            </button>

            <!-- Cart -->
            <a href="keranjang.php" class="nav-action-btn" title="Keranjang" aria-label="Keranjang">
                <i class="fas fa-shopping-bag"></i>
                <?php if ($cart_count > 0): ?>
                    <span class="nav-badge"><?php echo $cart_count > 9 ? '9+' : $cart_count; ?></span>
                <?php endif; ?>
            </a>

            <!-- Account -->
            <?php if (is_logged_in()): ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin/index.php" class="btn-login-nav">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                <?php else: ?>
                    <a href="profil.php" class="nav-action-btn" title="Profil Saya" aria-label="Profil">
                        <i class="fas fa-user-circle"></i>
                    </a>
                    <a href="logout.php" class="btn-login-nav" style="background:transparent; color:var(--accent); border-color:var(--accent);">
                        <i class="fas fa-sign-out-alt"></i> Keluar
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php" class="btn-login-nav">
                    <i class="fas fa-user"></i> Login
                </a>
            <?php endif; ?>

            <!-- Hamburger -->
            <button class="hamburger" id="hamburger" aria-label="Menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>

<!-- Mobile Nav -->
<div class="mobile-nav" id="mobileNav">
    <a href="index.php"    class="<?php echo $current_page=='index.php'   ?'active':''; ?>"><i class="fas fa-home"></i> Beranda</a>
    <a href="produk.php"   class="<?php echo $current_page=='produk.php'  ?'active':''; ?>"><i class="fas fa-tshirt"></i> Produk</a>
    <a href="kategori.php" class="<?php echo $current_page=='kategori.php'?'active':''; ?>"><i class="fas fa-tags"></i> Kategori</a>
    <a href="produk.php?promo=1"><i class="fas fa-percent"></i> Promo</a>
    <a href="tentang.php"  class="<?php echo $current_page=='tentang.php' ?'active':''; ?>"><i class="fas fa-info-circle"></i> Tentang Kami</a>
    <a href="kontak.php"   class="<?php echo $current_page=='kontak.php'  ?'active':''; ?>"><i class="fas fa-envelope"></i> Kontak</a>
    <?php if (is_logged_in()): ?>
        <a href="profil.php"><i class="fas fa-user"></i> Profil Saya</a>
        <a href="logout.php" style="color:var(--danger);"><i class="fas fa-sign-out-alt"></i> Keluar</a>
    <?php else: ?>
        <a href="login.php" style="background:var(--accent);color:white;border-color:var(--accent);"><i class="fas fa-sign-in-alt"></i> Login</a>
        <a href="register.php"><i class="fas fa-user-plus"></i> Daftar</a>
    <?php endif; ?>
</div>
