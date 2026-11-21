<aside
  :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
  class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 dark:border-gray-800 lg:static lg:translate-x-0"
>
<!-- SIDEBAR HEADER -->
<div
    :class="sidebarToggle ? 'justify-center' : 'justify-between'"
    class="flex items-center gap-2 pt-8 pb-7 sidebar-header"
>
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3" aria-label="{{ SystemHelper::appName() }} dashboard">
        @include('public.partials.logo', ['class' => 'h-12 w-12 shrink-0'])
        <span class="text-base font-semibold leading-tight text-gray-800" :class="sidebarToggle ? 'lg:hidden' : ''">{{ SystemHelper::appName() }}</span>
    </a>
</div>
<!-- SIDEBAR HEADER -->



  <div
    class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar"
  >
    <!-- Sidebar Menu -->
    <nav x-data="{selected: $persist('Dashboard')}">
      <!-- Menu Group -->
      <div>
        <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400">
          <span
            class="menu-group-title"
            :class="sidebarToggle ? 'lg:hidden' : ''"
          >
            MENU
          </span>

          <svg
            :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
            class="mx-auto fill-current menu-group-icon"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
              fill=""
            />
          </svg>
        </h3>

        <ul class="flex flex-col gap-4 mb-6">
          <!-- Menu Item Dashboard -->
          <li>
            <a
              href="{{ route('dashboard') }}"
              @click="selected = (selected === 'Dashboard' ? '':'Dashboard')"
              class="menu-item group"
              :class="(selected === 'Dashboard') && (page === 'dashboard') ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'Dashboard') && (page === 'dashboard') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z"
                  fill=""
                />
              </svg>

              <span
                class="menu-item-text"
                :class="sidebarToggle ? 'lg:hidden' : ''"
              >
                Dashboard
              </span>
            </a>
          </li>
          <!-- Menu Item Dashboard -->

          <!-- Menu Item Guests -->
          @if (auth()->user()->hasAnyRole(['admin', 'manager', 'receptionist']))
          <li>
            <a
              href="{{ route('guests.index') }}"
              @click="selected = (selected === 'Guests' ? '':'Guests')"
              class="menu-item group"
              :class="(selected === 'Guests') || (page === 'guests') ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'Guests') || (page === 'guests') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>

              <span
                class="menu-item-text"
                :class="sidebarToggle ? 'lg:hidden' : ''"
              >
                Guests
              </span>
            </a>
          </li>
          @endif
          <!-- Menu Item Guests -->

          <!-- Menu Item Stays -->
          @if (auth()->user()->hasAnyRole(['admin', 'manager', 'receptionist']))
          <li>
            <a
              href="#"
              @click.prevent="selected = (selected === 'Stays' ? '':'Stays')"
              class="menu-item group"
              :class="(selected === 'Stays') || (page === 'stays') ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'Stays') || (page === 'stays') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>

              <span
                class="menu-item-text"
                :class="sidebarToggle ? 'lg:hidden' : ''"
              >
                Stays
              </span>

              <svg
                class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                :class="[(selected === 'Stays') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
                width="20"
                height="20"
                viewBox="0 0 20 20"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585"
                  stroke=""
                  stroke-width="1.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </a>

            <!-- Dropdown Menu Start -->
            <div
              class="overflow-hidden transform translate"
              :class="(selected === 'Stays') ? 'block' :'hidden'"
            >
              <ul
                :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                class="flex flex-col gap-1 mt-2 menu-dropdown pl-9"
              >
                <li>
                  <a
                    href="{{ route('stays.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'stays.index' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    All Stays
                  </a>
                </li>
                <li>
                  <a
                    href="{{ route('booking-requests.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'booking-requests.index' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    Website Requests
                    @php($newRequests = \App\Models\BookingRequest::open()->count())
                    @if ($newRequests)
                      <span class="absolute right-3 rounded-full bg-warning-500 px-2 text-xs font-medium text-white">{{ $newRequests }}</span>
                    @endif
                  </a>
                </li>
                <li>
                  <a
                    href="{{ route('guest-messages.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'guest-messages.index' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    Guest Messages
                  </a>
                </li>
                <li>
                  <a
                    href="{{ route('companies.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'companies' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    Company accounts
                  </a>
                </li>
                <li>
                  <a
                    href="{{ route('night-audits.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'night-audits' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    Night audit
                  </a>
                </li>
                <li>
                  <a
                    href="{{ route('events.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'events.index' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    Conferences &amp; Events
                  </a>
                </li>
              </ul>
            </div>
            <!-- Dropdown Menu End -->
          </li>
          @endif
          <!-- Menu Item Stays -->

          <!-- Menu Item Rooms -->
          @if (auth()->user()->hasAnyRole(['admin', 'manager', 'receptionist', 'housekeeping']))
          <li>
            <a
              href="#"
              @click.prevent="selected = (selected === 'Rooms' ? '':'Rooms')"
              class="menu-item group"
              :class="(selected === 'Rooms') || (page === 'rooms' || page === 'room-types') ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'Rooms') || (page === 'rooms' || page === 'room-types') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>

              <span
                class="menu-item-text"
                :class="sidebarToggle ? 'lg:hidden' : ''"
              >
                Rooms
              </span>

              <svg
                class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                :class="[(selected === 'Rooms') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
                width="20"
                height="20"
                viewBox="0 0 20 20"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585"
                  stroke=""
                  stroke-width="1.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </a>

            <!-- Dropdown Menu Start -->
            <div
              class="overflow-hidden transform translate"
              :class="(selected === 'Rooms') ? 'block' :'hidden'"
            >
              <ul
                :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                class="flex flex-col gap-1 mt-2 menu-dropdown pl-9"
              >
                <li>
                  <a
                    href="{{ route('rooms.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'rooms.index' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    All Rooms
                  </a>
                </li>
                <li>
                  <a
                    href="{{ route('room-types.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'room-types.index' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    Room Types
                  </a>
                </li>
                <li>
                  <a
                    href="{{ route('facilities.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'facilities.index' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    Facilities
                  </a>
                </li>
              </ul>
            </div>
            <!-- Dropdown Menu End -->
          </li>
          @endif
          <!-- Menu Item Rooms -->

          <!-- Menu Item Tasks -->
          @if (auth()->user()->hasAnyRole(['admin', 'manager', 'receptionist', 'housekeeping', 'kitchen', 'procurement', 'hr', 'finance']))
          <li>
            <a
              href="{{ route('tasks.index') }}"
              @click="selected = (selected === 'Tasks' ? '':'Tasks')"
              class="menu-item group"
              :class="(selected === 'Tasks') || (page === 'tasks') ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'Tasks') || (page === 'tasks') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>

              <span
                class="menu-item-text"
                :class="sidebarToggle ? 'lg:hidden' : ''"
              >
                Tasks
              </span>
            </a>
          </li>
          @endif
          <!-- Menu Item Tasks -->

          <!-- Menu Item Finance -->
          @if (auth()->user()->hasAnyRole(['admin', 'manager', 'finance']))
          <li>
            <a
              href="#"
              @click.prevent="selected = (selected === 'Finance' ? '':'Finance')"
              class="menu-item group"
              :class="(selected === 'Finance') || (page === 'finance' || page === 'invoices' || page === 'payments' || page === 'expenses') ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'Finance') || (page === 'finance' || page === 'invoices' || page === 'payments' || page === 'expenses') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>

              <span
                class="menu-item-text"
                :class="sidebarToggle ? 'lg:hidden' : ''"
              >
                Finance
              </span>

              <svg
                class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                :class="[(selected === 'Finance') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
                width="20"
                height="20"
                viewBox="0 0 20 20"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585"
                  stroke=""
                  stroke-width="1.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </a>

            <!-- Dropdown Menu Start -->
            <div
              class="overflow-hidden transform translate"
              :class="(selected === 'Finance') ? 'block' :'hidden'"
            >
              <ul
                :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                class="flex flex-col gap-1 mt-2 menu-dropdown pl-9"
              >
                <li>
                  <a
                    href="{{ route('finance.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'payments.index' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    Overview
                  </a>
                </li>
                <li>
                  <a
                    href="{{ route('expenses.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'expenses.index' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    Expenses
                  </a>
                </li>
                <li>
                  <a
                    href="{{ route('invoices.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'invoices.index' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    Invoices
                  </a>
                </li>
                <li>
                  <a
                    href="{{ route('payments.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'payments.index' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    Payments
                  </a>
                </li>
                <li>
                  <a
                    href="{{ route('companies.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'companies' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    Company accounts
                  </a>
                </li>
                <li>
                  <a
                    href="{{ route('night-audits.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'night-audits' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    Night audit
                  </a>
                </li>
                
                <li>
                  <a
                    href="{{ route('reports.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'reports.index' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    Reports
                  </a>
                </li>
              </ul>
            </div>
            <!-- Dropdown Menu End -->
          </li>
          @endif
          <!-- Menu Item Finance -->

          <!-- Menu Item Inventory -->
          @if (auth()->user()->hasAnyRole(['admin', 'manager', 'procurement', 'kitchen', 'housekeeping']))
          <li>
            <a
              href="{{ route('inventory.index') }}"
              @click="selected = (selected === 'Inventory' ? '':'Inventory')"
              class="menu-item group"
              :class="(selected === 'Inventory') || (page === 'inventory') ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'Inventory') || (page === 'inventory') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>

              <span
                class="menu-item-text"
                :class="sidebarToggle ? 'lg:hidden' : ''"
              >
                Inventory
              </span>
            </a>
          </li>
          @endif
          <!-- Menu Item Inventory -->

          <!-- Menu Item Procurement -->
          @if (auth()->user()->hasAnyRole(['admin', 'manager', 'procurement', 'kitchen', 'finance']))
          <li>
            <a
              href="{{ route('purchase-orders.index') }}"
              @click="selected = (selected === 'Procurement' ? '':'Procurement')"
              class="menu-item group"
              :class="(selected === 'Procurement') || (page === 'purchase-orders' || page === 'suppliers') ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'Procurement') || (page === 'purchase-orders' || page === 'suppliers') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>

              <span
                class="menu-item-text"
                :class="sidebarToggle ? 'lg:hidden' : ''"
              >
                Procurement
              </span>
            </a>
          </li>
          @endif
          <!-- Menu Item Procurement -->

          <!-- Menu Item Kitchen -->
          @if (auth()->user()->hasAnyRole(['admin', 'manager', 'kitchen', 'receptionist']))
          <li>
            <a
              href="{{ route('kitchen.index') }}"
              @click="selected = (selected === 'Kitchen' ? '':'Kitchen')"
              class="menu-item group"
              :class="(selected === 'Kitchen') || (page === 'kitchen') ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'Kitchen') || (page === 'kitchen') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>

              <span
                class="menu-item-text"
                :class="sidebarToggle ? 'lg:hidden' : ''"
              >
                Restaurant &amp; Kitchen
              </span>
            </a>
          </li>
          @endif
          <!-- Menu Item Kitchen -->

          <!-- Menu Item HR -->
          @if (auth()->user()->hasAnyRole(['admin', 'manager', 'hr', 'finance']))
          <li>
            <a
              href="#"
              @click.prevent="selected = (selected === 'HR' ? '':'HR')"
              class="menu-item group"
              :class="(selected === 'HR') || (page === 'hr') ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'HR') || (page === 'hr') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>

              <span
                class="menu-item-text"
                :class="sidebarToggle ? 'lg:hidden' : ''"
              >
                HR
              </span>

              <svg
                class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                :class="[(selected === 'HR') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
                width="20"
                height="20"
                viewBox="0 0 20 20"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585"
                  stroke=""
                  stroke-width="1.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </a>

            <!-- Dropdown Menu Start -->
            <div
              class="overflow-hidden transform translate"
              :class="(selected === 'HR') ? 'block' :'hidden'"
            >
              <ul
                :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                class="flex flex-col gap-1 mt-2 menu-dropdown pl-9"
              >
                <li>
                  <a
                    href="{{ route('hr.staff.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'hr.staff.index' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    Staff
                  </a>
                </li>
                <li>
                  <a
                    href="{{ route('hr.rota.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'hr.rota.index' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    Rota
                  </a>
                </li>
                <li>
                  <a
                    href="{{ route('hr.payroll.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'hr.payroll.index' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    Payroll
                  </a>
                </li>
                <li>
                  <a
                    href="{{ route('hr.leave.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'hr.leave.index' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                  >
                    Leave
                  </a>
                </li>
              </ul>
            </div>
            <!-- Dropdown Menu End -->
          </li>
          @endif
          <!-- Menu Item HR -->

          
      <!-- Others Group -->
      <div>
        <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400">
          <span
            class="menu-group-title"
            :class="sidebarToggle ? 'lg:hidden' : ''"
          >
            SYSTEM
          </span>

          <svg
            :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
            class="mx-auto fill-current menu-group-icon"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
              fill=""
            />
          </svg>
        </h3>

        <ul class="flex flex-col gap-4 mb-6">
          <!-- Menu Item Reports -->
          @if (auth()->user()->hasAnyRole(['admin', 'manager', 'finance']))
          <li>
            <a
              href="{{ route('reports.index') }}"
              @click="selected = (selected === 'Reports' ? '':'Reports')"
              class="menu-item group"
              :class="(selected === 'Reports') || (page === 'reports') ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'Reports') || (page === 'reports') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>

              <span
                class="menu-item-text"
                :class="sidebarToggle ? 'lg:hidden' : ''"
              >
                Reports
              </span>
            </a>
          </li>
          @endif
          <!-- Menu Item Reports -->

          <!-- Menu Item Users -->
          @if (auth()->user()->hasAnyRole(['admin']))
          <li>
            <a
              href="{{ route('users.index') }}"
              @click="selected = (selected === 'Users' ? '':'Users')"
              class="menu-item group"
              :class="(selected === 'Users') || (page === 'users') ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'Users') || (page === 'users') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>

              <span
                class="menu-item-text"
                :class="sidebarToggle ? 'lg:hidden' : ''"
              >
                Users
              </span>
            </a>
          </li>
          @endif
          <!-- Menu Item Users -->

          <!-- Menu Item System -->
          @if (auth()->user()->hasAnyRole(['admin']))
          <li>
            <a
              href="{{ route('system.index') }}"
              @click="selected = (selected === 'System' ? '':'System')"
              class="menu-item group"
              :class="(selected === 'System') || (page === 'system') ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'System') || (page === 'system') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>

              <span
                class="menu-item-text"
                :class="sidebarToggle ? 'lg:hidden' : ''"
              >
                System
              </span>
            </a>
          </li>
          @endif
          <!-- Menu Item System -->


          <!-- Menu Item Calendar -->
          <li>
            <a
              href="{{ route('calendar') }}"
              @click="selected = (selected === 'Calendar' ? '':'Calendar')"
              class="menu-item group"
              :class="(selected === 'Calendar') && (page === 'calendar') ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'Calendar') && (page === 'calendar') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M8 2C8.41421 2 8.75 2.33579 8.75 2.75V3.75H15.25V2.75C15.25 2.33579 15.5858 2 16 2C16.4142 2 16.75 2.33579 16.75 2.75V3.75H18.5C19.7426 3.75 20.75 4.75736 20.75 6V9V19C20.75 20.2426 19.7426 21.25 18.5 21.25H5.5C4.25736 21.25 3.25 20.2426 3.25 19V9V6C3.25 4.75736 4.25736 3.75 5.5 3.75H7.25V2.75C7.25 2.33579 7.58579 2 8 2ZM8 5.25H5.5C5.08579 5.25 4.75 5.58579 4.75 6V8.25H19.25V6C19.25 5.58579 18.9142 5.25 18.5 5.25H16H8ZM19.25 9.75H4.75V19C4.75 19.4142 5.08579 19.75 5.5 19.75H18.5C18.9142 19.75 19.25 19.4142 19.25 19V9.75Z"
                  fill=""
                />
              </svg>

              <span
                class="menu-item-text"
                :class="sidebarToggle ? 'lg:hidden' : ''"
              >
                Calendar
              </span>
            </a>
          </li>
          <!-- Menu Item Calendar -->
        </ul>
      </div>
    </nav>
    <!-- Sidebar Menu -->

  </div>
</aside>
