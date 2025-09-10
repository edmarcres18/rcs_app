<tr id="noResultsRow">
    <td colspan="8" class="text-center py-5">
        <div class="d-flex flex-column align-items-center">
            <i class="fas fa-users fa-4x mb-3 text-muted opacity-50"></i>
            <h5 class="text-muted">No users found</h5>
            @if($hasFilters)
                <p class="text-muted mb-3">Try adjusting your search criteria or filters</p>
                <button type="button" id="clearAllFilters" class="btn btn-outline-primary">
                    <i class="fas fa-redo me-2"></i>Clear All Filters
                </button>
            @else
                <p class="text-muted mb-3">Start by adding your first user</p>
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>Add New User
                </a>
            @endif
        </div>
    </td>
</tr>
