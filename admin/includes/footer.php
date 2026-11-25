<?php if (basename($_SERVER['PHP_SELF']) !== 'login.php'): ?>
        </div> <!-- End admin-content -->
    </main>
</div> <!-- End admin-layout -->

<script>
// ==========================================================================
// ADMIN REALTIME NOTIFICATION & SOUND ENGINE
// ==========================================================================
let isNotifDropdownOpen = false;
let isSoundEnabled = localStorage.getItem('admin_notif_sound') !== 'disabled';
let lastKnownOrderId = null;
let lastKnownChatId = null;
let notifPollInterval = null;

// Initialize Sound Toggle Icon
function updateSoundIcon() {
    const icon = document.getElementById('soundToggleIcon');
    const btn = document.getElementById('adminSoundToggle');
    if (!icon || !btn) return;
    if (isSoundEnabled) {
        icon.className = 'fas fa-volume-up';
        icon.style.color = 'var(--accent, #2563EB)';
        btn.title = 'Suara Notifikasi: Aktif (Klik untuk Membisukan)';
    } else {
        icon.className = 'fas fa-volume-mute';
        icon.style.color = '#94A3B8';
        btn.title = 'Suara Notifikasi: Mati (Klik untuk Mengaktifkan)';
    }
}
updateSoundIcon();

function toggleAdminSound() {
    isSoundEnabled = !isSoundEnabled;
    localStorage.setItem('admin_notif_sound', isSoundEnabled ? 'enabled' : 'disabled');
    updateSoundIcon();
    if (isSoundEnabled) {
        playNotificationChime('chime');
    }
}

// Web Audio API Pleasant Notification Chime
function playNotificationChime(type = 'chime') {
    if (!isSoundEnabled) return;
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        
        if (type === 'order') {
            // Happy two-tone fanfare for new orders (E5 -> G#5 -> B5)
            const notes = [659.25, 830.61, 987.77];
            notes.forEach((freq, idx) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(freq, ctx.currentTime + (idx * 0.1));
                gain.gain.setValueAtTime(0.18, ctx.currentTime + (idx * 0.1));
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + (idx * 0.1) + 0.35);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(ctx.currentTime + (idx * 0.1));
                osc.stop(ctx.currentTime + (idx * 0.1) + 0.35);
            });
        } else {
            // Gentle double bell for new chat messages (G5 -> C6)
            const notes = [783.99, 1046.50];
            notes.forEach((freq, idx) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, ctx.currentTime + (idx * 0.12));
                gain.gain.setValueAtTime(0.15, ctx.currentTime + (idx * 0.12));
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + (idx * 0.12) + 0.4);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(ctx.currentTime + (idx * 0.12));
                osc.stop(ctx.currentTime + (idx * 0.12) + 0.4);
            });
        }
    } catch(e) {
        console.warn('Audio not allowed or supported:', e);
    }
}

// Show Inbound Interactive Toast Pop-up
function showAdminInboundToast(title, subtitle, link, type = 'order') {
    const container = document.getElementById('adminLiveToastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `admin-live-toast toast-${type}`;
    
    const iconClass = (type === 'order') ? 'fas fa-shopping-cart' : 'fas fa-comments';
    const iconBg = (type === 'order') ? '#FEF3C7' : '#DCFCE7';
    const iconColor = (type === 'order') ? '#D97706' : '#16A34A';
    const btnText = (type === 'order') ? 'Lihat Pesanan' : 'Buka Chat';

    toast.innerHTML = `
        <div style="width: 40px; height: 40px; border-radius: 10px; background: ${iconBg}; color: ${iconColor}; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">
            <i class="${iconClass}"></i>
        </div>
        <div style="flex: 1; min-width: 0;">
            <div style="font-size: 0.88rem; font-weight: 800; color: #0F172A; line-height: 1.25;">${title}</div>
            <div style="font-size: 0.78rem; color: #64748B; margin-top: 0.2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${subtitle}</div>
            <a href="${link}" style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.76rem; font-weight: 700; color: var(--accent, #2563EB); margin-top: 0.4rem; text-decoration: underline;">
                ${btnText} &rarr;
            </a>
        </div>
        <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:#94A3B8; cursor:pointer; font-size:0.9rem; padding: 0.2rem;">
            <i class="fas fa-times"></i>
        </button>
    `;

    container.appendChild(toast);

    // Auto-remove after 8 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-15px)';
        setTimeout(() => toast.remove(), 350);
    }, 8000);
}

// Toggle Dropdown
function toggleNotifDropdown() {
    isNotifDropdownOpen = !isNotifDropdownOpen;
    const drop = document.getElementById('adminNotifDropdown');
    if (!drop) return;
    drop.style.display = isNotifDropdownOpen ? 'flex' : 'none';
}

// Close Dropdown on outside click
document.addEventListener('click', function(e) {
    const trigger = document.getElementById('adminNotifTrigger');
    const drop = document.getElementById('adminNotifDropdown');
    if (drop && isNotifDropdownOpen && !drop.contains(e.target) && !trigger.contains(e.target)) {
        drop.style.display = 'none';
        isNotifDropdownOpen = false;
    }
});

// Mark All Read
function markAllNotificationsRead() {
    fetch('api_notifications.php?action=mark_all_read')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                pollAdminNotifications();
            }
        });
}

// Core Polling Function
function pollAdminNotifications() {
    fetch('api_notifications.php?action=get_summary')
        .then(res => res.json())
        .then(data => {
            if (data.status !== 'success') return;

            // 1. Update Badges
            const orderBadge = document.getElementById('sidebarOrderBadge');
            if (orderBadge) {
                if (data.new_orders_count > 0) {
                    orderBadge.textContent = data.new_orders_count > 99 ? '99+' : data.new_orders_count;
                    orderBadge.style.display = 'inline-block';
                } else {
                    orderBadge.style.display = 'none';
                }
            }

            const chatBadge = document.getElementById('sidebarChatBadge');
            if (chatBadge) {
                if (data.unread_chats_count > 0) {
                    chatBadge.textContent = data.unread_chats_count > 99 ? '99+' : data.unread_chats_count;
                    chatBadge.style.display = 'inline-block';
                } else {
                    chatBadge.style.display = 'none';
                }
            }

            const cartBadge = document.getElementById('sidebarCartBadge');
            if (cartBadge) {
                if (data.active_carts_count > 0) {
                    cartBadge.textContent = data.active_carts_count;
                    cartBadge.style.display = 'inline-block';
                } else {
                    cartBadge.style.display = 'none';
                }
            }

            const notifBadge = document.getElementById('adminNotifBadge');
            if (notifBadge) {
                if (data.total_notifs > 0) {
                    notifBadge.textContent = data.total_notifs > 9 ? '9+' : data.total_notifs;
                    notifBadge.style.display = 'flex';
                } else {
                    notifBadge.style.display = 'none';
                }
            }

            // 2. Render Dropdown Events
            const listEl = document.getElementById('adminNotifList');
            if (listEl && Array.isArray(data.recent_events)) {
                if (data.recent_events.length === 0) {
                    listEl.innerHTML = `
                        <div style="text-align: center; padding: 2.5rem 1rem; color: #94A3B8; font-size: 0.85rem;">
                            <i class="fas fa-check-circle" style="font-size: 2rem; color: #10B981; margin-bottom: 0.5rem; display: block;"></i>
                            <div>Semua notifikasi telah ditinjau!</div>
                        </div>
                    `;
                } else {
                    let html = '';
                    data.recent_events.forEach(evt => {
                        html += `
                            <a href="${evt.link}" class="notif-item">
                                <div class="notif-item-icon" style="background: ${evt.bg}; color: ${evt.color};">
                                    <i class="${evt.icon}"></i>
                                </div>
                                <div class="notif-item-content">
                                    <div class="notif-item-title">${escapeAdminHtml(evt.title)}</div>
                                    <div class="notif-item-desc">${escapeAdminHtml(evt.desc)}</div>
                                </div>
                                <div class="notif-item-time">${evt.time}</div>
                            </a>
                        `;
                    });
                    listEl.innerHTML = html;
                }
            }

            // 3. Detect Inbound New Order
            if (data.latest_order && data.latest_order.id) {
                const curOrderId = parseInt(data.latest_order.id);
                if (lastKnownOrderId !== null && curOrderId > lastKnownOrderId) {
                    playNotificationChime('order');
                    showAdminInboundToast(
                        '🎉 Pesanan Baru Masuk!',
                        `${data.latest_order.nomor_order} • ${data.latest_order.nama_pelanggan} (Rp ${parseInt(data.latest_order.total).toLocaleString('id-ID')})`,
                        `pesanan.php?id=${data.latest_order.id}`,
                        'order'
                    );
                }
                lastKnownOrderId = curOrderId;
            }

            // 4. Detect Inbound New Chat Message
            if (data.latest_chat && data.latest_chat.id) {
                const curChatId = parseInt(data.latest_chat.id);
                const isCurrentlyOnChatPage = window.location.pathname.endsWith('chat.php');
                if (lastKnownChatId !== null && curChatId > lastKnownChatId && !isCurrentlyOnChatPage) {
                    playNotificationChime('chat');
                    showAdminInboundToast(
                        `💬 Pesan Baru dari ${data.latest_chat.sender_name}`,
                        data.latest_chat.message_preview || 'Mengirim pesan baru...',
                        `chat.php?user=${data.latest_chat.sender_id}`,
                        'chat'
                    );
                }
                lastKnownChatId = curChatId;
            }

        })
        .catch(err => console.error('Admin notification poll error:', err));
}

function escapeAdminHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

// Start Realtime Poller
notifPollInterval = setInterval(pollAdminNotifications, 4000);
pollAdminNotifications();
</script>
<?php endif; ?>

<script src="../assets/js/script.js"></script>
</body>
</html>
