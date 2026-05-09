/**
 * Mobile Sidebar Toggle
 * Handles responsive sidebar collapse and expand on mobile devices
 */

(function() {
     const SIDEBAR_ENABLE_CLASS = 'sidebar-enable';
     const SIDEBAR_OVERLAY_ID = 'sidebar-overlay';
     const TOGGLE_BTN_SELECTOR = '.button-toggle-menu';
     const MOBILE_BREAKPOINT = 1024; // lg breakpoint
     const NAV_SELECTOR = '#navbar-nav';
     const MAIN_NAV_SELECTOR = '.main-nav';

     // Initialize sidebar functionality
     function initSidebar() {
          const toggleButtons = document.querySelectorAll(TOGGLE_BTN_SELECTOR);
          const mainNav = document.querySelector(MAIN_NAV_SELECTOR);
          const navContainer = document.querySelector(NAV_SELECTOR);

          if (!toggleButtons.length || !mainNav) {
               return;
          }

          // Create overlay backdrop
          let overlay = document.getElementById(SIDEBAR_OVERLAY_ID);
          if (!overlay) {
               overlay = document.createElement('div');
               overlay.id = SIDEBAR_OVERLAY_ID;
               overlay.className = 'sidebar-overlay';
               document.body.appendChild(overlay);
          }

          // Add click handlers to all toggle buttons
          toggleButtons.forEach(btn => {
               btn.addEventListener('click', handleToggleClick);
          });

          // Close sidebar when overlay is clicked
          overlay.addEventListener('click', closeSidebar);

          // Handle window resize
          let resizeTimeout;
          window.addEventListener('resize', () => {
               clearTimeout(resizeTimeout);
               resizeTimeout = setTimeout(handleResize, 250);
          });

          // Handle escape key
          document.addEventListener('keydown', (e) => {
               if (e.key === 'Escape') {
                    closeSidebar();
               }
          });

          // Close sidebar when navigation links are clicked (on mobile)
          if (navContainer) {
               const navLinks = navContainer.querySelectorAll('a');
               navLinks.forEach(link => {
                    link.addEventListener('click', () => {
                         if (window.innerWidth < MOBILE_BREAKPOINT) {
                              closeSidebar();
                         }
                    });
               });
          }
     }

     // Handle toggle button click
     function handleToggleClick(e) {
          e.preventDefault();
          e.stopPropagation();

          const html = document.documentElement;
          const isSidebarOpen = html.classList.contains(SIDEBAR_ENABLE_CLASS);

          if (isSidebarOpen) {
               closeSidebar();
          } else {
               openSidebar();
          }
     }

     // Open sidebar
     function openSidebar() {
          const html = document.documentElement;
          const overlay = document.getElementById(SIDEBAR_OVERLAY_ID);

          html.classList.add(SIDEBAR_ENABLE_CLASS);
          if (overlay) {
               overlay.classList.add('show');
          }
          document.body.style.overflow = 'hidden';
     }

     // Close sidebar
     function closeSidebar() {
          const html = document.documentElement;
          const overlay = document.getElementById(SIDEBAR_OVERLAY_ID);

          html.classList.remove(SIDEBAR_ENABLE_CLASS);
          if (overlay) {
               overlay.classList.remove('show');
          }
          document.body.style.overflow = '';
     }

     // Handle window resize
     function handleResize() {
          if (window.innerWidth >= MOBILE_BREAKPOINT) {
               // On desktop, always show sidebar and remove overlay
               closeSidebar();
               document.documentElement.classList.remove(SIDEBAR_ENABLE_CLASS);
          } else {
               // On mobile, hide sidebar by default
               closeSidebar();
          }
     }

     // Initialize when DOM is ready
     if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', initSidebar);
     } else {
          initSidebar();
     }

     // Expose functions to window for manual control if needed
     window.sidebarToggle = {
          open: openSidebar,
          close: closeSidebar,
          toggle: handleToggleClick
     };
})();
