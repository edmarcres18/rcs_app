<div class="list-group">
    <a href="{{ route('admin.system-settings.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.system-settings.index') ? 'active' : '' }}">
        <i class="fas fa-cog me-2"></i> General Settings
    </a>
    <a href="{{ route('admin.system-settings.mail') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.system-settings.mail') ? 'active' : '' }}">
        <i class="fas fa-envelope me-2"></i> Mail Settings
    </a>
</div>
