            </div><!-- .content-wrapper -->
            
            <footer class="admin-footer">
                <p>&copy; <?php echo date('Y'); ?> SDO CTS - San Pedro Division Office Complaint Tracking System</p>
            </footer>
        </main>
    </div>

    <script src="/SDO-cts/admin/assets/js/admin.js"></script>
    <script>
    // Real-time notification polling for new complaints
    (function() {
        const POLL_INTERVAL = 10000; // Check every 10 seconds
        let lastCount = 0;
        let isFirstLoad = true;
        
        // Initialize notification system
        function initNotifications() {
            const badge = document.getElementById('complaints-badge');
            if (badge) {
                lastCount = parseInt(badge.textContent) || 0;
            }
            
            // Start polling
            fetchNotificationCount();
            setInterval(fetchNotificationCount, POLL_INTERVAL);
        }
        
        // Fetch notification counts from API
        function fetchNotificationCount() {
            fetch('/SDO-cts/admin/api/notification-count.php', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateBadge(data.counts.complaints, data.hasNew && !isFirstLoad);
                    isFirstLoad = false;
                }
            })
            .catch(error => {
                console.log('Notification fetch error:', error);
            });
        }
        
        // Update the badge in the sidebar
        function updateBadge(count, showAlert) {
            const badge = document.getElementById('complaints-badge');
            if (!badge) return;
            
            const displayCount = count > 99 ? '99+' : count;
            
            if (count > 0) {
                badge.style.display = '';
                badge.textContent = displayCount;
                badge.title = count + ' pending';
                
                // If there's a new complaint, add animation
                if (showAlert && count > lastCount) {
                    badge.classList.add('badge-new');
                    playNotificationEffect();
                    
                    // Show toast notification
                    showNewComplaintToast(count - lastCount);
                    
                    // Remove animation class after animation completes
                    setTimeout(() => {
                        badge.classList.remove('badge-new');
                    }, 3000);
                }
            } else {
                badge.style.display = 'none';
            }
            
            lastCount = count;
        }
        
        // Play notification effect
        function playNotificationEffect() {
            const navItem = document.getElementById('nav-complaints');
            if (navItem) {
                navItem.classList.add('nav-item-highlight');
                setTimeout(() => {
                    navItem.classList.remove('nav-item-highlight');
                }, 3000);
            }
        }
        
        // Show toast notification for new complaints
        function showNewComplaintToast(newCount) {
            // Remove existing toast if any
            const existingToast = document.querySelector('.notification-toast');
            if (existingToast) {
                existingToast.remove();
            }
            
            const toast = document.createElement('div');
            toast.className = 'notification-toast';
            toast.innerHTML = `
                <div class="toast-icon"><i class="fas fa-bell"></i></div>
                <div class="toast-content">
                    <strong>New Complaint${newCount > 1 ? 's' : ''}!</strong>
                    <span>${newCount} new complaint${newCount > 1 ? 's' : ''} received</span>
                </div>
                <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
            `;
            
            document.body.appendChild(toast);
            
            // Trigger animation
            setTimeout(() => toast.classList.add('show'), 10);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }
        
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initNotifications);
        } else {
            initNotifications();
        }
    })();
    </script>
    <script>
    // Sidebar toggle - inline backup to ensure it works
    (function() {
        const sidebar = document.getElementById('sidebar');
        const adminLayout = document.querySelector('.admin-layout');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const desktopToggle = document.getElementById('desktopSidebarToggle');
        const mobileToggle = document.getElementById('mobileMenuToggle');
        
        if (!sidebar) {
            console.error('Sidebar element not found');
            return;
        }
        
        // Restore sidebar state from localStorage
        const savedState = localStorage.getItem('sidebarCollapsed') === 'true';
        if (savedState && window.innerWidth >= 992) {
            sidebar.classList.add('collapsed');
            if (adminLayout) adminLayout.classList.add('sidebar-collapsed');
        }
        
        // Function to toggle sidebar
        function toggleSidebar(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            const isCollapsed = sidebar.classList.toggle('collapsed');
            if (adminLayout) adminLayout.classList.toggle('sidebar-collapsed', isCollapsed);
            
            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', isCollapsed);
            
            console.log('Sidebar toggled:', isCollapsed ? 'collapsed' : 'expanded');
        }
        
        // Sidebar header toggle button
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', toggleSidebar);
        }
        
        // Desktop top bar toggle button
        if (desktopToggle) {
            desktopToggle.addEventListener('click', toggleSidebar);
        }
        
        // Mobile menu toggle
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar.classList.toggle('open');
            });
        }
        
        console.log('Sidebar toggle initialized');
    })();
    </script>
</body>
</html>

