@php
     $topbarUser = $topbarUser ?? [
          'name' => session('user_name', auth()->user()?->name ?? 'User'),
          'email' => session('user_email', auth()->user()?->email ?? ''),
          'avatar' => session('user_avatar', '/images/users/avatar-1.jpg'),
     ];

     $topbarNotifications = $topbarNotifications ?? [
          'count' => 0,
          'display_count' => '0',
          'has_unread' => false,
          'items' => [],
     ];

     $topbarSearchItems = [
          ['title' => 'Dashboard', 'section' => 'Main', 'url' => route('second', ['dashboard', 'index']), 'icon' => 'layout-dashboard', 'keywords' => 'home metrics overview'],
          ['title' => 'Quotes', 'section' => 'Sales', 'url' => route('quotes.index'), 'icon' => 'message-square-quote', 'keywords' => 'leads requests inquiries'],
          ['title' => 'Create Invoice', 'section' => 'Sales', 'url' => route('invoice.create'), 'icon' => 'file-plus-2', 'keywords' => 'billing new invoice'],
          ['title' => 'Invoices', 'section' => 'Sales', 'url' => route('invoice.index'), 'icon' => 'receipt-text', 'keywords' => 'billing payments'],
          ['title' => 'Customers', 'section' => 'Sales', 'url' => route('any', 'customers'), 'icon' => 'book-user', 'keywords' => 'contacts clients'],
          ['title' => 'Messages', 'section' => 'Sales', 'url' => route('messages.index'), 'icon' => 'mail', 'keywords' => 'inbox notifications email'],
          ['title' => 'Gallery', 'section' => 'Content', 'url' => route('gallery.index'), 'icon' => 'images', 'keywords' => 'photos images media'],
          ['title' => 'FAQs', 'section' => 'Content', 'url' => route('faqs.index'), 'icon' => 'circle-help', 'keywords' => 'help questions answers'],
          ['title' => 'Reviews', 'section' => 'Content', 'url' => route('reviews.index'), 'icon' => 'star', 'keywords' => 'testimonials ratings'],
          ['title' => 'Careers', 'section' => 'Content', 'url' => route('careers.jobs.index'), 'icon' => 'briefcase', 'keywords' => 'jobs applications hiring'],
          ['title' => 'Reports', 'section' => 'Insights', 'url' => route('second', ['reports', 'overview']), 'icon' => 'chart-column', 'keywords' => 'analytics performance'],
          ['title' => 'My Account', 'section' => 'Workspace', 'url' => route('account.show'), 'icon' => 'circle-user', 'keywords' => 'profile security password user'],
          ['title' => 'Settings', 'section' => 'Workspace', 'url' => route('settings.index'), 'icon' => 'settings', 'keywords' => 'payments smtp brevo resend analytics sms integrations'],
          ['title' => 'Manage Apps', 'section' => 'Workspace', 'url' => route('settings.apps'), 'icon' => 'layout-grid', 'keywords' => 'integrations connected apps sms email analytics payments'],
          ['title' => 'Todo', 'section' => 'Workspace', 'url' => route('todo.index'), 'icon' => 'clipboard-check', 'keywords' => 'tasks checklist'],
     ];
@endphp

<style>
     .topbar-search-results {
          display: none;
          left: 0;
          max-height: 320px;
          overflow: auto;
          position: absolute;
          right: 0;
          top: calc(100% + 8px);
          z-index: 1050;
     }
     .topbar-search-results.show {
          display: block;
     }
     .topbar-search-result-icon {
          width: 2rem;
          height: 2rem;
          flex: 0 0 2rem;
          display: inline-flex;
          align-items: center;
          justify-content: center;
     }
     .topbar-search-result-copy {
          min-width: 0;
     }
     .topbar-left {
          flex: 1 1 auto;
          min-width: 0;
     }
     .topbar-actions {
          flex: 0 0 auto;
     }
     .topbar-app-search {
          flex: 1 1 280px;
          max-width: 420px;
          min-width: 180px;
     }
     .topbar-user-avatar {
          border-radius: 50%;
          display: block;
          flex: 0 0 32px;
          height: 32px;
          object-fit: cover;
          width: 32px;
     }
     @media (max-width: 767.98px) {
          .topbar .navbar-header {
               gap: 8px;
               padding-left: 12px;
               padding-right: 12px;
          }
          .topbar-app-search {
               flex-basis: auto;
               max-width: none;
               min-width: 0;
          }
          .topbar-app-search .form-control {
               font-size: 13px;
               height: 36px;
               min-width: 0;
               padding-left: 34px;
               padding-right: 10px;
          }
          .topbar-actions {
               gap: 4px !important;
          }
          .topbar .topbar-item .topbar-button {
               padding-left: 6px;
               padding-right: 6px;
          }
     }
</style>

<header class="topbar d-flex">
     <div class="container-fluid">
          <div class="navbar-header">

               <div class="d-flex align-items-center gap-2 topbar-left">
                    <div class="topbar-item d-xl-none">
                         <button type="button" class="topbar-button fs-24 button-toggle-menu" aria-label="Open sidebar" aria-controls="navbar-nav" aria-expanded="false">
                              <i data-lucide="menu"></i>
                         </button>
                    </div>

                    <form class="app-search topbar-app-search me-auto" id="topbar-search-form" role="search">
                         <div class="position-relative">
                              <input type="search" class="form-control" placeholder="Search pages..." autocomplete="off" value="" id="topbar-search-input" aria-label="Search pages">
                              <i data-lucide="search" class="search-widget-icon"></i>
                              <div class="dropdown-menu shadow-sm topbar-search-results" id="topbar-search-results"></div>
                         </div>
                    </form>
               </div>

               <div class="d-flex align-items-center gap-2 ms-auto topbar-actions">
                    <div class="topbar-item">
                         <button type="button" class="topbar-button fs-24" id="light-dark-mode">
                              <i data-lucide="moon" class="light-mode"></i>
                              <i data-lucide="sun" class="dark-mode"></i>
                         </button>
                    </div>

                    <div class="dropdown topbar-item">
                         <button type="button" class="topbar-button position-relative" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <i data-lucide="bell" class="fs-20"></i>
                              <span class="topbar-badge text-bg-danger rounded-pill" id="notification-count" @unless($topbarNotifications['has_unread']) hidden @endunless>
                                   <span id="notification-count-label">{{ $topbarNotifications['display_count'] }}</span>
                                   <span class="visually-hidden" id="notification-count-sr">{{ $topbarNotifications['count'] }} unread messages</span>
                              </span>
                         </button>
                         <div class="dropdown-menu pt-0 dropdown-lg dropdown-menu-end" aria-labelledby="page-header-notifications-dropdown">
                              <div class="p-3 border-top-0 border-start-0 border-end-0 border-dashed border">
                                   <div class="row align-items-center">
                                        <div class="col">
                                             <h6 class="m-0 fs-16 fw-semibold">Notifications</h6>
                                        </div>
                                   </div>
                              </div>
                              <div data-simplebar style="max-height: 280px;" id="notifications-container">
                                   @forelse ($topbarNotifications['items'] as $notification)
                                   <a href="{{ $notification['url'] }}" class="dropdown-item py-3 {{ $loop->last ? '' : 'border-bottom' }}">
                                        <p class="mb-0"><span class="fw-medium">{{ $notification['name'] }}</span></p>
                                        <p class="mb-0 text-wrap small">{{ $notification['subject'] }}</p>
                                        <p class="mb-0 text-muted small">{{ $notification['created_at_human'] }}</p>
                                   </a>
                                   @empty
                                   <div class="text-center p-3">
                                        <p class="text-muted mb-0">No new notifications</p>
                                   </div>
                                   @endforelse
                              </div>
                         </div>
                    </div>

                    <div class="dropdown topbar-item">
                         <a type="button" class="topbar-button p-0" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <span class="d-flex align-items-center gap-2">
                                   <img class="topbar-user-avatar rounded-circle" src="{{ $topbarUser['avatar'] }}" alt="user-image" id="user-avatar" width="32" height="32">
                                   <span class="d-lg-flex flex-column gap-1 d-none">
                                        <h5 class="my-0 text-reset fs-14" id="user-name">{{ $topbarUser['name'] }}</h5>
                                   </span>
                              </span>
                         </a>
                         <div class="dropdown-menu dropdown-menu-end">

                              <a class="dropdown-item" href="{{ route('account.show') }}">
                                   <i data-lucide="circle-user" class="fs-16 text-muted align-middle me-2"></i><span class="align-middle">My Account</span>
                              </a>

                              <a class="dropdown-item" href="{{ route('second', [ 'reports' , 'overview']) }}">
                                   <i data-lucide="chart-column" class="fs-16 text-muted align-middle me-2"></i><span class="align-middle">Reports</span>
                              </a>
                              <a class="dropdown-item" href="{{ route('faqs.index') }}">
                                   <i data-lucide="circle-help" class="fs-16 text-muted align-middle me-2"></i><span class="align-middle">Help</span>
                              </a>
                              <a class="dropdown-item" href="{{ route('gallery.index') }}">
                                   <i data-lucide="images" class="fs-16 text-muted align-middle me-2"></i>
                                   <span class="align-middle">Gallery</span>
                              </a>

                              <div class="dropdown-divider my-1"></div>

                              <a class="dropdown-item" href="{{ route('second', [ 'auth' , 'lock-screen']) }}">
                                   <i data-lucide="lock" class="fs-16 text-muted align-middle me-2"></i><span class="align-middle">Lock screen</span>
                              </a>
                              <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); const form = document.getElementById('logout-form'); form.requestSubmit ? form.requestSubmit() : form.submit();">
                                   <i data-lucide="log-out" class="fs-16 text-muted align-middle me-2"></i><span class="align-middle">Logout</span>
                              </a>
                              <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;" data-action-message="Logging you out...">
                                   @csrf
                              </form>
                         </div>
                    </div>
               </div>
          </div>
     </div>
</header>

<script>
     document.addEventListener('DOMContentLoaded', function() {
          initTopbarSearch();
          refreshTopbarData();
          window.setInterval(refreshTopbarData, 30000);
     });

     function initTopbarSearch() {
          const form = document.getElementById('topbar-search-form');
          const input = document.getElementById('topbar-search-input');
          const results = document.getElementById('topbar-search-results');
          const searchItems = @json($topbarSearchItems);

          if (!form || !input || !results || !Array.isArray(searchItems)) {
               return;
          }

          let currentMatches = [];
          const normalize = (value) => String(value ?? '').toLowerCase();

          const renderResults = () => {
               const query = normalize(input.value).trim();
               currentMatches = searchItems
                    .filter((item) => {
                         const haystack = normalize(`${item.title} ${item.section} ${item.keywords}`);
                         return query === '' || haystack.includes(query);
                    })
                    .slice(0, 7);

               if (currentMatches.length === 0) {
                    results.innerHTML = '<div class="dropdown-item-text text-muted small py-2">No matching page found</div>';
                    results.classList.add('show');
                    return;
               }

               results.innerHTML = currentMatches.map((item) => `
                    <a class="dropdown-item py-2 d-flex align-items-start gap-2" href="${escapeAttribute(item.url)}">
                         <span class="topbar-search-result-icon rounded bg-light text-primary">
                              <iconify-icon icon="lucide:${escapeAttribute(item.icon || 'file')}"></iconify-icon>
                         </span>
                         <span class="topbar-search-result-copy">
                              <span class="fw-medium d-block">${escapeHtml(item.title)}</span>
                              <small class="text-muted d-block">${escapeHtml(item.section)}</small>
                         </span>
                    </a>
               `).join('');
               results.classList.add('show');
          };

          input.addEventListener('input', renderResults);
          input.addEventListener('focus', renderResults);
          input.addEventListener('keydown', function(event) {
               if (event.key === 'Escape') {
                    results.classList.remove('show');
                    input.blur();
               }
          });

          form.addEventListener('submit', function(event) {
               event.preventDefault();

               if (currentMatches.length > 0) {
                    window.location.href = currentMatches[0].url;
               }
          });

          document.addEventListener('click', function(event) {
               if (!form.contains(event.target)) {
                    results.classList.remove('show');
               }
          });
     }

     function refreshTopbarData() {
          fetch(@json(route('topbar.data')), {
               method: 'GET',
               headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
               }
          })
          .then(response => {
               if (!response.ok) {
                    throw new Error('Failed to fetch topbar data.');
               }

               return response.json();
          })
          .then(data => {
               if (data.user) {
                    document.getElementById('user-name').textContent = data.user.name ?? 'User';
                    document.getElementById('user-avatar').src = data.user.avatar ?? '/images/users/avatar-1.jpg';
               }

               if (data.notifications) {
                    updateNotificationsUI(data.notifications);
               }
          })
          .catch(error => {
               console.error('Error fetching topbar data:', error);
          });
     }

     function updateNotificationsUI(notifications) {
          const count = Number(notifications.count ?? 0);
          const badge = document.getElementById('notification-count');
          const badgeLabel = document.getElementById('notification-count-label');
          const badgeScreenReader = document.getElementById('notification-count-sr');
          const container = document.getElementById('notifications-container');

          badge.hidden = count < 1;
          badgeLabel.textContent = notifications.display_count ?? formatNotificationCount(count);
          badgeScreenReader.textContent = `${count} unread messages`;

          if (!Array.isArray(notifications.items) || notifications.items.length === 0) {
               container.innerHTML = '<div class="text-center p-3"><p class="text-muted mb-0">No new notifications</p></div>';
               return;
          }

          container.innerHTML = notifications.items.map((notification, index) => `
               <a href="${escapeAttribute(notification.url ?? '#')}" class="dropdown-item py-3 ${index === notifications.items.length - 1 ? '' : 'border-bottom'}">
                    <p class="mb-0"><span class="fw-medium">${escapeHtml(notification.name ?? 'New message')}</span></p>
                    <p class="mb-0 text-wrap small">${escapeHtml(notification.subject ?? 'New notification')}</p>
                    <p class="mb-0 text-muted small">${escapeHtml(notification.created_at_human ?? '')}</p>
               </a>
          `).join('');
     }

     function formatNotificationCount(count) {
          if (count > 9) {
               return '9+';
          }

          return String(Math.max(count, 0));
     }

     function escapeHtml(text) {
          const div = document.createElement('div');
          div.textContent = text;
          return div.innerHTML;
     }

     function escapeAttribute(text) {
          return escapeHtml(text).replace(/"/g, '&quot;');
     }
</script>
