<div class="main-nav">
     <div class="d-flex justify-content-between main-logo-box">
          <div class="logo-box">
               <a href="{{ route('second', ['dashboard', 'index']) }}" class="logo-dark">
                    <img src="/images/logo-sm.png" class="logo-sm" alt="logo sm">
                    <img src="/images/logo-dark.png" class="logo-lg" alt="logo dark">
               </a>

               <a href="{{ route('second', ['dashboard', 'index']) }}" class="logo-light">
                    <img src="/images/logo-sm.png" class="logo-sm" alt="logo sm">
                    <img src="/images/logo-white.png" class="logo-lg" alt="logo light">
               </a>
          </div>

          <button type="button" class="btn btn-link d-flex button-sm-hover button-toggle-menu" aria-label="Show Full Sidebar">
               <i data-lucide="menu" class="button-sm-hover-icon"></i>
          </button>
     </div>

     <div class="h-100" data-simplebar>
          <ul class="navbar-nav" id="navbar-nav">
               <li class="menu-title">Main</li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->is('dashboard/*') ? 'active' : '' }}" href="{{ route('second', ['dashboard', 'index']) }}">
                         <span class="nav-icon">
                              <i data-lucide="layout-dashboard"></i>
                         </span>
                         <span class="nav-text">Dashboard</span>
                    </a>
               </li>

               <li class="menu-title">Sales</li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->routeIs('quotes.*') ? 'active' : '' }}" href="{{ route('quotes.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="message-square-quote"></i>
                         </span>
                         <span class="nav-text">Quotes</span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->is('customers*') ? 'active' : '' }}" href="{{ route('any', 'customers') }}">
                         <span class="nav-icon">
                              <i data-lucide="book-user"></i>
                         </span>
                         <span class="nav-text">Customers</span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->routeIs('messages.*') ? 'active' : '' }}" href="{{ route('messages.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="mail"></i>
                         </span>
                         <span class="nav-text">Messages</span>
                         @php
                              $unreadCount = \App\Models\Message::where('status', 'unread')->count();
                         @endphp
                         @if($unreadCount > 0)
                              <span class="badge bg-danger badge-pill text-end">{{ $unreadCount }}</span>
                         @endif
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->routeIs('invoice.*') ? 'active' : '' }}" href="{{ route('invoice.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="receipt-text"></i>
                         </span>
                         <span class="nav-text">Invoices</span>
                    </a>
               </li>

               <li class="menu-title">Content</li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->routeIs('gallery.*') && !request()->routeIs('gallery.asset') ? 'active' : '' }}" href="{{ route('gallery.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="images"></i>
                         </span>
                         <span class="nav-text">Gallery</span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->routeIs('faqs.*') ? 'active' : '' }}" href="{{ route('faqs.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="circle-help"></i>
                         </span>
                         <span class="nav-text">FAQs</span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->routeIs('reviews.*') ? 'active' : '' }}" href="{{ route('reviews.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="star"></i>
                         </span>
                         <span class="nav-text">Reviews</span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->routeIs('careers.*') ? 'active' : '' }}" href="#sidebarCareers" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('careers.*') ? 'true' : 'false' }}" aria-controls="sidebarCareers">
                         <span class="nav-icon">
                              <i data-lucide="briefcase"></i>
                         </span>
                         <span class="nav-text">Careers</span>
                         <span class="menu-arrow"><x-icons.chevron-down /></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('careers.*') ? 'show' : '' }}" id="sidebarCareers">
                         <ul class="sub-menu-nav">
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link {{ request()->routeIs('careers.jobs.*') ? 'active' : '' }}" href="{{ route('careers.jobs.index') }}">Jobs</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link {{ request()->routeIs('careers.applications.*') ? 'active' : '' }}" href="{{ route('careers.applications.index') }}">Applications</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="menu-title">Reports</li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->is('reports/overview') ? 'active' : '' }}" href="{{ route('second', ['reports', 'overview']) }}">
                         <span class="nav-icon">
                              <i data-lucide="chart-column"></i>
                         </span>
                         <span class="nav-text">Overview</span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->is('reports/customer-lifecycle') ? 'active' : '' }}" href="{{ route('second', ['reports', 'customer-lifecycle']) }}">
                         <span class="nav-icon">
                              <i data-lucide="users"></i>
                         </span>
                         <span class="nav-text">Customer Report</span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->is('reports/financial-reports') ? 'active' : '' }}" href="{{ route('second', ['reports', 'financial-reports']) }}">
                         <span class="nav-icon">
                              <i data-lucide="wallet"></i>
                         </span>
                         <span class="nav-text">Financial Report</span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->is('reports/email-delivery') ? 'active' : '' }}" href="{{ route('second', ['reports', 'email-delivery']) }}">
                         <span class="nav-icon">
                              <i data-lucide="mail-check"></i>
                         </span>
                         <span class="nav-text">Email Delivery</span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->is('reports/visitor-reports') ? 'active' : '' }}" href="{{ route('second', ['reports', 'visitor-reports']) }}">
                         <span class="nav-icon">
                              <i data-lucide="chart-no-axes-combined"></i>
                         </span>
                         <span class="nav-text">Visitor Insights</span>
                    </a>
               </li>

               <li class="menu-title">Workspace</li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->routeIs('account.*') ? 'active' : '' }}" href="{{ route('account.show') }}">
                         <span class="nav-icon">
                              <i data-lucide="circle-user"></i>
                         </span>
                         <span class="nav-text">My Account</span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->routeIs('settings.index') || request()->routeIs('settings.update') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="settings"></i>
                         </span>
                         <span class="nav-text">Settings</span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->routeIs('todo.*') ? 'active' : '' }}" href="{{ route('todo.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="clipboard-check"></i>
                         </span>
                         <span class="nav-text">Todo</span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->routeIs('settings.apps') ? 'active' : '' }}" href="{{ route('settings.apps') }}">
                         <span class="nav-icon">
                              <i data-lucide="layout-grid"></i>
                         </span>
                         <span class="nav-text">Manage Apps</span>
                    </a>
               </li>
          </ul>
     </div>
</div>
