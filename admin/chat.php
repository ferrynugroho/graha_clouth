<?php
require_once 'includes/header.php';

// Get list of users who have sent messages or received messages
$stmt = $conn->query("
    SELECT u.id, u.nama, u.email,
           (SELECT message FROM messages WHERE (sender_id = u.id OR receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_msg,
           (SELECT created_at FROM messages WHERE (sender_id = u.id OR receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_time,
           (SELECT COUNT(id) FROM messages WHERE sender_id = u.id AND is_read = 0) as unread
    FROM users u
    WHERE u.role = 'customer' AND EXISTS (SELECT 1 FROM messages WHERE sender_id = u.id OR receiver_id = u.id)
    ORDER BY (SELECT created_at FROM messages WHERE (sender_id = u.id OR receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) DESC
");
$customers = $stmt->fetchAll();

$active_id = isset($_GET['user']) ? (int)$_GET['user'] : (count($customers) > 0 ? $customers[0]['id'] : 0);
$active_user = null;
foreach ($customers as $c) {
    if ($c['id'] == $active_id) {
        $active_user = $c;
        break;
    }
}
?>

<div class="flex justify-between items-center mb-4">
    <div>
        <h2 style="font-size: 1.5rem; color: var(--dark, #0F172A); font-weight: 800; margin: 0 0 0.25rem;">Live Chat Pelanggan</h2>
        <p style="color: var(--muted, #64748B); font-size: 0.88rem; margin: 0;">Balas pertanyaan produk dan bantu konsultasi belanja pelanggan secara real-time</p>
    </div>
    <div>
        <span style="background: var(--accent-light, #EFF6FF); color: var(--accent, #2563EB); padding: 0.4rem 0.9rem; border-radius: 9999px; font-size: 0.82rem; font-weight: 700; border: 1px solid var(--accent-mid, #BFDBFE);">
            <i class="fas fa-circle" style="color: #10B981; font-size: 0.65rem; margin-right: 0.35rem;"></i> CS Online
        </span>
    </div>
</div>

<style>
.admin-chat-wrap {
    display: grid;
    grid-template-columns: 320px 1fr;
    height: calc(100vh - 200px);
    min-height: 580px;
    background: #FFFFFF;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    border: 1px solid #E2E8F0;
    overflow: hidden;
}

@media (max-width: 900px) {
    .admin-chat-wrap {
        grid-template-columns: 1fr;
        height: auto;
        min-height: 600px;
    }
    .admin-chat-sidebar {
        max-height: 240px;
    }
}

/* Sidebar Conversations */
.admin-chat-sidebar {
    background: #F8FAFC;
    border-right: 1px solid #E2E8F0;
    display: flex;
    flex-direction: column;
}
.sidebar-search-box {
    padding: 1rem;
    border-bottom: 1px solid #E2E8F0;
    background: #FFFFFF;
}
.sidebar-search-input {
    width: 100%;
    padding: 0.65rem 0.85rem 0.65rem 2.2rem;
    border-radius: 10px;
    border: 1.5px solid #E2E8F0;
    font-size: 0.85rem;
    background: #F8FAFC;
    outline: none;
    box-sizing: border-box;
}
.sidebar-search-input:focus {
    background: #FFFFFF;
    border-color: var(--accent, #2563EB);
}
.sidebar-user-list {
    flex: 1;
    overflow-y: auto;
}
.sidebar-user-item {
    padding: 1rem 1.15rem;
    border-bottom: 1px solid #F1F5F9;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    position: relative;
}
.sidebar-user-item:hover {
    background: #FFFFFF;
}
.sidebar-user-item.active {
    background: #EFF6FF;
    border-left: 4px solid var(--accent, #2563EB);
}
.user-avatar-circle {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
    flex-shrink: 0;
}
.user-item-info {
    flex: 1;
    overflow: hidden;
}
.user-item-name {
    font-weight: 700;
    font-size: 0.92rem;
    color: #0F172A;
    margin-bottom: 0.2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.user-item-time {
    font-size: 0.72rem;
    color: #94A3B8;
    font-weight: 500;
}
.user-item-preview {
    font-size: 0.8rem;
    color: #64748B;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.unread-badge-pill {
    background: #EF4444;
    color: #FFFFFF;
    font-size: 0.72rem;
    font-weight: 800;
    padding: 0.15rem 0.5rem;
    border-radius: 9999px;
    margin-left: 0.5rem;
}

/* Chat Main View */
.admin-chat-main {
    display: flex;
    flex-direction: column;
    background: #FFFFFF;
    height: 100%;
    overflow: hidden;
}
.admin-chat-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #E2E8F0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #FFFFFF;
}
.admin-header-user {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}
.admin-header-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0;
}
.admin-header-meta {
    font-size: 0.78rem;
    color: #64748B;
}

/* Chat Message Thread */
.admin-chat-messages {
    flex: 1;
    padding: 1.5rem;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    background: #F8FAFC;
}

.msg-row {
    display: flex;
    flex-direction: column;
    max-width: 75%;
}
.msg-row.sent-by-admin {
    align-self: flex-end;
}
.msg-row.sent-by-customer {
    align-self: flex-start;
}

.msg-bubble-box {
    padding: 0.9rem 1.2rem;
    border-radius: 18px;
    font-size: 0.92rem;
    line-height: 1.5;
    word-break: break-word;
    position: relative;
}
.sent-by-admin .msg-bubble-box {
    background: linear-gradient(135deg, var(--accent, #2563EB) 0%, #1D4ED8 100%);
    color: #FFFFFF;
    border-bottom-right-radius: 4px;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
}
.sent-by-customer .msg-bubble-box {
    background: #FFFFFF;
    color: #1E293B;
    border: 1px solid #E2E8F0;
    border-bottom-left-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.msg-timestamp {
    font-size: 0.7rem;
    margin-top: 0.35rem;
    opacity: 0.75;
    text-align: right;
}

/* Product Inquiry Card */
.chat-product-preview-card {
    background: #F1F5F9;
    border: 1px solid #CBD5E1;
    border-radius: 12px;
    padding: 0.75rem;
    margin-bottom: 0.6rem;
    color: #0F172A;
}
.sent-by-admin .chat-product-preview-card {
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
.sent-by-admin .chat-card-tag { color: #FEF08A; }
.chat-card-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.chat-card-thumb {
    width: 52px;
    height: 52px;
    border-radius: 8px;
    object-fit: cover;
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
}
.chat-card-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    background: #E2E8F0;
    color: #64748B;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}
.chat-card-details {
    flex: 1;
    overflow: hidden;
}
.chat-card-name {
    font-weight: 700;
    font-size: 0.88rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
    margin-bottom: 0.2rem;
}
.chat-card-price {
    font-weight: 800;
    font-size: 0.82rem;
    color: #059669;
}
.sent-by-admin .chat-card-price { color: #86EFAC; }
.chat-card-link {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--accent, #2563EB);
    text-decoration: underline;
    margin-top: 0.25rem;
}
.sent-by-admin .chat-card-link { color: #BFDBFE; }

.chat-actual-msg {
    font-size: 0.92rem;
    line-height: 1.5;
}

/* Quick Response Templates */
.admin-quick-templates {
    display: flex;
    gap: 0.4rem;
    overflow-x: auto;
    padding: 0.5rem 1.25rem;
    background: #FFFFFF;
    border-top: 1px solid #F1F5F9;
    scrollbar-width: none;
}
.admin-quick-templates::-webkit-scrollbar { display: none; }
.template-pill-btn {
    white-space: nowrap;
    padding: 0.35rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.78rem;
    font-weight: 600;
    background: #F8FAFC;
    color: #475569;
    border: 1px solid #E2E8F0;
    cursor: pointer;
    transition: all 0.2s ease;
}
.template-pill-btn:hover {
    background: #EFF6FF;
    color: var(--accent, #2563EB);
    border-color: #BFDBFE;
}

/* Bottom Input Area */
.admin-chat-input-area {
    padding: 1rem 1.25rem;
    border-top: 1px solid #E2E8F0;
    background: #FFFFFF;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.admin-chat-input {
    flex: 1;
    border: 1.5px solid #E2E8F0;
    border-radius: 12px;
    padding: 0.8rem 1.15rem;
    font-size: 0.92rem;
    color: #0F172A;
    background: #F8FAFC;
    outline: none;
    font-family: inherit;
    transition: all 0.2s ease;
}
.admin-chat-input:focus {
    background: #FFFFFF;
    border-color: var(--accent, #2563EB);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}
.admin-send-btn {
    background: var(--accent, #2563EB);
    color: #FFFFFF;
    border: none;
    border-radius: 12px;
    padding: 0.8rem 1.4rem;
    font-weight: 700;
    font-size: 0.92rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}
.admin-send-btn:hover {
    background: #1D4ED8;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
}
.admin-send-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}
</style>

<div class="admin-chat-wrap">
    
    <!-- Sidebar: Customer List -->
    <div class="admin-chat-sidebar">
        <div class="sidebar-search-box">
            <div style="position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 0.85rem;"></i>
                <input type="text" id="searchUserChat" class="sidebar-search-input" placeholder="Cari percakapan pelanggan..." onkeyup="filterUserList()">
            </div>
        </div>

        <div class="sidebar-user-list" id="sidebarUserList">
            <?php foreach($customers as $c): 
                $initial = strtoupper(mb_substr($c['nama'], 0, 1, 'UTF-8'));
                $time_str = $c['last_time'] ? date('H:i', strtotime($c['last_time'])) : '';
                $is_active = ($c['id'] == $active_id);
            ?>
                <div class="sidebar-user-item <?php echo $is_active ? 'active' : ''; ?>" 
                     onclick="window.location.href='chat.php?user=<?php echo $c['id']; ?>'"
                     data-name="<?php echo htmlspecialchars(strtolower($c['nama'])); ?>">
                    
                    <div class="user-avatar-circle">
                        <?php echo $initial; ?>
                    </div>
                    
                    <div class="user-item-info">
                        <div class="user-item-name">
                            <span><?php echo htmlspecialchars($c['nama']); ?></span>
                            <span class="user-item-time"><?php echo $time_str; ?></span>
                        </div>
                        <div class="user-item-preview">
                            <?php 
                            $clean_preview = preg_replace('/\[PRODUK_INQUIRY:.*?\]|\[TANYA PRODUK:.*?\]/i', '🏷️ [Tanya Produk] ', $c['last_msg'] ?: 'Belum ada pesan');
                            echo htmlspecialchars($clean_preview); 
                            ?>
                        </div>
                    </div>

                    <?php if($c['unread'] > 0): ?>
                        <span class="unread-badge-pill"><?php echo $c['unread']; ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if(count($customers) == 0): ?>
                <div style="padding: 3rem 1.5rem; text-align: center; color: #94A3B8;">
                    <i class="fas fa-comments" style="font-size: 2.5rem; margin-bottom: 0.75rem; color: #CBD5E1;"></i>
                    <div style="font-weight: 600; font-size: 0.95rem; color: #64748B;">Belum Ada Percakapan</div>
                    <div style="font-size: 0.8rem; margin-top: 0.25rem;">Pesan dari pelanggan akan muncul di sini secara otomatis.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="admin-chat-main">
        <?php if($active_id > 0 && $active_user): ?>
            <!-- Header -->
            <div class="admin-chat-header">
                <div class="admin-header-user">
                    <div class="user-avatar-circle" style="width: 42px; height: 42px;">
                        <?php echo strtoupper(mb_substr($active_user['nama'], 0, 1, 'UTF-8')); ?>
                    </div>
                    <div>
                        <h3 class="admin-header-title"><?php echo htmlspecialchars($active_user['nama']); ?></h3>
                        <div class="admin-header-meta">
                            <i class="fas fa-envelope" style="margin-right: 0.25rem;"></i> <?php echo htmlspecialchars($active_user['email']); ?>
                        </div>
                    </div>
                </div>
                <div>
                    <button type="button" onclick="loadMessages()" class="btn btn-sm btn-outline" style="border-radius: 8px;" title="Refresh pesan">
                        <i class="fas fa-rotate"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- Messages Container -->
            <div class="admin-chat-messages" id="chatMessages">
                <div style="text-align: center; padding: 2rem; color: #94A3B8;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 1.5rem;"></i>
                    <div style="font-size: 0.85rem; margin-top: 0.5rem;">Memuat pesan...</div>
                </div>
            </div>

            <!-- Quick Template Responses -->
            <div class="admin-quick-templates">
                <button type="button" class="template-pill-btn" onclick="insertTemplate('Halo kak! Produk ini ready stok dan siap dikirim hari ini ya 😊')">
                    ✨ Ready Stok
                </button>
                <button type="button" class="template-pill-btn" onclick="insertTemplate('Untuk panduan ukuran / size chart bisa dicek pada rincian produk ya kak 👍')">
                    📏 Panduan Ukuran
                </button>
                <button type="button" class="template-pill-btn" onclick="insertTemplate('Barang 100% original dengan bahan Combed 30s premium yang adem dan nyaman dipakai ✨')">
                    👕 Kualitas Bahan
                </button>
                <button type="button" class="template-pill-btn" onclick="insertTemplate('Pesanan sedang kami proses dan segera kami kirimkan ya kak. Terima kasih! 🙏')">
                    📦 Proses Pengiriman
                </button>
            </div>

            <!-- Input Footer -->
            <form id="adminChatForm" onsubmit="event.preventDefault(); sendAdminMessage();" class="admin-chat-input-area">
                <input type="text" 
                       id="adminChatInput" 
                       class="admin-chat-input" 
                       placeholder="Ketik balasan untuk <?php echo htmlspecialchars($active_user['nama']); ?>..." 
                       autocomplete="off"
                       autofocus>
                <button type="submit" id="adminSendBtn" class="admin-send-btn">
                    <i class="fas fa-paper-plane"></i> Kirim
                </button>
            </form>

        <?php else: ?>
            <div style="display:flex; flex-direction:column; height:100%; align-items:center; justify-content:center; color:#94A3B8; padding: 2rem; text-align: center;">
                <div style="width: 72px; height: 72px; border-radius: 50%; background: #F1F5F9; color: #94A3B8; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 1rem;">
                    <i class="fas fa-comments"></i>
                </div>
                <h3 style="color: #1E293B; font-weight: 700; margin-bottom: 0.35rem;">Pilih Percakapan Pelanggan</h3>
                <p style="font-size: 0.88rem; max-width: 340px; margin: 0;">Pilih salah satu nama pelanggan di sebelah kiri untuk melihat pesan dan mengirim balasan.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php if($active_id > 0): ?>
<script>
const activeUser = <?php echo (int)$active_id; ?>;
let chatInterval;
let isSubmitting = false;

function formatChatMessage(rawMsg, isAdmin = false) {
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
        let pGambar = params.gambar || '';
        if (pGambar) {
            if (!pGambar.startsWith('http') && !pGambar.startsWith('../')) {
                pGambar = '../' + pGambar.replace(/^\//, '');
            }
        }

        const linkHref = '../detail_produk.php?id=' + pId;

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

function loadMessages() {
    fetch('api_chat.php?action=get_messages&user=' + activeUser)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('chatMessages');
            if (!Array.isArray(data)) return;

            if (data.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 3rem 1rem; color: #94A3B8;">
                        <i class="fas fa-comment-dots" style="font-size: 2rem; color: #CBD5E1; margin-bottom: 0.5rem;"></i>
                        <div>Belum ada pesan dalam percakapan ini.</div>
                    </div>
                `;
                return;
            }

            let html = '';
            data.forEach(msg => {
                const isAdmin = (msg.sender_type === 'admin');
                const timeStr = msg.time_formatted || (msg.created_at ? msg.created_at.substring(11, 16) : '');
                
                html += `
                    <div class="msg-row ${isAdmin ? 'sent-by-admin' : 'sent-by-customer'}">
                        <div class="msg-bubble-box">
                            ${formatChatMessage(msg.message, isAdmin)}
                            <div class="msg-timestamp">${timeStr}</div>
                        </div>
                    </div>
                `;
            });

            const shouldScroll = (container.scrollTop + container.clientHeight >= container.scrollHeight - 100) || (container.innerHTML.includes('fa-spinner'));
            container.innerHTML = html;
            if (shouldScroll) {
                container.scrollTop = container.scrollHeight;
            }
        })
        .catch(err => console.error(err));
}

function sendAdminMessage() {
    if (isSubmitting) return;

    const input = document.getElementById('adminChatInput');
    const sendBtn = document.getElementById('adminSendBtn');
    const msg = input.value.trim();
    if (!msg) return;

    isSubmitting = true;
    sendBtn.disabled = true;

    input.value = '';

    fetch('api_chat.php?action=send_message', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'user=' + activeUser + '&message=' + encodeURIComponent(msg)
    })
    .then(res => res.json())
    .then(data => {
        isSubmitting = false;
        sendBtn.disabled = false;
        if (data.status === 'success') {
            loadMessages();
            setTimeout(() => {
                const container = document.getElementById('chatMessages');
                container.scrollTop = container.scrollHeight;
            }, 100);
            input.focus();
        } else {
            alert('Gagal mengirim pesan: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(err => {
        isSubmitting = false;
        sendBtn.disabled = false;
        console.error(err);
    });
}

function insertTemplate(text) {
    const input = document.getElementById('adminChatInput');
    input.value = text;
    input.focus();
}

function filterUserList() {
    const q = document.getElementById('searchUserChat').value.toLowerCase();
    const items = document.querySelectorAll('#sidebarUserList .sidebar-user-item');
    items.forEach(el => {
        const name = el.getAttribute('data-name') || '';
        el.style.display = name.includes(q) ? 'flex' : 'none';
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

// Initial load
loadMessages();
// Auto polling
chatInterval = setInterval(loadMessages, 3000);
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
