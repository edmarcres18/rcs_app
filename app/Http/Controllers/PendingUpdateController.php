<?php

namespace App\Http\Controllers;

use App\Models\PendingUpdate;
use App\Models\User;
use App\Services\UserActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PendingUpdateController extends Controller
{
    public function index()
    {
        $pendingUpdates = PendingUpdate::with(['user', 'requester'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);

        return view('admin.pending-updates.index', compact('pendingUpdates'));
    }

    public function approve(Request $request, PendingUpdate $pendingUpdate)
    {
        if ($pendingUpdate->status !== 'pending') {
            return redirect()->route('admin.pending-updates.index')->with('error', 'This request has already been processed.');
        }

        $user = $pendingUpdate->user;

        if ($pendingUpdate->type === 'update') {
            $user->update($pendingUpdate->data);
            UserActivityService::logUserUpdated(Auth::user(), $user, []); // Old data not available here easily
        } elseif ($pendingUpdate->type === 'delete') {
            $user->delete(); // Soft delete
            UserActivityService::logUserDeleted(Auth::user(), $user);
        }

        $pendingUpdate->update([
            'status' => 'approved',
            'approver_id' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.pending-updates.index')->with('success', 'Request approved successfully.');
    }

    public function reject(Request $request, PendingUpdate $pendingUpdate)
    {
        if ($pendingUpdate->status !== 'pending') {
            return redirect()->route('admin.pending-updates.index')->with('error', 'This request has already been processed.');
        }

        $pendingUpdate->update([
            'status' => 'rejected',
            'approver_id' => Auth::id(),
            'rejected_at' => now(),
        ]);

        return redirect()->route('admin.pending-updates.index')->with('success', 'Request rejected successfully.');
    }
}
