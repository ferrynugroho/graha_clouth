<?php
// includes/chat_widget.php
$is_cust_logged_in = is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'customer';
$current_user_name = $_SESSION['nama'] ?? 'Pelanggan';
$wa_num = preg_replace('/[^0-9]/', '', $whatsapp_number ?? '6281234567890');
?>

<style>
/* Floating Chat Button */
.gc-chat-fab {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: linear-gradient(135deg, var(--accent, #2563EB) 0%, #1D4ED8 100%);
    color: #FFFFFF;
    width: 62px;
    height: 62px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    box-shadow: 0 8px 24px rgba(37, 99, 235, 0.4);
    cursor: pointer;
    z-index: 9990;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    border: 3px solid rgba(255,255,255,0.85);
}
.gc-chat-fab:hover {
    transform: scale(1.1) translateY(-3px);
    box-shadow: 0 12px 28px rgba(37, 99, 235, 0.5);
}
.gc-chat-unread-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background: #EF4444;
    color: #FFFFFF;
    border-radius: 9999px;
    padding: 0.15rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 800;
    border: 2px solid #FFFFFF;
    display: none;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    animation: pulseBadge 1.8s infinite;
}
@keyframes pulseBadge {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.15); }
}

/* Chat Window Container */
.gc-chat-window {
    position: fixed;
    bottom: 96px;
    right: 24px;
    width: 380px;
    max-width: calc(100vw - 32px);
    height: 530px;
    max-height: calc(100vh - 120px);
    background: #FFFFFF;
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(15, 23, 42, 0.2), 0 2px 8px rgba(0,0,0,0.06);
    display: none;
    flex-direction: column;
    z-index: 9991;
    overflow: hidden;
    border: 1px solid rgba(226, 232, 240, 0.8);
    animation: chatSlideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes chatSlideUp {
    from { opacity: 0; transform: translateY(25px) scale(0.95); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

@media (max-width: 768px) {
    .gc-chat-fab {
        bottom: 80px;
        right: 16px;
        width: 52px;
        height: 52px;
        font-size: 1.35rem;
    }
    .gc-chat-window {
        bottom: 74px;
        right: 10px;
        left: 10px;
        width: auto;
        max-width: none;
        height: calc(100vh - 100px);
        max-height: 540px;
        border-radius: 20px;
    }
}

/* Chat Header */
.gc-chat-header {
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
    color: #FFFFFF;
    padding: 1.1rem 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}
.gc-chat-cs-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.gc-chat-cs-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent, #2563EB) 0%, #60A5FA 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #FFFFFF;
    font-size: 1.1rem;
    position: relative;
    border: 2px solid rgba(255,255,255,0.2);
}
.gc-chat-online-dot {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 11px;
    height: 11px;
    border-radius: 50%;
    background: #10B981;
    border: 2px solid #0F172A;
}
.gc-chat-title {
    font-weight: 700;
    font-size: 0.95rem;
    margin: 0;
    color: #FFFFFF;
}
.gc-chat-status {
    font-size: 0.75rem;
    color: #94A3B8;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    margin-top: 0.15rem;
}
.gc-chat-header-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.gc-chat-btn-icon {
    background: rgba(255,255,255,0.12);
    border: none;
    color: #FFFFFF;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
    font-size: 0.9rem;
}
.gc-chat-btn-icon:hover { background: rgba(255,255,255,0.25); }

/* Product Context Card (Inside Chat) */
.gc-chat-product-box {
    background: #F1F5F9;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #E2E8F0;
    display: none;
    align-items: center;
    gap: 0.75rem;
    position: relative;
}
.gc-chat-product-thumb {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    object-fit: cover;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
}
.gc-chat-product-name {
    font-size: 0.85rem;
    font-weight: 700;
    color: #0F172A;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 220px;
    margin: 0 0 0.2rem;
}
.gc-chat-product-price {
    font-size: 0.8rem;
    font-weight: 800;
    color: var(--accent, #2563EB);
}
.gc-chat-product-close {
    position: absolute;
    top: 0.5rem;
    right: 0.75rem;
    background: none;
    border: none;
    color: #94A3B8;
    cursor: pointer;
    font-size: 0.85rem;
}

/* Chat Body Messages */
.gc-chat-body {
    flex: 1;
    padding: 1.25rem;
    overflow-y: auto;
    background: #F8FAFC;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}
.gc-chat-msg {
    max-width: 82%;
    padding: 0.75rem 1rem;
    border-radius: 16px;
    font-size: 0.88rem;
    line-height: 1.45;
    word-break: break-word;
    position: relative;
}
.gc-chat-msg.sent {
    background: linear-gradient(135deg, var(--accent, #2563EB) 0%, #1D4ED8 100%);
    color: #FFFFFF;
    align-self: flex-end;
    border-bottom-right-radius: 4px;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.2);
}
.gc-chat-msg.received {
    background: #FFFFFF;
    color: #1E293B;
    border: 1px solid #E2E8F0;
    align-self: flex-start;
    border-bottom-left-radius: 4px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}
.gc-chat-time {
    font-size: 0.68rem;
    margin-top: 0.35rem;
    opacity: 0.75;
    text-align: right;
}

/* Product Card in Bubble */
.chat-product-preview-card {
    background: #F1F5F9;
    border: 1px solid #CBD5E1;
    border-radius: 12px;
    padding: 0.75rem;
    margin-bottom: 0.6rem;
    color: #0F172A;
}
.gc-chat-msg.sent .chat-product-preview-card {
    background: rgba(255,255,255,0.15);
    border-color: rgba(255,255,255,0.3);
    color: #FFFFFF;
}
.chat-card-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--accent, #2563EB);
    margin-bottom: 0.4rem;
}
.gc-chat-msg.sent .chat-card-tag { color: #FEF08A; }
.chat-card-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.chat-card-thumb {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    object-fit: cover;
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
}
.chat-card-icon-box {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: #E2E8F0;
    color: #64748B;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}
.chat-card-details {
    flex: 1;
    overflow: hidden;
}
.chat-card-name {
    font-weight: 700;
    font-size: 0.85rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
    margin-bottom: 0.15rem;
}
.chat-card-price {
    font-weight: 800;
    font-size: 0.8rem;
    color: #059669;
}
.gc-chat-msg.sent .chat-card-price { color: #86EFAC; }
.chat-card-link {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--accent, #2563EB);
    text-decoration: underline;
    margin-top: 0.2rem;
}
.gc-chat-msg.sent .chat-card-link { color: #BFDBFE; }

.chat-actual-msg {
    font-size: 0.88rem;
    line-height: 1.45;
}
</style>

<!-- Floating Chat Button -->
<div class="gc-chat-fab" id="gcChatFab" onclick="toggleCustomerChat()" title="Chat Customer Service">
    <i class="fas fa-comment-dots" id="gcChatFabIcon"></i>
    <div class="gc-chat-unread-badge" id="gcChatUnreadBadge">0</div>
</div>

<!-- Chat Window -->
<div class="gc-chat-window" id="gcChatWindow">
    
    <!-- Chat Header -->
    <div class="gc-chat-header">
        <div class="gc-chat-cs-info">
            <div class="gc-chat-cs-avatar">
                <i class="fas fa-headset"></i>
                <div class="gc-chat-online-dot"></div>
            </div>
            <div>
                <h4 class="gc-chat-title">Admin Support GrahaClouth</h4>
                <div class="gc-chat-status">
                    <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#10B981;"></span>
                    Online | Siap Membantu
                </div>
            </div>
        </div>
        <div class="gc-chat-header-actions">
            <button class="gc-chat-btn-icon" onclick="toggleCustomerChat()" title="Tutup Chat">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Product Inquiry Context Bar -->
    <div class="gc-chat-product-box" id="gcProductContextBox">
        <img src="" id="gcProductContextImg" alt="Product" class="gc-chat-product-thumb">
        <div style="flex: 1;">
            <h5 class="gc-chat-product-name" id="gcProductContextTitle">Nama Produk</h5>
            <div class="gc-chat-product-price" id="gcProductContextPrice">Rp 0</div>
        </div>
        <button class="gc-chat-product-close" onclick="clearProductContext()" title="Hapus lampiran">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <?php if ($is_cust_logged_in): ?>
        <!-- Messages Body -->
        <div class="gc-chat-body" id="gcChatBody">
            <div style="text-align:center; padding: 1rem 0; color: #94A3B8; font-size: 0.78rem;">
                <i class="fas fa-lock" style="margin-right: 0.25rem;"></i> Percakapan Anda aman & terenkripsi
            </div>
        </div>

        <!-- Quick Question Chips -->
        <div class="gc-chat-chips" id="gcChatChips">
            <button type="button" class="gc-chip-btn" onclick="sendQuickChip('Halo Admin, apakah produk ini masih ready stok?')">
                📦 Ready Stok?
            </button>
            <button type="button" class="gc-chip-btn" onclick="sendQuickChip('Halo Admin, boleh minta panduan ukuran/size chart produk ini?')">
                📏 Panduan Ukuran
            </button>
            <button type="button" class="gc-chip-btn" onclick="sendQuickChip('Bisa dikirim hari ini?')">
                🚚 Kirim Hari Ini?
            </button>
            <button type="button" class="gc-chip-btn" onclick="sendQuickChip('Apakah ada voucher promo diskon yang bisa digunakan?')">
                🎁 Info Promo
            </button>
        </div>

        <!-- Input Footer -->
        <div class="gc-chat-footer">
            <input type="text" id="gcChatInput" class="gc-chat-input" 
                   placeholder="Tulis pesan pertanyaan Anda..." 
                   onkeypress="if(event.key === 'Enter') submitCustomerMessage();">
            <button type="button" class="gc-chat-send-btn" onclick="submitCustomerMessage()" title="Kirim">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>

    <?php else: ?>
        <!-- Guest User View -->
        <div class="gc-chat-guest-box">
            <div class="gc-chat-guest-icon">
                <i class="fas fa-comments"></i>
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin-bottom: 0.5rem;">
                Tanya Produk & Bantuan CS
            </h3>
            <p style="font-size: 0.85rem; color: #64748B; line-height: 1.5; margin-bottom: 1.5rem;">
                Silakan masuk ke akun Anda untuk memulai percakapan langsung dengan customer service toko kami.
            </p>
            <a href="login.php" class="btn btn-primary btn-full" style="border-radius: 12px; font-weight: 700; margin-bottom: 0.75rem; justify-content: center;">
                <i class="fas fa-sign-in-alt"></i> Masuk Sekarang
            </a>
            <div style="font-size: 0.8rem; color: #94A3B8; margin: 0.5rem 0;">atau hubungi langsung via</div>
            <a href="https://wa.me/<?php echo $wa_num; ?>?text=Halo%20Admin%20GrahaClouth,%20saya%20ingin%20bertanya%20tentang%20produk" target="_blank" 
               class="btn btn-outline btn-full" style="border-radius: 12px; font-weight: 700; color: #16A34A; border-color: #BBF7D0; justify-content: center;">
                <i class="fab fa-whatsapp" style="font-size: 1.1rem; margin-right: 0.4rem;"></i> WhatsApp CS
            </a>
        </div>
    <?php endif; ?>

</div>

<script>
let isChatOpen = false;
let chatPollTimer = null;
let unreadPollTimer = null;
let activeProductInquiry = null;

function formatChatMessage(rawMsg, isSent = false) {
    if (!rawMsg) return '';

    // Format 1: [PRODUK_INQUIRY:id=1|nama=...|harga=...|gambar=...]
    const inquiryRegex = /\[PRODUK_INQUIRY:(.*?)\]\s*([\s\S]*)/i;
    const match = rawMsg.match(inquiryRegex);

    if (match) {
        const metaStr = match[1];
        const userText = match[2];
        const params = {};
        metaStr.split('|').forEach(part => {
            const idx = part.indexOf('=');
            if (idx > -1) {
                const k = part.substring(0, idx).trim();
                const v = part.substring(idx + 1).trim();
                params[k] = v;
            }
        });

        const pId = params.id || '';
        const pNama = params.nama || 'Produk';
        const pHarga = params.harga || '';
        const pGambar = params.gambar || '';
        const linkHref = 'detail_produk.php?id=' + pId;

        let imgTag = pGambar 
            ? `<img src="${pGambar}" class="chat-card-thumb" alt="${escapeHtml(pNama)}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=100&h=100&fit=crop';">` 
            : `<div class="chat-card-icon-box"><i class="fas fa-tshirt"></i></div>`;

        return `
            <div class="chat-product-preview-card">
                <div class="chat-card-tag"><i class="fas fa-tag"></i> PRODUK YANG DITANYAKAN</div>
                <div class="chat-card-row">
                    ${imgTag}
                    <div class="chat-card-details">
                        <div class="chat-card-name">${escapeHtml(pNama)}</div>
                        ${pHarga ? `<div class="chat-card-price">${escapeHtml(pHarga)}</div>` : ''}
                        ${pId ? `<a href="${linkHref}" target="_blank" class="chat-card-link"><i class="fas fa-arrow-up-right-from-square"></i> Buka Detail Produk</a>` : ''}
                    </div>
                </div>
            </div>
            ${userText ? `<div class="chat-actual-msg">${escapeHtml(userText)}</div>` : ''}
        `;
    }

    // Format 2: [TANYA PRODUK: {Title} ({Price})] {Question}
    const legacyRegex = /\[TANYA PRODUK:\s*([^\]]+)\]\s*([\s\S]*)/i;
    const legacyMatch = rawMsg.match(legacyRegex);
    if (legacyMatch) {
        const titleAndPrice = legacyMatch[1].trim();
        const userText = legacyMatch[2].trim();

        let title = titleAndPrice;
        let price = '';
        const pMatch = titleAndPrice.match(/^(.*?)\s*\((Rp[\s\S]*?)\)$/i);
        if (pMatch) {
            title = pMatch[1].trim();
            price = pMatch[2].trim();
        }

        return `
            <div class="chat-product-preview-card">
                <div class="chat-card-tag"><i class="fas fa-tag"></i> PRODUK YANG DITANYAKAN</div>
                <div class="chat-card-row">
                    <div class="chat-card-icon-box"><i class="fas fa-tshirt"></i></div>
                    <div class="chat-card-details">
                        <div class="chat-card-name">${escapeHtml(title)}</div>
                        ${price ? `<div class="chat-card-price">${escapeHtml(price)}</div>` : ''}
                    </div>
                </div>
            </div>
            ${userText ? `<div class="chat-actual-msg">${escapeHtml(userText)}</div>` : ''}
        `;
    }

    return `<div class="chat-actual-msg">${escapeHtml(rawMsg)}</div>`;
}

function toggleCustomerChat() {
    isChatOpen = !isChatOpen;
    const windowEl = document.getElementById('gcChatWindow');
    const fabIcon = document.getElementById('gcChatFabIcon');
    
    if (isChatOpen) {
        windowEl.style.display = 'flex';
        fabIcon.className = 'fas fa-chevron-down';
        
        <?php if ($is_cust_logged_in): ?>
            document.getElementById('gcChatUnreadBadge').style.display = 'none';
            fetchMessages();
            clearInterval(chatPollTimer);
            chatPollTimer = setInterval(fetchMessages, 3000);
            setTimeout(() => document.getElementById('gcChatInput').focus(), 300);
        <?php endif; ?>
    } else {
        windowEl.style.display = 'none';
        fabIcon.className = 'fas fa-comment-dots';
        clearInterval(chatPollTimer);
    }
}

<?php if ($is_cust_logged_in): ?>
function fetchMessages() {
    fetch('api_chat.php?action=get_messages')
        .then(res => res.json())
        .then(data => {
            if (!Array.isArray(data)) return;
            const chatBody = document.getElementById('gcChatBody');
            
            let html = `
                <div style="text-align:center; padding: 0.5rem 0; color: #94A3B8; font-size: 0.75rem;">
                    <i class="fas fa-shield-alt"></i> Layanan Bantuan Pelanggan GrahaClouth
                </div>
            `;

            if (data.length === 0) {
                html += `
                    <div style="text-align:center; padding: 2rem 1rem; color: #94A3B8;">
                        <i class="fas fa-comments" style="font-size: 1.8rem; color: var(--accent); margin-bottom: 0.5rem;"></i>
                        <div style="font-weight: 700; color: #1E293B;">Halo <?php echo htmlspecialchars($current_user_name); ?>!</div>
                        <div style="font-size: 0.8rem; margin-top: 0.25rem;">Ada yang bisa kami bantu seputar produk atau pesanan Anda?</div>
                    </div>
                `;
            } else {
                data.forEach(msg => {
                    const isSent = (msg.sender_type === 'customer');
                    const time = msg.time_formatted || (msg.created_at ? msg.created_at.substring(11, 16) : '');
                    
                    html += `
                        <div class="gc-chat-msg ${isSent ? 'sent' : 'received'}">
                            ${formatChatMessage(msg.message, isSent)}
                            <div class="gc-chat-time">${time}</div>
                        </div>
                    `;
                });
            }

            chatBody.innerHTML = html;
            chatBody.scrollTop = chatBody.scrollHeight;
        })
        .catch(err => console.error(err));
}

function submitCustomerMessage() {
    const input = document.getElementById('gcChatInput');
    let messageText = input.value.trim();
    if (!messageText && !activeProductInquiry) return;

    // Prepend product info if active
    if (activeProductInquiry) {
        messageText = `[PRODUK_INQUIRY:id=${activeProductInquiry.id}|nama=${activeProductInquiry.name}|harga=${activeProductInquiry.price}|gambar=${activeProductInquiry.img}] ${messageText}`;
        clearProductContext();
    }

    input.value = '';

    fetch('api_chat.php?action=send_message', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'message=' + encodeURIComponent(messageText)
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            fetchMessages();
        }
    });
}

function sendQuickChip(text) {
    document.getElementById('gcChatInput').value = text;
    submitCustomerMessage();
}

function checkCustomerUnread() {
    fetch('api_chat.php?action=check_unread')
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('gcChatUnreadBadge');
            const mobileBadge = document.getElementById('mobileChatNavBadge');
            if (data.unread > 0) {
                const text = data.unread > 9 ? '9+' : data.unread;
                if (badge) { badge.textContent = text; badge.style.display = 'block'; }
                if (mobileBadge) { mobileBadge.textContent = text; mobileBadge.style.display = 'block'; }
            } else {
                if (badge) badge.style.display = 'none';
                if (mobileBadge) mobileBadge.style.display = 'none';
            }
        });
}

// Start unread polling
unreadPollTimer = setInterval(checkCustomerUnread, 4000);
checkCustomerUnread();
<?php endif; ?>

// Global function to trigger chat with product inquiry attached
window.askProductInChat = function(name, price, img, id) {
    activeProductInquiry = { name, price, img, id };

    document.getElementById('gcProductContextTitle').textContent = name;
    document.getElementById('gcProductContextPrice').textContent = price;
    document.getElementById('gcProductContextImg').src = img;
    document.getElementById('gcProductContextBox').style.display = 'flex';

    if (!isChatOpen) {
        toggleCustomerChat();
    }

    <?php if ($is_cust_logged_in): ?>
        const input = document.getElementById('gcChatInput');
        input.value = 'Halo Admin, saya ingin menanyakan produk ini, apakah stok masih tersedia?';
        input.focus();
    <?php endif; ?>
};

function clearProductContext() {
    activeProductInquiry = null;
    document.getElementById('gcProductContextBox').style.display = 'none';
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}
</script>
