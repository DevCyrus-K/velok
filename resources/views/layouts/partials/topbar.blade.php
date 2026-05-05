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
@endphp

<header class="topbar d-flex">
     <div class="container-fluid">
          <div class="navbar-header">

               <div class="d-flex align-items-center gap-2">
                    <form class="app-search d-none d-md-block me-auto">
                         <div class="position-relative">
                              <input type="search" class="form-control" placeholder="Start typing..." autocomplete="off" value="">
                              <i data-lucide="search" class="search-widget-icon"></i>
                         </div>
                    </form>
               </div>

               <div class="d-flex align-items-center gap-2 ms-auto">
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
                                   <img class="rounded-circle" width="32" src="{{ $topbarUser['avatar'] }}" alt="user-image" id="user-avatar">
                                   <span class="d-lg-flex flex-column gap-1 d-none">
                                        <h5 class="my-0 text-reset fs-14" id="user-name">{{ $topbarUser['name'] }}</h5>
                                   </span>
                              </span>
                         </a>
                         <div class="dropdown-menu dropdown-menu-end">

                              <a class="dropdown-item" href="{{ route('second', [ 'pages' , 'profile']) }}">
                                   <i data-lucide="circle-user" class="fs-16 text-muted align-middle me-2"></i><span class="align-middle">My Account</span>
                              </a>

                              <a class="dropdown-item" href="{{ route('second', [ 'pages' , 'pricing']) }}">
                                   <i data-lucide="badge-percent" class="fs-16 text-muted align-middle me-2"></i><span class="align-middle">Pricing</span>
                              </a>
                              <a class="dropdown-item" href="{{ route('second', [ 'reports' , 'overview']) }}">
                                   <i data-lucide="chart-column" class="fs-16 text-muted align-middle me-2"></i><span class="align-middle">Reports</span>
                              </a>
                              <a class="dropdown-item" href="{{ route('second', [ 'pages' , 'faqs']) }}">
                                   <i data-lucide="circle-help" class="fs-16 text-muted align-middle me-2"></i><span class="align-middle">Help</span>
                              </a>
                              <a class="dropdown-item" href="{{ route('second', [ 'pages' , 'gallery']) }}">
                                   <i data-lucide="book-image" class="fs-16 text-muted align-middle me-2"></i>
                                   <span class="align-middle">Photos</span>
                                   <span class="align-middle float-end badge badge-soft-danger">New</span>
                              </a>

                              <div class="dropdown-divider my-1"></div>

                              <a class="dropdown-item" href="{{ route('second', [ 'auth' , 'lock-screen']) }}">
                                   <i data-lucide="lock" class="fs-16 text-muted align-middle me-2"></i><span class="align-middle">Lock screen</span>
                              </a>
                              <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                   <i data-lucide="log-out" class="fs-16 text-muted align-middle me-2"></i><span class="align-middle">Logout</span>
                              </a>
                              <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
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
          refreshTopbarData();
          window.setInterval(refreshTopbarData, 30000);
     });

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
