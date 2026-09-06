<?php
/**
 * Admin Panel Layout Wrapper — Somali Cardiac Society
 */
require_once __DIR__ . '/../config/auth.php';

// Force admin login
requireLogin();

$currentAdminPage = basename($_SERVER['PHP_SELF']);
$adminFullName    = $_SESSION['admin_name'] ?? 'Administrator';
$adminRole        = $_SESSION['admin_role'] ?? 'admin';
$adminUsername    = $_SESSION['admin_user'] ?? 'admin';

/**
 * Render the full admin shell header + sidebar
 */
function startAdminLayout(string $title) {
    global $currentAdminPage, $adminFullName, $adminRole, $adminUsername;
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?> — SCS Admin Panel</title>
        <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/images/logo-2.png">
        <script src="https://unpkg.com/@phosphor-icons/web"></script>
        <!-- SweetAlert2 -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
        <!-- Quill Rich Text Editor -->
        <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
        <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
        <!-- Cropper.js Image Cropper -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css?v=1000">
    </head>
    <body>
    <div class="admin-wrapper">

        <!-- ── Sidebar ── -->
        <aside class="admin-sidebar" id="adminSidebar">

            <!-- Brand -->
            <div class="sidebar-brand">
                <img src="<?php echo SITE_URL; ?>/images/logo.png" alt="SCS Logo">
                <span>SCS Portal</span>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">

                <div class="sidebar-label">Main Menu</div>

                <a href="<?php echo SITE_URL; ?>/admin/dashboard"
                   class="<?php echo $currentAdminPage === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="ph ph-squares-four"></i>
                    Dashboard
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/members"
                   class="<?php echo $currentAdminPage === 'members.php' ? 'active' : ''; ?>">
                    <i class="ph ph-users"></i>
                    Manage Members
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/content"
                   class="<?php echo $currentAdminPage === 'content.php' ? 'active' : ''; ?>">
                    <i class="ph ph-file-text"></i>
                    Manage Content
                </a>

                <?php if (isSuperAdmin()): ?>
                <div class="sidebar-label">Admin</div>
                <a href="<?php echo SITE_URL; ?>/admin/admins"
                   class="<?php echo $currentAdminPage === 'admins.php' ? 'active' : ''; ?>">
                    <i class="ph ph-shield-check"></i>
                    Admin Users
                </a>
                <?php endif; ?>

                <div class="sidebar-label">Account</div>

                <a href="<?php echo SITE_URL; ?>/admin/profile"
                   class="<?php echo $currentAdminPage === 'profile.php' ? 'active' : ''; ?>">
                    <i class="ph ph-gear"></i>
                    Settings
                </a>

                <a href="<?php echo SITE_URL; ?>/" target="_blank">
                    <i class="ph ph-arrow-square-out"></i>
                    View Website
                </a>

            </nav>

            <!-- Footer: user info + logout -->
            <div class="sidebar-footer">
                <div class="sf-avatar">
                    <?php echo strtoupper(substr($adminFullName, 0, 1)); ?>
                </div>
                <div class="sf-info">
                    <div class="sf-name"><?php echo htmlspecialchars($adminFullName); ?></div>
                    <div class="sf-role"><?php echo $adminRole === 'super_admin' ? 'Super Admin' : 'Admin'; ?></div>
                </div>
                <a href="<?php echo SITE_URL; ?>/admin/logout" class="sf-logout" title="Logout">
                    <i class="ph ph-sign-out" style="font-size:1.2rem;"></i>
                </a>
            </div>
        </aside>

        <!-- ── Main Content ── -->
        <main class="admin-main">

            <!-- Top Header -->
            <header class="admin-header">
                <button class="header-toggle" id="headerToggle" aria-label="Toggle Sidebar">
                    <i class="ph ph-list" style="font-size:1.5rem;"></i>
                </button>

                <div></div><!-- Spacer -->

                <div class="header-user">
                    <div>
                        <div class="user-name"><?php echo htmlspecialchars($adminFullName); ?></div>
                        <span class="user-role role-<?php echo $adminRole === 'super_admin' ? 'super' : 'admin'; ?>">
                            <?php echo $adminRole === 'super_admin' ? 'Super Admin' : 'Admin'; ?>
                        </span>
                    </div>
                    <div class="user-avatar-small">
                        <?php echo strtoupper(substr($adminFullName, 0, 1)); ?>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="admin-content">

                <!-- Flash messages -->
                <?php
                $successFlash = getFlash('success');
                $errorFlash   = getFlash('error');
                if ($successFlash): ?>
                    <div class="alert alert-success" style="margin-bottom:24px;"><?php echo $successFlash; ?></div>
                <?php endif;
                if ($errorFlash): ?>
                    <div class="alert alert-error" style="margin-bottom:24px;"><?php echo $errorFlash; ?></div>
                <?php endif; ?>
    <?php
}

/**
 * Close the admin layout shell
 */
function endAdminLayout() {
    ?>
            </div><!-- /admin-content -->
        </main><!-- /admin-main -->
    </div><!-- /admin-wrapper -->

    <!-- ── Image Crop Modal ── -->
    <div id="cropModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.75);align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;padding:24px;max-width:560px;width:calc(100% - 32px);box-shadow:0 24px 60px rgba(0,0,0,0.4);">
            <h3 style="margin-bottom:16px;font-size:1rem;color:#0D1B2A;">Crop Image</h3>
            <div style="max-height:380px;overflow:hidden;border-radius:8px;background:#f0f4f8;">
                <img id="cropImage" src="" alt="Crop" style="max-width:100%;display:block;">
            </div>
            <div style="display:flex;gap:12px;margin-top:20px;justify-content:flex-end;">
                <button id="cropCancel" type="button" style="padding:10px 20px;border-radius:8px;border:1.5px solid #E2E8F0;background:#F0F4F8;color:#4A5568;font-weight:600;cursor:pointer;">Cancel</button>
                <button id="cropConfirm" type="button" style="padding:10px 22px;border-radius:8px;border:none;background:linear-gradient(135deg,#27AAE1,#1a8bbf);color:#fff;font-weight:600;cursor:pointer;">Apply Crop</button>
            </div>
        </div>
    </div>

    <script src="<?php echo SITE_URL; ?>/assets/js/admin.js"></script>

    <!-- Confirm-delete via SweetAlert -->
    <script>
    document.querySelectorAll('.confirm-delete').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.href;
            const item = this.dataset.item || 'item';
            Swal.fire({
                title: 'Delete ' + item + '?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ED1C24',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                borderRadius: '16px'
            }).then(function(result) {
                if (result.isConfirmed) window.location.href = href;
            });
        });
    });

    // Table search
    const searchInput = document.getElementById('tableSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#dataTable tbody tr').forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    // ── Cropper.js integration ──────────────────────────────────────────
    (function () {
        let cropper = null;
        let activeTrigger = null;
        let activeRealInput = null;
        const cropModal  = document.getElementById('cropModal');
        const cropImage  = document.getElementById('cropImage');
        const cropConfirm = document.getElementById('cropConfirm');
        const cropCancel  = document.getElementById('cropCancel');

        function openCropModal(file, triggerInput) {
            activeTrigger = triggerInput;
            activeRealInput = triggerInput._realInput;
            const reader = new FileReader();
            reader.onload = function(e) {
                cropImage.src = e.target.result;
                cropModal.style.display = 'flex';
                if (cropper) { cropper.destroy(); cropper = null; }
                const ratio = parseFloat(triggerInput.dataset.cropRatio) || NaN;
                cropper = new Cropper(cropImage, {
                    aspectRatio: isNaN(ratio) ? NaN : ratio,
                    viewMode: 1,
                    autoCropArea: 0.9,
                    responsive: true,
                    background: false
                });
            };
            reader.readAsDataURL(file);
        }

        function closeCropModal() {
            cropModal.style.display = 'none';
            if (cropper) { cropper.destroy(); cropper = null; }
            // Reset the file input so it can be re-triggered
            if (activeTrigger) activeTrigger.value = '';
            activeTrigger = null;
            activeRealInput = null;
        }

        cropCancel.addEventListener('click', closeCropModal);

        cropConfirm.addEventListener('click', function () {
            if (!cropper || !activeRealInput) return;
            const canvas = cropper.getCroppedCanvas({ maxWidth: 1600, maxHeight: 1600, imageSmoothingQuality: 'high' });
            canvas.toBlob(function(blob) {
                // Inject cropped blob into the real hidden file input
                const dt = new DataTransfer();
                dt.items.add(new File([blob], 'cropped_image.jpg', { type: 'image/jpeg' }));
                activeRealInput.files = dt.files;

                // Update preview
                const previewId = activeRealInput.dataset.preview;
                if (previewId) {
                    const preview = document.getElementById(previewId);
                    if (preview) {
                        preview.src = canvas.toDataURL('image/jpeg');
                        preview.style.display = 'block';
                    }
                }
                closeCropModal();
            }, 'image/jpeg', 0.92);
        });

        // Wire all image inputs with crop functionality
        document.querySelectorAll('.image-upload-input').forEach(function(realInput) {
            // Create a visible trigger input that opens file picker
            const triggerInput = document.createElement('input');
            triggerInput.type = 'file';
            triggerInput.accept = 'image/*';
            triggerInput.style.cssText = realInput.style.cssText;
            triggerInput.className = realInput.className + ' crop-trigger';
            triggerInput.dataset.cropRatio = realInput.dataset.cropRatio || '';
            triggerInput._realInput = realInput;

            // Copy any label/hint text from parent
            realInput.parentNode.insertBefore(triggerInput, realInput);

            // Hide the actual input that goes into the form
            realInput.style.display = 'none';
            realInput.removeAttribute('class'); // prevent double-init

            triggerInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    openCropModal(this.files[0], this);
                }
            });
        });
    })();

    // ── Quill Rich Text Editors ─────────────────────────────────────────
    (function () {
        // Full toolbar for body-length content
        const toolbarFull = [
            [{ 'header': [2, 3, false] }],
            ['bold', 'italic', 'underline'],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            ['blockquote', 'link'],
            ['clean']
        ];
        // Minimal toolbar for short fields (bio, summary)
        const toolbarMini = [
            ['bold', 'italic', 'underline'],
            [{ 'list': 'bullet' }],
            ['link', 'clean']
        ];

        function initQuill(editorId, hiddenId, toolbar) {
            const editorEl = document.getElementById(editorId);
            const hiddenEl = document.getElementById(hiddenId);
            if (!editorEl || !hiddenEl) return;

            // Pre-fill editor with existing HTML content
            editorEl.innerHTML = hiddenEl.value || '';

            const q = new Quill(editorEl, {
                theme: 'snow',
                modules: { toolbar: toolbar },
                placeholder: editorEl.dataset.placeholder || ''
            });

            // Keep hidden textarea in sync
            q.on('text-change', function() {
                hiddenEl.value = q.root.innerHTML === '<p><br></p>' ? '' : q.root.innerHTML;
            });

            // Also sync on form submit
            const form = hiddenEl.closest('form');
            if (form) {
                form.addEventListener('submit', function() {
                    hiddenEl.value = q.root.innerHTML === '<p><br></p>' ? '' : q.root.innerHTML;
                });
            }
        }

        // Members: bio
        initQuill('bioEditor', 'bio', toolbarMini);

        // Content: summary + body
        initQuill('summaryEditor', 'summary', toolbarMini);
        initQuill('bodyEditor', 'body', toolbarFull);
    })();
    </script>
    </body>
    </html>
    <?php
    echo ob_get_clean();
}
