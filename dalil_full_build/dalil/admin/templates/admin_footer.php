</div> <!-- End .admin-content -->
    </main> <!-- End .admin-main -->
</div> <!-- End .admin-wrapper -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Logic for sidebar submenu toggling
    const submenuToggles = document.querySelectorAll('.sidebar-nav .submenu-toggle');

    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const parentLi = this.parentElement;
            const submenu = parentLi.querySelector('.submenu');

            if (parentLi.classList.contains('open')) {
                // This is to allow closing the menu by clicking it again
                submenu.style.display = 'none';
                parentLi.classList.remove('open');
            } else {
                // This part is optional: close other open submenus
                document.querySelectorAll('.sidebar-nav .has-submenu.open').forEach(openSubmenu => {
                    openSubmenu.classList.remove('open');
                    openSubmenu.querySelector('.submenu').style.display = 'none';
                });

                submenu.style.display = 'block';
                parentLi.classList.add('open');
            }
        });
    });

    // Auto-hide messages after 5 seconds
    const messages = document.querySelectorAll('.message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => {
                message.remove();
            }, 300);
        }, 5000);
    });

    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('.delete-btn, .action-btn[data-action="delete"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('هل أنت متأكد من الحذف؟ هذا الإجراء لا يمكن التراجع عنه.')) {
                e.preventDefault();
            }
        });
    });
});
</script>
</body>
</html>
