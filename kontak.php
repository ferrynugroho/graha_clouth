<?php
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="container section">
    <h1 class="section-title">Kontak Kami</h1>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; max-width: 1000px; margin: 0 auto;">
        <div>
            <h3 class="mb-3">Informasi Kontak</h3>
            <p class="text-secondary mb-4">Kami selalu siap membantu Anda. Jangan ragu untuk menghubungi kami jika Anda memiliki pertanyaan seputar produk, pesanan, atau kerja sama.</p>
            
            <div class="flex items-center gap-2 mb-3">
                <div style="width: 40px; height: 40px; background: var(--light); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--accent);">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div>
                    <strong style="display: block;">Alamat Toko</strong>
                    <span class="text-secondary">SMK Karya Nugraha Boyolali</span>
                </div>
            </div>
            
            <div class="flex items-center gap-2 mb-3">
                <div style="width: 40px; height: 40px; background: var(--light); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--accent);">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div>
                    <strong style="display: block;">WhatsApp</strong>
                    <span class="text-secondary">+<?php echo $whatsapp_number; ?></span>
                </div>
            </div>
            
            <div class="flex items-center gap-2 mb-3">
                <div style="width: 40px; height: 40px; background: var(--light); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--accent);">
                    <i class="fab fa-instagram"></i>
                </div>
                <div>
                    <strong style="display: block;">Instagram</strong>
                    <span class="text-secondary"><?php echo $instagram_account; ?></span>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <div style="width: 40px; height: 40px; background: var(--light); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--accent);">
                    <i class="fas fa-envelope"></i>
                </div>
                <div>
                    <strong style="display: block;">Email</strong>
                    <span class="text-secondary">support@grahaclouth.com</span>
                </div>
            </div>
        </div>
        
        <div style="background: var(--white); padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <h3 class="mb-3">Kirim Pesan</h3>
            <form action="" method="POST" onsubmit="event.preventDefault(); alert('Terima kasih, pesan Anda telah terkirim!'); this.reset();">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Pesan</label>
                    <textarea class="form-control" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Kirim Pesan</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
