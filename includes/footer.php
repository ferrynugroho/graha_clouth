<?php
// Get contact info
$whatsapp_number  = '6281234567890';
$instagram_account = '@grahaclouth';
try {
    $s = $conn->query("SELECT * FROM settings LIMIT 1");
    if ($s) {
        $cfg = $s->fetch();
        if ($cfg) {
            $whatsapp_number   = $cfg['whatsapp_number']   ?? $whatsapp_number;
            $instagram_account = $cfg['instagram_account'] ?? $instagram_account;
        }
    }
} catch(Exception $e) {}
?>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">

            <!-- Brand Column -->
            <div>
                <a href="index.php" class="footer-logo">GRAHA<span>CLOUTH</span></a>
                <p class="footer-desc">Fashion modern berkualitas premium untuk setiap momen. Tampil percaya diri dengan koleksi pilihan terbaik kami.</p>
                <div class="footer-social">
                    <a href="#" class="social-btn" title="Instagram" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsapp_number); ?>" class="social-btn" title="WhatsApp" target="_blank" aria-label="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="#" class="social-btn" title="Facebook" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-btn" title="TikTok" aria-label="TikTok">
                        <i class="fab fa-tiktok"></i>
                    </a>
                </div>
            </div>

            <!-- Menu Column -->
            <div>
                <h4 class="footer-heading">Menu</h4>
                <ul class="footer-links">
                    <li><a href="index.php"><i class="fas fa-chevron-right" style="font-size:0.65rem;"></i> Beranda</a></li>
                    <li><a href="produk.php"><i class="fas fa-chevron-right" style="font-size:0.65rem;"></i> Semua Produk</a></li>
                    <li><a href="kategori.php"><i class="fas fa-chevron-right" style="font-size:0.65rem;"></i> Kategori</a></li>
                    <li><a href="produk.php?promo=1"><i class="fas fa-chevron-right" style="font-size:0.65rem;"></i> Promo</a></li>
                    <li><a href="tentang.php"><i class="fas fa-chevron-right" style="font-size:0.65rem;"></i> Tentang Kami</a></li>
                    <li><a href="kontak.php"><i class="fas fa-chevron-right" style="font-size:0.65rem;"></i> Kontak</a></li>
                </ul>
            </div>

            <!-- Pelanggan Column -->
            <div>
                <h4 class="footer-heading">Akun Saya</h4>
                <ul class="footer-links">
                    <li><a href="login.php"><i class="fas fa-chevron-right" style="font-size:0.65rem;"></i> Login</a></li>
                    <li><a href="register.php"><i class="fas fa-chevron-right" style="font-size:0.65rem;"></i> Daftar</a></li>
                    <li><a href="profil.php"><i class="fas fa-chevron-right" style="font-size:0.65rem;"></i> Profil Saya</a></li>
                    <li><a href="keranjang.php"><i class="fas fa-chevron-right" style="font-size:0.65rem;"></i> Keranjang</a></li>
                    <li><a href="detail_pesanan.php"><i class="fas fa-chevron-right" style="font-size:0.65rem;"></i> Riwayat Pesanan</a></li>
                </ul>
            </div>

            <!-- Kontak Column -->
            <div>
                <h4 class="footer-heading">Kontak</h4>
                <ul class="footer-contact">
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>SMK Karya Nugraha Boyolali, Jawa Tengah</span>
                    </li>
                    <li>
                        <i class="fab fa-whatsapp"></i>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsapp_number); ?>" target="_blank" style="color:var(--muted);transition:var(--ease);">
                            +<?php echo $whatsapp_number; ?>
                        </a>
                    </li>
                    <li>
                        <i class="fab fa-instagram"></i>
                        <span><?php echo htmlspecialchars($instagram_account); ?></span>
                    </li>
                    <li>
                        <i class="fas fa-clock"></i>
                        <span>Senin–Sabtu, 08.00–21.00 WIB</span>
                    </li>
                </ul>
            </div>

        </div><!-- end footer-grid -->

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <p class="footer-copyright">
                &copy; <?php echo date('Y'); ?> <strong>GrahaClouth</strong>. All rights reserved.
            </p>
            <div class="footer-bottom-links">
                <a href="#">Kebijakan Privasi</a>
                <a href="#">Syarat & Ketentuan</a>
            </div>
        </div>
    </div>
</footer>


<?php
// Calculate cart count for mobile bottom navigation if not already defined
if (!isset($cart_count)) {
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
}
$current_page_file = basename($_SERVER['PHP_SELF']);
?>

<!-- Mobile Bottom Floating Navigation Bar (Responsive Mobile Fashion UX) -->
<nav class="mobile-bottom-nav" id="mobileBottomNav" aria-label="Navigasi Seluler">
    <a href="index.php" class="mobile-nav-item <?php echo $current_page_file === 'index.php' ? 'active' : ''; ?>">
        <div class="mobile-nav-icon-wrap">
            <i class="fas fa-home"></i>
        </div>
        <span>Beranda</span>
    </a>
    
    <a href="produk.php" class="mobile-nav-item <?php echo in_array($current_page_file, ['produk.php', 'kategori.php', 'detail_produk.php']) ? 'active' : ''; ?>">
        <div class="mobile-nav-icon-wrap">
            <i class="fas fa-tshirt"></i>
        </div>
        <span>Katalog</span>
    </a>
    
    <a href="keranjang.php" class="mobile-nav-item mobile-nav-cart-btn <?php echo in_array($current_page_file, ['keranjang.php', 'checkout.php']) ? 'active' : ''; ?>" title="Keranjang Belanja">
        <div class="mobile-nav-icon-wrap">
            <i class="fas fa-shopping-bag"></i>
            <span class="mobile-nav-badge" id="mobileCartBadge" style="<?php echo $cart_count > 0 ? '' : 'display:none;'; ?>"><?php echo $cart_count > 9 ? '9+' : $cart_count; ?></span>
        </div>
        <span>Keranjang</span>
    </a>
    
    <button type="button" class="mobile-nav-item" onclick="toggleCustomerChat()" title="Chat Customer Service">
        <div class="mobile-nav-icon-wrap">
            <i class="fas fa-comment-dots"></i>
            <span class="mobile-nav-badge chat-badge-alert" id="mobileChatNavBadge" style="display:none;">!</span>
        </div>
        <span>Chat CS</span>
    </button>
    
    <?php if (is_logged_in()): ?>
        <a href="profil.php" class="mobile-nav-item <?php echo $current_page_file === 'profil.php' ? 'active' : ''; ?>">
            <div class="mobile-nav-icon-wrap">
                <i class="fas fa-user-circle"></i>
            </div>
            <span>Akun</span>
        </a>
    <?php else: ?>
        <a href="login.php" class="mobile-nav-item <?php echo $current_page_file === 'login.php' ? 'active' : ''; ?>">
            <div class="mobile-nav-icon-wrap">
                <i class="fas fa-sign-in-alt"></i>
            </div>
            <span>Masuk</span>
        </a>
    <?php endif; ?>
</nav>

<!-- Customer Support Live Chat Widget -->
<?php require_once __DIR__ . '/chat_widget.php'; ?>

<!-- Scripts -->
<script src="assets/js/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>
