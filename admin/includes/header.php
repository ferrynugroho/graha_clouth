<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

// Only require admin for pages other than login
$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page !== 'login.php') {
    require_admin();
}

// Calculate initial counts for sidebar
$new_orders_count = 0;
$unread_admin = 0;
$active_carts_count = 0;

if (isset($_SESSION['user_id'])) {
    try {
        $stmt_ord = $conn->query("SELECT COUNT(id) FROM orders WHERE status = 'Pesanan Baru'");
        $new_orders_count = (int)$stmt_ord->fetchColumn();

        $stmt_unread = $conn->prepare("SELECT COUNT(id) FROM messages WHERE receiver_id = ? AND is_read = 0");
        $stmt_unread->execute([$_SESSION['user_id']]);
        $unread_admin = (int)$stmt_unread->fetchColumn();

        $stmt_cart = $conn->query("SELECT COUNT(DISTINCT user_id) FROM carts");
        $active_carts_count = (int)$stmt_cart->fetchColumn();
    } catch(Exception $e) {}
}

$total_notifs = $new_orders_count + $unread_admin;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – GrahaClouth</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    /* Admin Notification Center & Real-time Badges */
    .sidebar-menu a {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .sidebar-badge {
        padding: 2px 8px;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: 0.3px;
        transition: all 0.3s ease;
    }
    .badge-order {
        background: #F59E0B;
        color: #FFFFFF;
        box-shadow: 0 2px 6px rgba(245, 158, 11, 0.35);
    }
    .badge-chat {
        background: var(--accent, #2563EB);
        color: #FFFFFF;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.35);
    }
    .badge-cart {
        background: #64748B;
        color: #FFFFFF;
    }

    /* Top Header Action Buttons */
    .admin-header-actions {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }
    .admin-action-btn-wrap {
        position: relative;
    }
    .admin-icon-btn {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        cursor: pointer;
        position: relative;
        transition: all 0.2s ease;
    }
    .admin-icon-btn:hover {
        background: #EFF6FF;
        color: var(--accent, #2563EB);
        border-color: #BFDBFE;
    }
    .admin-bell-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background: #EF4444;
        color: #FFFFFF;
        font-size: 0.65rem;
        font-weight: 800;
        min-width: 18px;
        height: 18px;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        border: 2px solid #FFFFFF;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        animation: pulseBadge 1.8s infinite;
    }

    /* Notification Dropdown Menu */
    .admin-notif-dropdown {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: 360px;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.16);
        z-index: 10000;
        display: none;
        flex-direction: column;
        overflow: hidden;
        animation: dropSlideDown 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes dropSlideDown {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .notif-dropdown-header {
        padding: 1rem 1.25rem;
        background: #F8FAFC;
        border-bottom: 1px solid #E2E8F0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .notif-dropdown-title {
        font-size: 0.92rem;
        font-weight: 800;
        color: #0F172A;
    }
    .notif-mark-read-btn {
        font-size: 0.75rem;
        color: var(--accent, #2563EB);
        background: none;
        border: none;
        font-weight: 600;
        cursor: pointer;
    }
    .notif-mark-read-btn:hover {
        text-decoration: underline;
    }
    .notif-dropdown-body {
        max-height: 340px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }
    .notif-item {
        padding: 0.85rem 1.15rem;
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        text-decoration: none;
        color: #1E293B;
        transition: background 0.15s ease;
    }
    .notif-item:hover {
        background: #F8FAFC;
    }
    .notif-item-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        flex-shrink: 0;
    }
    .notif-item-content {
        flex: 1;
        min-width: 0;
    }
    .notif-item-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: #0F172A;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .notif-item-desc {
        font-size: 0.75rem;
        color: #64748B;
        margin-top: 0.1rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .notif-item-time {
        font-size: 0.7rem;
        color: #94A3B8;
        font-weight: 500;
        margin-left: 0.25rem;
        flex-shrink: 0;
    }
    .notif-dropdown-footer {
        padding: 0.75rem 1rem;
        background: #F8FAFC;
        border-top: 1px solid #E2E8F0;
        text-align: center;
    }
    .notif-dropdown-footer a {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--accent, #2563EB);
    }

    /* Live Toast Pop-up Banner for Admin */
    .admin-live-toast-wrap {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 100000;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        pointer-events: none;
    }
    .admin-live-toast {
        pointer-events: auto;
        background: #FFFFFF;
        border: 1px solid #CBD5E1;
        border-left: 5px solid var(--accent, #2563EB);
        border-radius: 14px;
        padding: 1rem 1.25rem;
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.2);
        display: flex;
        align-items: center;
        gap: 0.85rem;
        width: 350px;
        animation: toastDrop 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        transition: all 0.3s ease;
    }
    .admin-live-toast.toast-order {
        border-left-color: #F59E0B;
    }
    .admin-live-toast.toast-chat {
        border-left-color: #10B981;
    }
    @keyframes toastDrop {
        from { opacity: 0; transform: translateY(-20px) scale(0.95); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    </style>
</head>
<body>
<?php if ($current_page !== 'login.php'): ?>
<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            GRAHA<span class="text-accent">CLOUTH</span>
            <div style="font-size: 0.8rem; color: var(--secondary); font-weight: normal; margin-top: 0.5rem;">Admin Panel</div>
        </div>
        <nav class="sidebar-menu">
            <a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                <span><i class="fas fa-home"></i> Dashboard</span>
            </a>
            <a href="kategori.php" class="<?php echo $current_page == 'kategori.php' ? 'active' : ''; ?>">
                <span><i class="fas fa-tags"></i> Kategori</span>
            </a>
            <a href="produk.php" class="<?php echo in_array($current_page, ['produk.php', 'tambah_produk.php', 'edit_produk.php']) ? 'active' : ''; ?>">
                <span><i class="fas fa-tshirt"></i> Produk</span>
            </a>
            <a href="pesanan.php" class="<?php echo $current_page == 'pesanan.php' ? 'active' : ''; ?>">
                <span><i class="fas fa-shopping-cart"></i> Pesanan</span>
                <span class="sidebar-badge badge-order" id="sidebarOrderBadge" style="<?php echo $new_orders_count > 0 ? '' : 'display:none;'; ?>">
                    <?php echo $new_orders_count > 99 ? '99+' : $new_orders_count; ?>
                </span>
            </a>
            <a href="pelanggan.php" class="<?php echo $current_page == 'pelanggan.php' ? 'active' : ''; ?>">
                <span><i class="fas fa-users"></i> Pelanggan</span>
            </a>
            <a href="keranjang_pelanggan.php" class="<?php echo in_array($current_page, ['keranjang_pelanggan.php', 'detail_keranjang.php']) ? 'active' : ''; ?>">
                <span><i class="fas fa-shopping-basket"></i> Keranjang</span>
                <span class="sidebar-badge badge-cart" id="sidebarCartBadge" style="<?php echo $active_carts_count > 0 ? '' : 'display:none;'; ?>">
                    <?php echo $active_carts_count; ?>
                </span>
            </a>
            <a href="chat.php" class="<?php echo $current_page == 'chat.php' ? 'active' : ''; ?>">
                <span><i class="fas fa-comments"></i> Live Chat</span>
                <span class="sidebar-badge badge-chat" id="sidebarChatBadge" style="<?php echo $unread_admin > 0 ? '' : 'display:none;'; ?>">
                    <?php echo $unread_admin > 99 ? '99+' : $unread_admin; ?>
                </span>
            </a>
            <a href="laporan.php" class="<?php echo $current_page == 'laporan.php' ? 'active' : ''; ?>">
                <span><i class="fas fa-chart-bar"></i> Laporan</span>
            </a>
        </nav>
        <div style="padding: 1.25rem;">
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <a href="../index.php" target="_blank" style="display:block;text-align:center;margin-top:0.75rem;font-size:0.78rem;color:rgba(255,255,255,0.4);transition:var(--ease);" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='rgba(255,255,255,0.4)'"><i class="fas fa-external-link-alt"></i> Lihat Website</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <header class="admin-header">
            <div class="flex items-center gap-2">
                <span style="font-weight: 700; color: #0F172A; font-size: 1.05rem;">
                    <?php 
                    $page_titles = [
                        'index.php' => 'Dashboard Overview',
                        'pesanan.php' => 'Manajemen Pesanan',
                        'produk.php' => 'Katalog Produk',
                        'kategori.php' => 'Kategori Fashion',
                        'pelanggan.php' => 'Daftar Pelanggan',
                        'keranjang_pelanggan.php' => 'Keranjang Belanja Aktif',
                        'chat.php' => 'Live Chat Pelanggan',
                        'laporan.php' => 'Laporan Penjualan'
                    ];
                    echo $page_titles[$current_page] ?? 'Admin Panel';
                    ?>
                </span>
            </div>

            <!-- Top Header Action Center -->
            <div class="admin-header-actions">
                
                <!-- Sound Chime Mute/Unmute Toggle -->
                <button type="button" class="admin-icon-btn" id="adminSoundToggle" title="Suara Notifikasi: Aktif" onclick="toggleAdminSound()">
                    <i class="fas fa-volume-up" id="soundToggleIcon"></i>
                </button>

                <!-- Notification Bell Trigger & Dropdown -->
                <div class="admin-action-btn-wrap">
                    <button type="button" class="admin-icon-btn" id="adminNotifTrigger" title="Notifikasi Baru" onclick="toggleNotifDropdown()">
                        <i class="fas fa-bell"></i>
                        <span class="admin-bell-badge" id="adminNotifBadge" style="<?php echo $total_notifs > 0 ? '' : 'display:none;'; ?>">
                            <?php echo $total_notifs > 9 ? '9+' : $total_notifs; ?>
                        </span>
                    </button>

                    <!-- Dropdown Box -->
                    <div class="admin-notif-dropdown" id="adminNotifDropdown">
                        <div class="notif-dropdown-header">
                            <div class="notif-dropdown-title">Notifikasi Terbaru</div>
                            <button type="button" class="notif-mark-read-btn" onclick="markAllNotificationsRead()">Tandai dibaca</button>
                        </div>
                        <div class="notif-dropdown-body" id="adminNotifList">
                            <div style="text-align: center; padding: 2rem 1rem; color: #94A3B8; font-size: 0.85rem;">
                                <i class="fas fa-spinner fa-spin" style="margin-bottom: 0.5rem; font-size: 1.3rem;"></i>
                                <div>Memuat notifikasi...</div>
                            </div>
                        </div>
                        <div class="notif-dropdown-footer">
                            <a href="pesanan.php">Lihat Seluruh Pesanan Masuk &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Admin User Profile -->
                <div class="flex items-center gap-1" style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 0.35rem 0.85rem 0.35rem 0.45rem; border-radius: 12px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--accent, #2563EB); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 700;">
                        <?php echo strtoupper(substr($_SESSION['nama'] ?? 'A', 0, 1)); ?>
                    </div>
                    <span style="font-weight: 600; font-size: 0.88rem; color: #0F172A;"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></span>
                </div>

            </div>
        </header>

        <!-- Live Realtime Toast Container for Inbound Orders & Chats -->
        <div class="admin-live-toast-wrap" id="adminLiveToastContainer"></div>

        <div class="admin-content">
<?php endif; ?>
