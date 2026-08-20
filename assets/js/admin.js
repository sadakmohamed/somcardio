/**
 * Somali Cardiac Society — Admin Panel JavaScript
 */
document.addEventListener('DOMContentLoaded', () => {

    // ========== Sidebar Toggle for Mobile/Tablet ==========
    const headerToggle = document.getElementById('headerToggle');
    const adminSidebar = document.getElementById('adminSidebar');
    if (headerToggle && adminSidebar) {
        headerToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            adminSidebar.classList.toggle('open');
        });

        // Close sidebar when clicking outside of it on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                if (!adminSidebar.contains(e.target) && e.target !== headerToggle) {
                    adminSidebar.classList.remove('open');
                }
            }
        });
    }

    // ========== Image Preview on File Selection ==========
    const imageUploadInputs = document.querySelectorAll('.image-upload-input');
    imageUploadInputs.forEach(input => {
        input.addEventListener('change', function() {
            const previewId = this.dataset.preview;
            const previewImg = document.getElementById(previewId);
            
            if (previewImg && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // ========== Confirmation Dialogs for Dangerous Operations ==========
    const deleteButtons = document.querySelectorAll('.confirm-delete');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const itemType = this.dataset.item || 'item';
            const confirmMsg = `Are you sure you want to delete this ${itemType}? This action cannot be undone.`;
            if (!confirm(confirmMsg)) {
                e.preventDefault();
            }
        });
    });

    // ========== Auto Slug Generation (Content Section) ==========
    const titleInput = document.getElementById('contentTitle');
    const slugInput = document.getElementById('contentSlug');
    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function() {
            // Simple slug generation: lowercase, remove non-alphanumeric, replace spaces with hyphens
            let slug = this.value.toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s-]+/g, '-');
            slugInput.value = slug;
        });
    }

    // ========== Quick search for Admin Data Tables ==========
    const searchInput = document.getElementById('tableSearch');
    const dataTable = document.getElementById('dataTable');
    if (searchInput && dataTable) {
        const rows = dataTable.querySelectorAll('tbody tr');
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // ========== Alert Fadeout ==========
    const flashAlerts = document.querySelectorAll('.admin-content .alert');
    flashAlerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 400);
        }, 5000);
    });
});
