</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        (function() {
            const layout = document.getElementById('app-layout');
            const toggleBtn = document.getElementById('sidebar-toggle');
            const sidebar = document.querySelector('.sidebar');
            const closeBtn = document.querySelector('.sidebar-close');
            const overlay = document.querySelector('.sidebar-overlay');

            if (!layout || !toggleBtn) return;

            function isMobile() {
                return window.matchMedia('(max-width: 991.98px)').matches;
            }
            let lastIsMobile = isMobile();

            function updateAria() {
                const expanded = isMobile() ?
                    layout.classList.contains('sidebar-open') :
                    !layout.classList.contains('sidebar-collapsed');
                toggleBtn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            }

            function closeMobile() {
                layout.classList.remove('sidebar-open');
                if (overlay) overlay.classList.remove('is-visible');
                if (sidebar) sidebar.classList.remove('is-open');
                if (sidebar) {
                    sidebar.style.display = 'none';
                    sidebar.style.transform = 'translateX(-100%)';
                }
                updateAria();
            }

            function syncMode() {
                if (isMobile()) {
                    layout.classList.remove('sidebar-collapsed');
                    layout.classList.remove('sidebar-open');
                    if (sidebar) sidebar.classList.remove('is-collapsed');
                    if (sidebar) sidebar.classList.remove('is-open');
                    if (overlay) overlay.classList.remove('is-visible');
                    if (sidebar) {
                        sidebar.style.display = 'none';
                        sidebar.style.transform = 'translateX(-100%)';
                    }
                } else {
                    layout.classList.remove('sidebar-open');
                    if (sidebar) sidebar.classList.remove('is-open');
                    if (overlay) overlay.classList.remove('is-visible');
                    if (sidebar) {
                        sidebar.style.display = '';
                        sidebar.style.transform = '';
                    }
                }
                updateAria();
            }

            function toggleSidebarAction() {
                if (isMobile()) {
                    layout.classList.remove('sidebar-collapsed');
                    layout.classList.toggle('sidebar-open');
                    if (sidebar) sidebar.classList.toggle('is-open');
                    if (overlay) overlay.classList.toggle('is-visible');
                    if (sidebar) {
                        const isOpen = layout.classList.contains('sidebar-open');
                        sidebar.style.display = isOpen ? 'block' : 'none';
                        sidebar.style.position = 'fixed';
                        sidebar.style.left = '0';
                        sidebar.style.top = '0';
                        sidebar.style.zIndex = '1050';
                        sidebar.style.width = '260px';
                        sidebar.style.maxWidth = '85vw';
                        sidebar.style.height = '100vh';
                        sidebar.style.transform = isOpen ? 'translateX(0)' : 'translateX(-100%)';
                    }
                } else {
                    layout.classList.toggle('sidebar-collapsed');
                    if (sidebar) sidebar.classList.toggle('is-collapsed');
                    if (sidebar) {
                        const isCollapsed = layout.classList.contains('sidebar-collapsed');
                        sidebar.style.display = isCollapsed ? 'none' : '';
                        sidebar.style.transform = isCollapsed ? 'translateX(-100%)' : '';
                    }
                }
                updateAria();
            }

            toggleBtn.addEventListener('click', toggleSidebarAction);

            window.toggleSidebar = toggleSidebarAction;

            if (closeBtn) {
                closeBtn.addEventListener('click', closeMobile);
            }

            if (overlay) {
                overlay.addEventListener('click', closeMobile);
            }

            window.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && isMobile() && layout.classList.contains('sidebar-open')) {
                    closeMobile();
                }
            });

            window.addEventListener('resize', function() {
                const nowIsMobile = isMobile();
                if (nowIsMobile !== lastIsMobile) {
                    lastIsMobile = nowIsMobile;
                    syncMode();
                }
            });
            syncMode();
        })();
    });
</script>
</body>

</html>
