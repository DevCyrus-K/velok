@php
     $topbarFallbackName = session('user_name', auth()->user()?->name ?? 'User');
     $topbarUser = array_replace([
          'name' => $topbarFallbackName,
          'email' => session('user_email', auth()->user()?->email ?? ''),
          'avatar' => session('user_avatar', '/images/users/avatar-1.jpg'),
          'initials' => session('user_avatar_initials', 'U'),
          'has_avatar' => session('user_has_avatar', false),
     ], is_array($topbarUser ?? null) ? $topbarUser : []);

     $topbarUser['name'] = trim((string) ($topbarUser['name'] ?? 'User')) ?: 'User';
     $topbarUser['initials'] = trim((string) ($topbarUser['initials'] ?? 'U')) ?: 'U';
     $topbarUser['has_avatar'] = filter_var($topbarUser['has_avatar'] ?? false, FILTER_VALIDATE_BOOLEAN);
     $topbarAvatarPlaceholder = 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=';

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
          ['title' => 'Help Center', 'section' => 'Content', 'url' => route('faqs.index'), 'icon' => 'circle-help', 'keywords' => 'help questions answers faq faqs'],
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
     .topbar-user-initials {
          align-items: center;
          background-color: rgba(var(--bs-primary-rgb), 0.12);
          color: var(--bs-primary);
          display: inline-flex;
          font-size: 0.75rem;
          font-weight: 700;
          justify-content: center;
          line-height: 1;
          text-transform: uppercase;
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
                                        <div class="col-auto">
                                             <button class="btn btn-sm btn-link p-0 text-decoration-none" id="mark-all-notifications-read" type="button" @unless($topbarNotifications['has_unread']) hidden @endunless>
                                                  <i data-lucide="check-check" class="icon-xs me-1"></i>Mark all
                                             </button>
                                        </div>
                                   </div>
                              </div>
                              <div data-simplebar style="max-height: 280px;" id="notifications-container">
                                   @forelse ($topbarNotifications['items'] as $notification)
                                   <div class="dropdown-item py-3 {{ $loop->last ? '' : 'border-bottom' }} d-flex align-items-start gap-2">
                                        <a href="{{ $notification['url'] }}" class="text-reset text-decoration-none flex-grow-1" data-notification-link data-notification-id="{{ $notification['id'] }}" data-notification-read-url="{{ $notification['mark_read_url'] }}">
                                             <p class="mb-0"><span class="fw-medium">{{ $notification['name'] }}</span></p>
                                             <p class="mb-0 text-wrap small">{{ $notification['subject'] }}</p>
                                             <p class="mb-0 text-muted small" data-notification-time="{{ $notification['created_at'] }}" title="{{ $notification['created_at_local'] ?? $notification['created_at_human'] }}">{{ $notification['created_at_human'] }}</p>
                                        </a>
                                        <button class="btn btn-sm btn-soft-secondary flex-shrink-0" type="button" data-notification-mark-read data-notification-id="{{ $notification['id'] }}" data-notification-read-url="{{ $notification['mark_read_url'] }}" title="Mark as read" aria-label="Mark notification as read">
                                             <i data-lucide="check" class="icon-xs"></i>
                                        </button>
                                   </div>
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
                                   <img class="topbar-user-avatar rounded-circle {{ $topbarUser['has_avatar'] ? '' : 'd-none' }}" src="{{ $topbarUser['has_avatar'] ? $topbarUser['avatar'] : $topbarAvatarPlaceholder }}" alt="{{ $topbarUser['name'] }} avatar" id="user-avatar" width="32" height="32">
                                   <span class="topbar-user-avatar topbar-user-initials rounded-circle {{ $topbarUser['has_avatar'] ? 'd-none' : '' }}" id="user-avatar-initials" aria-label="{{ $topbarUser['name'] }} initials">{{ $topbarUser['initials'] }}</span>
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
                                   <i data-lucide="circle-help" class="fs-16 text-muted align-middle me-2"></i><span class="align-middle">Help Center</span>
                              </a>
                              <a class="dropdown-item" href="{{ route('settings.index') }}">
                                   <i data-lucide="settings" class="fs-16 text-muted align-middle me-2"></i>
                                   <span class="align-middle">Settings</span>
                              </a>

                              <div class="dropdown-divider my-1"></div>

                              <a class="dropdown-item" href="{{ route('lock-screen') }}">
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
          initNotificationActions();
          updateRenderedNotificationTimes();
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
                    updateTopbarUser(data.user);
               }

               if (data.notifications) {
                    updateNotificationsUI(data.notifications);
               }
          })
          .catch(error => {
               console.error('Error fetching topbar data:', error);
          });
     }

     function updateTopbarUser(user) {
          const name = String(user.name ?? 'User').trim() || 'User';
          const initials = String(user.initials ?? '').trim() || initialsFromName(name);
          const hasAvatar = toBoolean(user.has_avatar) && Boolean(user.avatar);
          const nameLabel = document.getElementById('user-name');
          const avatarImage = document.getElementById('user-avatar');
          const avatarInitials = document.getElementById('user-avatar-initials');

          if (nameLabel) {
               nameLabel.textContent = name;
          }

          if (avatarImage) {
               avatarImage.src = hasAvatar ? user.avatar : @json($topbarAvatarPlaceholder);
               avatarImage.alt = `${name} avatar`;
               avatarImage.classList.toggle('d-none', !hasAvatar);
          }

          if (avatarInitials) {
               avatarInitials.textContent = initials;
               avatarInitials.setAttribute('aria-label', `${name} initials`);
               avatarInitials.classList.toggle('d-none', hasAvatar);
          }
     }

     function updateNotificationsUI(notifications) {
          const count = Number(notifications.count ?? 0);
          const badge = document.getElementById('notification-count');
          const badgeLabel = document.getElementById('notification-count-label');
          const badgeScreenReader = document.getElementById('notification-count-sr');
          const container = document.getElementById('notifications-container');
          const markAllButton = document.getElementById('mark-all-notifications-read');

          badge.hidden = count < 1;
          badgeLabel.textContent = notifications.display_count ?? formatNotificationCount(count);
          badgeScreenReader.textContent = `${count} unread notifications`;

          if (markAllButton) {
               markAllButton.hidden = count < 1;
          }

          if (!Array.isArray(notifications.items) || notifications.items.length === 0) {
               container.innerHTML = '<div class="text-center p-3"><p class="text-muted mb-0">No new notifications</p></div>';
               return;
          }

          container.innerHTML = notifications.items.map((notification, index) => `
               <div class="dropdown-item py-3 ${index === notifications.items.length - 1 ? '' : 'border-bottom'} d-flex align-items-start gap-2">
                    <a href="${escapeAttribute(notification.url ?? '#')}" class="text-reset text-decoration-none flex-grow-1" data-notification-link data-notification-id="${escapeAttribute(notification.id ?? '')}" data-notification-read-url="${escapeAttribute(notification.mark_read_url ?? '')}">
                         <p class="mb-0"><span class="fw-medium">${escapeHtml(notification.name ?? 'New notification')}</span></p>
                         <p class="mb-0 text-wrap small">${escapeHtml(notification.subject ?? 'New notification')}</p>
                         <p class="mb-0 text-muted small" data-notification-time="${escapeAttribute(notification.created_at ?? '')}" title="${escapeAttribute(notification.created_at_local ?? notification.created_at_human ?? '')}">${escapeHtml(formatNotificationTime(notification))}</p>
                    </a>
                    <button class="btn btn-sm btn-soft-secondary flex-shrink-0" type="button" data-notification-mark-read data-notification-id="${escapeAttribute(notification.id ?? '')}" data-notification-read-url="${escapeAttribute(notification.mark_read_url ?? '')}" title="Mark as read" aria-label="Mark notification as read">
                         <i data-lucide="check" class="icon-xs"></i>
                    </button>
               </div>
          `).join('');

          if (window.lucide?.createIcons) {
               window.lucide.createIcons();
          }
     }

     function initNotificationActions() {
          const container = document.getElementById('notifications-container');
          const markAllButton = document.getElementById('mark-all-notifications-read');

          container?.addEventListener('click', async function(event) {
               const markButton = event.target.closest('[data-notification-mark-read]');
               const link = event.target.closest('[data-notification-link]');

               if (markButton) {
                    event.preventDefault();
                    event.stopPropagation();
                    await markNotificationRead(markButton.dataset.notificationReadUrl);
                    return;
               }

               if (link) {
                    const url = link.getAttribute('href') || '#';
                    const readUrl = link.dataset.notificationReadUrl;

                    if (!readUrl || url === '#') {
                         return;
                    }

                    event.preventDefault();

                    try {
                         await markNotificationRead(readUrl);
                    } finally {
                         window.location.href = url;
                    }
               }
          });

          markAllButton?.addEventListener('click', async function(event) {
               event.preventDefault();
               await markNotificationRead(@json(route('topbar.notifications.read-all')));
          });
     }

     async function markNotificationRead(url) {
          if (!url) {
               return;
          }

          const response = await fetch(url, {
               method: 'POST',
               headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token()),
               },
          });

          if (!response.ok) {
               throw new Error('Notification could not be marked as read.');
          }

          const data = await response.json();

          if (data.notifications) {
               updateNotificationsUI(data.notifications);
          } else {
               refreshTopbarData();
          }
     }

     function updateRenderedNotificationTimes() {
          document.querySelectorAll('[data-notification-time]').forEach((element) => {
               const isoDate = element.getAttribute('data-notification-time');

               if (!isoDate) {
                    return;
               }

               element.textContent = relativeTimeLabel(new Date(isoDate));
          });
     }

     function formatNotificationTime(notification) {
          if (notification?.created_at) {
               return relativeTimeLabel(new Date(notification.created_at));
          }

          return notification?.created_at_human ?? '';
     }

     function relativeTimeLabel(date) {
          if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
               return '';
          }

          const seconds = Math.round((date.getTime() - Date.now()) / 1000);
          const absoluteSeconds = Math.abs(seconds);
          const units = [
               ['year', 31536000],
               ['month', 2592000],
               ['day', 86400],
               ['hour', 3600],
               ['minute', 60],
               ['second', 1],
          ];
          const [unit, unitSeconds] = units.find(([, value]) => absoluteSeconds >= value) || ['second', 1];
          const value = Math.round(seconds / unitSeconds);

          return new Intl.RelativeTimeFormat(undefined, { numeric: 'auto' }).format(value, unit);
     }

     function formatNotificationCount(count) {
          if (count > 9) {
               return '9+';
          }

          return String(Math.max(count, 0));
     }

     function initialsFromName(name) {
          const parts = String(name ?? 'User').trim().split(/\s+/).filter(Boolean);

          if (parts.length === 0) {
               return 'U';
          }

          const first = parts[0].charAt(0);
          const last = parts.length > 1 ? parts[parts.length - 1].charAt(0) : '';

          return `${first}${last}`.toUpperCase();
     }

     function toBoolean(value) {
          return value === true || value === 1 || value === '1' || value === 'true';
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
