<div class="sidebar-content">
    <!-- Dashboard - Available to all roles -->
    <div class="sidebar-section">
        <div class="sidebar-section-title">Dashboard</div>
        <ul class="sidebar-nav">
            <li class="sidebar-nav-item">
                <a href="{{ url('/home') }}" class="sidebar-nav-link {{ Request::is('home') ? 'active' : '' }}" data-title="Home">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="{{ route('wrapped.index') }}" class="sidebar-nav-link {{ Request::routeIs('wrapped.index') ? 'active' : '' }}" data-title="RCS Wrapped">
                    <i class="fas fa-magic"></i>
                    <span>RCS Wrapped</span>
                </a>
            </li>
        </ul>
    </div>

    @if(Auth::check())
        @php
            $userRole = Auth::user()->roles;
        @endphp

        <!-- EMPLOYEE Role Menu -->
        @if($userRole === \App\Enums\UserRole::EMPLOYEE)
            <div class="sidebar-section">
                <div class="sidebar-section-title">Instructions</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('instructions.index') }}" class="sidebar-nav-link {{ Request::routeIs('instructions.*') && !Request::routeIs('instructions.monitor*') ? 'active' : '' }}" data-title="Received Instructions">
                            <i class="fas fa-inbox"></i>
                            <span>Received Instructions</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Task Management</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('task-priorities.index') }}" class="sidebar-nav-link {{ Request::routeIs('task-priorities.*') ? 'active' : '' }}" data-title="Task Priorities">
                            <i class="fas fa-flag"></i>
                            <span>Task Priorities</span>
                        </a>
                    </li>
                </ul>
            </div>

        <!-- SUPERVISOR Role Menu -->
        @elseif($userRole === \App\Enums\UserRole::SUPERVISOR)
            <div class="sidebar-section">
                <div class="sidebar-section-title">Instructions</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('instructions.index') }}" class="sidebar-nav-link {{ Request::routeIs('instructions.*') && !Request::routeIs('instructions.monitor*') ? 'active' : '' }}" data-title="Received Instructions">
                            <i class="fas fa-inbox"></i>
                            <span>Received Instructions</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Task Management</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('task-priorities.index') }}" class="sidebar-nav-link {{ Request::routeIs('task-priorities.*') ? 'active' : '' }}" data-title="Task Priorities">
                            <i class="fas fa-flag"></i>
                            <span>Task Priorities</span>
                        </a>
                    </li>
                </ul>
            </div>

        <!-- ADMIN Role Menu -->
        @elseif($userRole === \App\Enums\UserRole::ADMIN)
            <div class="sidebar-section">
                <div class="sidebar-section-title">Instructions</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('instructions.index') }}" class="sidebar-nav-link {{ Request::routeIs('instructions.*') && !Request::routeIs('instructions.monitor*') ? 'active' : '' }}" data-title="All Instructions">
                            <i class="fas fa-clipboard-list"></i>
                            <span>All Instructions</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('instructions.monitor') }}" class="sidebar-nav-link {{ Request::routeIs('instructions.monitor*') ? 'active' : '' }}" data-title="Instruction Monitoring">
                            <i class="fas fa-eye"></i>
                            <span>Instruction Monitoring</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">User Management</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('users.index') }}" class="sidebar-nav-link {{ Request::routeIs('users.index') ? 'active' : '' }}" data-title="Manage Users">
                            <i class="fas fa-users-cog"></i>
                            <span>Manage Users</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('users.all-activities') }}" class="sidebar-nav-link {{ Request::routeIs('users.all-activities') ? 'active' : '' }}" data-title="User Activity Logs">
                            <i class="fas fa-history"></i>
                            <span>User Activity Logs</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- <div class="sidebar-section">
                <div class="sidebar-section-title">Reports</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="#" class="sidebar-nav-link" data-title="Instruction Reports">
                            <i class="fas fa-chart-bar"></i>
                            <span>Instruction Reports</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="#" class="sidebar-nav-link" data-title="Notifications">
                            <i class="fas fa-bell"></i>
                            <span>Notifications</span>
                            <span class="badge bg-danger rounded-pill ms-auto">2</span>
                        </a>
                    </li>
                </ul>
            </div> --}}

        <!-- SYSTEM_ADMIN Role Menu -->
        @elseif($userRole === \App\Enums\UserRole::SYSTEM_ADMIN)
            <div class="sidebar-section">
                <div class="sidebar-section-title">System</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('instructions.monitor') }}" class="sidebar-nav-link {{ Request::routeIs('instructions.monitor*') ? 'active' : '' }}" data-title="System Logs">
                            <i class="fas fa-clipboard-list"></i>
                            <span>System Logs</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.ratings.index') }}" class="sidebar-nav-link {{ Request::routeIs('admin.ratings.index') ? 'active' : '' }}" data-title="Ratings Monitor">
                            <i class="fas fa-star"></i>
                            <span>Ratings Monitor</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">User Management</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('users.index') }}" class="sidebar-nav-link {{ Request::routeIs('users.*') && !Request::routeIs('users.all-activities') ? 'active' : '' }}" data-title="Manage Users">
                            <i class="fas fa-users"></i>
                            <span>Manage Users</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('users.all-activities') }}" class="sidebar-nav-link {{ Request::is('activities') ? 'active' : '' }}" data-title="All User Activity">
                            <i class="fas fa-history"></i>
                            <span>All User Activity</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.pending-updates.index') }}" class="sidebar-nav-link {{ Request::routeIs('admin.pending-updates.index') ? 'active' : '' }}" data-title="Pending Approvals">
                            <i class="fas fa-tasks"></i>
                            <span>Pending Approvals</span>
                            @if($pendingUpdatesCount > 0)
                                <span class="badge bg-warning rounded-pill ms-auto">{{ $pendingUpdatesCount }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Settings</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.system-settings.index') }}" class="sidebar-nav-link {{ Request::routeIs('admin.system-settings.index') || Request::routeIs('admin.system-settings.mail') ? 'active' : '' }}" data-title="System Settings">
                            <i class="fas fa-cogs"></i>
                            <span>System Settings</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('database.backups') }}" class="sidebar-nav-link {{ Request::routeIs('database.*') ? 'active' : '' }}" data-title="Database Backups">
                            <i class="fas fa-database"></i>
                            <span>Database Backups</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.system-notifications.index') }}" class="sidebar-nav-link {{ Request::routeIs('admin.system-notifications.*') ? 'active' : '' }}" data-title="System Notifications">
                            <i class="fas fa-bell"></i>
                            <span>System Notifications</span>
                        </a>
                    </li>
                </ul>
            </div>
        @endif

        <!-- User Profile Section - Available to all authenticated users -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">User</div>
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item">
                    <a href="{{ route('profile.show') }}" class="sidebar-nav-link {{ Request::routeIs('profile.*') ? 'active' : '' }}" data-title="My Profile">
                        <i class="fas fa-user-circle"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();"
                       class="sidebar-nav-link" data-title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                    <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    @endif

    <!-- Collapse Button for Mobile -->
    <div class="sidebar-section">
        <ul class="sidebar-nav">
            <li class="sidebar-nav-item">
                <a href="#" class="sidebar-nav-link" id="collapse-btn" data-title="Collapse">
                    <i class="fas fa-chevron-left"></i>
                    <span>Collapse</span>
                </a>
            </li>
        </ul>
    </div>
</div>
