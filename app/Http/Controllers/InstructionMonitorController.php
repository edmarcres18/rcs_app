<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Instruction;
use App\Models\InstructionActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstructionMonitorController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || !in_array(Auth::user()->roles, [UserRole::ADMIN, UserRole::SYSTEM_ADMIN])) {
                return response()->view('errors.403', ['message' => 'Only Administrators can access this page.'], 403);
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of all instructions.
     */
    public function index(Request $request)
    {
        $instructions = Instruction::with(['sender', 'recipients'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->when($request->sender_id, function ($query, $senderId) {
                $query->where('sender_id', $senderId);
            })
            ->when($request->from_date, function ($query, $fromDate) {
                $query->whereDate('created_at', '>=', $fromDate);
            })
            ->when($request->to_date, function ($query, $toDate) {
                $query->whereDate('created_at', '<=', $toDate);
            })
            ->latest()
            ->paginate(15);

        // Get statistics
        $stats = [
            'total' => Instruction::count(),
            'read' => DB::table('instruction_user')->where('is_read', true)->count(),
            'unread' => DB::table('instruction_user')->where('is_read', false)->count(),
            'forwarded' => DB::table('instruction_user')->whereNotNull('forwarded_by_id')->count(),
        ];

        return view('instructions.monitor.index', compact('instructions', 'stats'));
    }

    /**
     * Display activity logs for a specific instruction.
     */
    public function showActivityLogs(Request $request, Instruction $instruction)
    {
        $activities = InstructionActivity::with(['user', 'targetUser'])
            ->where('instruction_id', $instruction->id)
            ->when($request->action, function ($query, $action) {
                $query->where('action', $action);
            })
            ->when($request->user_id, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->latest()
            ->paginate(20);

        return view('instructions.monitor.activities', compact('instruction', 'activities'));
    }

    /**
     * Display comprehensive activity logs for all instructions.
     */
    public function allActivityLogs(Request $request)
    {
        $activities = InstructionActivity::with(['instruction', 'user', 'targetUser'])
            ->when($request->action, function ($query, $action) {
                $query->where('action', $action);
            })
            ->when($request->user_id, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($request->instruction_id, function ($query, $instructionId) {
                $query->where('instruction_id', $instructionId);
            })
            ->when($request->from_date, function ($query, $fromDate) {
                $query->whereDate('created_at', '>=', $fromDate);
            })
            ->when($request->to_date, function ($query, $toDate) {
                $query->whereDate('created_at', '<=', $toDate);
            })
            ->latest()
            ->paginate(20);

        return view('instructions.monitor.all-activities', compact('activities'));
    }

    /**
     * Display compliance statistics and reports.
     */
    public function reports()
    {
        // User compliance stats
        $userStats = DB::table('instruction_user')
            ->select('users.id', 'users.first_name', 'users.last_name',
                DB::raw('COUNT(*) as total_assigned'),
                DB::raw('SUM(CASE WHEN instruction_user.is_read = 1 THEN 1 ELSE 0 END) as read_count'),
                DB::raw('SUM(CASE WHEN instruction_user.is_read = 0 THEN 1 ELSE 0 END) as unread_count')
            )
            ->join('users', 'users.id', '=', 'instruction_user.user_id')
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->get();

        // Get monthly stats
        $monthlyStats = DB::table('instructions')
            ->select(DB::raw('YEAR(created_at) as year'), DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return view('instructions.monitor.reports', compact('userStats', 'monthlyStats'));
    }
}
