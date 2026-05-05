<div class="main-nav">
     <div class="d-flex justify-content-between main-logo-box">
          <!-- Sidebar Logo -->
          <div class="logo-box">
               <a href="{{ route('second', ['dashboard', 'index'])}}" class="logo-dark">
                    <img src="/images/logo-sm.png" class="logo-sm" alt="logo sm">
                    <img src="/images/logo-dark.png" class="logo-lg" alt="logo dark">
               </a>

               <a href="index.html" class="logo-light">
                    <img src="/images/logo-sm.png" class="logo-sm" alt="logo sm">
                    <img src="/images/logo-white.png" class="logo-lg" alt="logo light">
               </a>
          </div>
          <!-- Menu Toggle Button -->
          <button type="button" class="btn btn-link d-flex button-sm-hover button-toggle-menu" aria-label="Show Full Sidebar">
               <i data-lucide="menu" class="button-sm-hover-icon"></i>
          </button>
     </div>

     <div class="h-100" data-simplebar>

          <ul class="navbar-nav" id="navbar-nav">

               <li class="menu-item">
                    <a class="menu-link" href="{{ route('second', ['dashboard', 'index'])}}">
                         <span class="nav-icon">
                              <i data-lucide="house"></i>
                         </span>
                         <span class="nav-text"> Dashboard </span>
                         <span class="badge bg-success badge-pill text-end">9+</span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->is('customers*') ? 'active' : '' }}" href="{{ route('any', 'customers')}}">
                         <span class="nav-icon">
                              <i data-lucide="book-user"></i>
                         </span>
                         <span class="nav-text"> Customers </span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link" href="{{ route('any', 'todo')}}">
                         <span class="nav-icon">
                              <i data-lucide="clipboard-check"></i>
                         </span>
                         <span class="nav-text"> Todo </span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link" href="{{ route('any', 'manage-apps')}}">
                         <span class="nav-icon">
                              <i data-lucide="layout-grid"></i>
                         </span>
                         <span class="nav-text"> Manage Apps </span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->is('invoice/*') || request()->is('invoices*') ? 'active' : '' }}" href="{{ route('second', ['invoice', 'invoices'])}}">
                         <span class="nav-icon">
                              <i data-lucide="receipt-text"></i>
                         </span>
                         <span class="nav-text"> Invoices </span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->routeIs('quotes.*') ? 'active' : '' }}" href="{{ route('quotes.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="message-square-quote"></i>
                         </span>
                         <span class="nav-text">Quotes</span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->routeIs('messages.*') ? 'active' : '' }}" href="{{ route('messages.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="mail"></i>
                         </span>
                         <span class="nav-text"> Messages </span>
                         @php
                              $unreadCount = \App\Models\Message::where('status', 'unread')->count();
                         @endphp
                         @if($unreadCount > 0)
                              <span class="badge bg-danger badge-pill text-end">{{ $unreadCount }}</span>
                         @endif
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link {{ request()->is('reports/*') ? 'active' : '' }}" href="#sidebarReports" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->is('reports/*') ? 'true' : 'false' }}" aria-controls="sidebarReports">
                         <span class="nav-icon">
                              <i data-lucide="chart-column"></i>
                         </span>
                         <span class="nav-text"> Reports </span>
                         <span class="menu-arrow"><x-icons.chevron-down /></span>
                    </a>
                    <div class="collapse {{ request()->is('reports/*') ? 'show' : '' }}" id="sidebarReports">
                         <ul class="sub-menu-nav">
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link {{ request()->is('reports/customer-lifecycle') ? 'active' : '' }}" href="{{ route('second', ['reports', 'customer-lifecycle']) }}">Customer Report</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link {{ request()->is('reports/financial-reports') ? 'active' : '' }}" href="{{ route('second', ['reports', 'financial-reports']) }}">Financial Report</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link {{ request()->is('reports/email-delivery') ? 'active' : '' }}" href="{{ route('second', ['reports', 'email-delivery']) }}">Email Delivery Report</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link {{ request()->is('reports/visitor-reports') ? 'active' : '' }}" href="{{ route('second', ['reports', 'visitor-reports']) }}">Visitor Insights</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="menu-item">
                    <a class="menu-link" href="#sidebarPages" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarPages">
                         <span class="nav-icon">
                              <i data-lucide="notebook-text"></i>
                         </span>
                         <span class="nav-text"> Pages </span>
                         <span class="menu-arrow"><x-icons.chevron-down /></span>
                    </a>
                    <div class="collapse" id="sidebarPages">
                         <ul class="sub-menu-nav">
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['pages', 'starter'])}}">Welcome</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['pages', 'profile'])}}">Profile</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['pages', 'settings'])}}">Settings</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['pages', 'faqs'])}}">FAQs</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['pages', 'gallery'])}}">Gallery</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['pages', 'comingsoon'])}}">Coming Soon</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['pages', 'timeline'])}}">Timeline</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['pages', 'pricing'])}}">Pricing</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['pages', 'maintenance'])}}">Maintenance</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['pages', '404'])}}">404 Error</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="menu-item">
                    <a class="menu-link" href="#sidebarAuthentication" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAuthentication">
                         <span class="nav-icon">
                              <i data-lucide="circle-user"></i>
                         </span>
                         <span class="nav-text"> Authentication </span>
                         <span class="menu-arrow"><x-icons.chevron-down /></span>
                    </a>
                    <div class="collapse" id="sidebarAuthentication">
                         <ul class="sub-menu-nav">
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('login') }}">Sign In</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('register') }}">Sign Up</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('password.request') }}">Reset Password</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['auth', 'lock-screen'])}}">Lock Screen</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="menu-title">Components</li>

               <li class="menu-item">
                    <a class="menu-link" href="#sidebarBaseUI" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarBaseUI">
                         <span class="nav-icon">
                              <i data-lucide="flame"></i>
                         </span>
                         <span class="nav-text"> Base UI </span>
                         <span class="menu-arrow"><x-icons.chevron-down /></span>
                    </a>
                    <div class="collapse" id="sidebarBaseUI">
                         <ul class="sub-menu-nav">
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'accordion'])}}">Accordion</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'alerts'])}}">Alerts</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'avatar'])}}">Avatar</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'badge'])}}">Badge</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'breadcrumb'])}}">Breadcrumb</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'buttons'])}}">Buttons</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'card'])}}">Card</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'carousel'])}}">Carousel</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'collapse'])}}">Collapse</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'dropdown'])}}">Dropdown</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'list-group'])}}">List Group</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'modal'])}}">Modal</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'tabs'])}}">Tabs</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'offcanvas'])}}">Offcanvas</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'pagination'])}}">Pagination</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'placeholders'])}}">Placeholders</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'popovers'])}}">Popovers</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'progress'])}}">Progress</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'scrollspy'])}}">Scrollspy</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'spinners'])}}">Spinners</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'toasts'])}}">Toasts</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['ui', 'tooltips'])}}">Tooltips</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="menu-item">
                    <a class="menu-link" href="#sidebarExtendedUI" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarExtendedUI">
                         <span class="nav-icon">
                              <i data-lucide="wand"></i>
                         </span>
                         <span class="nav-text"> Advanced UI </span>
                         <span class="menu-arrow"><x-icons.chevron-down /></span>
                    </a>
                    <div class="collapse" id="sidebarExtendedUI">
                         <ul class="sub-menu-nav">
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['extended', 'ratings'])}}">Ratings</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['extended', 'sweetalert'])}}">Sweet Alert</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['extended', 'scrollbar'])}}">Scrollbar</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['extended', 'toastify'])}}">Toastify</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="menu-item">
                    <a class="menu-link" href="#sidebarCharts" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCharts">
                         <span class="nav-icon">
                              <i data-lucide="bar-chart-3"></i>
                         </span>
                         <span class="nav-text"> Charts </span>
                         <span class="menu-arrow"><x-icons.chevron-down /></span>
                    </a>
                    <div class="collapse" id="sidebarCharts">
                         <ul class="sub-menu-nav">
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['charts', 'area'])}}">Area</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['charts', 'bar'])}}">Bar</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['charts', 'bubble'])}}">Bubble</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['charts', 'candlestick'])}}">Candlestick</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['charts', 'column'])}}">Column</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['charts', 'heatmap'])}}">Heatmap</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['charts', 'line'])}}">Line</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['charts', 'mixed'])}}">Mixed</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['charts', 'timeline'])}}">Timeline</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['charts', 'boxplot'])}}">Boxplot</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['charts', 'treemap'])}}">Treemap</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['charts', 'pie'])}}">Pie</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['charts', 'radar'])}}">Radar</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['charts', 'radialbar'])}}">RadialBar</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['charts', 'scatter'])}}">Scatter</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['charts', 'polar-area'])}}">Polar Area</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="menu-item">
                    <a class="menu-link" href="#sidebarForms" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarForms">
                         <span class="nav-icon">
                              <i data-lucide="file-text"></i>
                         </span>
                         <span class="nav-text"> Forms </span>
                         <span class="menu-arrow"><x-icons.chevron-down /></span>
                    </a>
                    <div class="collapse" id="sidebarForms">
                         <ul class="sub-menu-nav">
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['forms', 'basic'])}}">Basic Elements</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['forms', 'checkbox-radio'])}}">Checkbox &amp; Radio</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['forms', 'choices'])}}">Choice Select</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['forms', 'clipboard'])}}">Clipboard</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['forms', 'flatepicker'])}}">Flatepicker</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['forms', 'validation'])}}">Validation</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['forms', 'fileuploads'])}}">File Upload</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['forms', 'editors'])}}">Editors</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['forms', 'input-mask'])}}">Input Mask</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['forms', 'range-slider'])}}">Slider</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="menu-item">
                    <a class="menu-link" href="#sidebarTables" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarTables">
                         <span class="nav-icon">
                              <i data-lucide="table"></i>
                         </span>
                         <span class="nav-text"> Tables </span>
                         <span class="menu-arrow"><x-icons.chevron-down /></span>
                    </a>
                    <div class="collapse" id="sidebarTables">
                         <ul class="sub-menu-nav">
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['tables', 'basic'])}}">Basic Tables</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['tables', 'gridjs'])}}">Grid Js</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="menu-item">
                    <a class="menu-link" href="#sidebarIcons" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarIcons">
                         <span class="nav-icon">
                              <i data-lucide="image"></i>
                         </span>
                         <span class="nav-text"> Icons </span>
                         <span class="menu-arrow"><x-icons.chevron-down /></span>
                    </a>
                    <div class="collapse" id="sidebarIcons">
                         <ul class="sub-menu-nav">
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['icons', 'lucid'])}}">Lucide</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="menu-item">
                    <a class="menu-link" href="#sidebarMaps" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarMaps">
                         <span class="nav-icon">
                              <i data-lucide="map-pin"></i>
                         </span>
                         <span class="nav-text"> Maps </span>
                         <span class="menu-arrow"><x-icons.chevron-down /></span>
                    </a>
                    <div class="collapse" id="sidebarMaps">
                         <ul class="sub-menu-nav">
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['maps', 'google'])}}">Google Maps</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('second', ['maps', 'vector'])}}">Vector Maps</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="menu-item">
                    <a class="menu-link" href="javascript:void(0);">
                         <span class="nav-icon">
                              <i data-lucide="volleyball"></i>
                         </span>
                         <span class="nav-text">Badge Menu</span>
                         <span class="badge bg-primary badge-pill text-end">1</span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link" href="#sidebarMultiLevelDemo" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarMultiLevelDemo">
                         <span class="nav-icon">
                              <i data-lucide="share-2"></i>
                         </span>
                         <span class="nav-text"> Menu Item </span>
                         <span class="menu-arrow"><x-icons.chevron-down /></span>
                    </a>
                    <div class="collapse" id="sidebarMultiLevelDemo">
                         <ul class="sub-menu-nav">
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="javascript:void(0);">Menu Item 1</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="#sidebarItemDemoSubItem" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarItemDemoSubItem">
                                        <span> Menu Item 2 </span>
                                        <span class="menu-arrow"><x-icons.chevron-down /></span>
                                   </a>
                                   <div class="collapse" id="sidebarItemDemoSubItem">
                                        <ul class="sub-menu-nav">
                                             <li class="sub-menu-item">
                                                  <a class="sub-menu-link" href="javascript:void(0);">Menu Sub item</a>
                                             </li>
                                        </ul>
                                   </div>
                              </li>
                         </ul>
                    </div>
               </li>
          </ul>
     </div>
</div>
