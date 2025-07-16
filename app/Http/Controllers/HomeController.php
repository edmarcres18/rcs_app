<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Instruction;
use App\Models\InstructionActivity;
use App\Models\InstructionReply;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        $data = [];

        switch ($user->roles) {
            case UserRole::EMPLOYEE:
                $data = $this->getEmployeeDashboardData($user);
                break;
            case UserRole::SUPERVISOR:
                $data = $this->getSupervisorDashboardData($user);
                break;
            case UserRole::ADMIN:
                $data = $this->getAdminDashboardData();
                break;
            case UserRole::SYSTEM_ADMIN:
                $data = $this->getSystemAdminDashboardData();
                break;
        }

        return view('home', $data);
    }

    /**
     * Get dashboard data for EMPLOYEE role.
     *
     * @param \App\Models\User $user
     * @return array
     */
    private function getEmployeeDashboardData(User $user)
    {
        // Get received instructions with statistics
        $receivedInstructions = $user->receivedInstructions;

        $totalInstructions = $receivedInstructions->count();
        $pendingInstructions = $receivedInstructions->filter(function($instruction) {
            return !$instruction->activities()->where('action', 'completed')->exists();
        })->count();
        $completedInstructions = $totalInstructions - $pendingInstructions;

        // Forwarded instructions (instructions that were forwarded to this user)
        $forwardedInstructions = $user->receivedInstructions()
            ->whereNotNull('forwarded_by_id')
            ->count();

        // Total feedback (replies received on instructions)
        $feedbackCount = InstructionReply::whereHas('instruction', function($query) use ($user) {
            $query->where('sender_id', $user->id);
        })->count();

        // Get upcoming deadlines
        $upcomingDeadlines = Instruction::join('instruction_user', 'instructions.id', '=', 'instruction_user.instruction_id')
            ->where('instruction_user.user_id', $user->id)
            ->whereDate('instructions.created_at', '>=', now()->subDays(30))
            ->whereNotIn('instructions.id', function($query) {
                $query->select('instruction_id')
                    ->from('instruction_activities')
                    ->where('action', 'completed');
            })
            ->count();

        // Get trend data (last 7 entries)
        $trendData = $this->getInstructionTrendData($user);

        // Status distribution data
        $statusDistribution = $this->getStatusDistributionData($user);

        // Get recent assigned instructions (limit to 5)
        $recentInstructions = $user->receivedInstructions()
            ->with(['sender'])
            ->latest('instruction_user.created_at')
            ->take(5)
            ->get();

        // Get recent feedbacks (replies)
        $recentFeedbacks = InstructionReply::whereHas('instruction', function($query) use ($user) {
            $query->where(function($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhereHas('recipients', function($r) use ($user) {
                      $r->where('users.id', $user->id);
                  });
            });
        })
        ->with(['user', 'instruction'])
        ->latest()
        ->take(4)
        ->get();

        // Get forwarded instructions
        $forwardedInstructionList = $user->receivedInstructions()
            ->whereNotNull('forwarded_by_id')
            ->with(['sender', 'recipients' => function($query) {
                $query->whereNotNull('instruction_user.forwarded_by_id');
            }])
            ->latest('instruction_user.created_at')
            ->take(4)
            ->get();

        return [
            'totalInstructions' => $totalInstructions,
            'pendingInstructions' => $pendingInstructions,
            'completedInstructions' => $completedInstructions,
            'forwardedInstructions' => $forwardedInstructions,
            'feedbackCount' => $feedbackCount,
            'upcomingDeadlines' => $upcomingDeadlines,
            'trendData' => $trendData,
            'statusDistribution' => $statusDistribution,
            'recentInstructions' => $recentInstructions,
            'recentFeedbacks' => $recentFeedbacks,
            'forwardedInstructionList' => $forwardedInstructionList,
        ];
    }

    /**
     * Get dashboard data for SUPERVISOR role.
     *
     * @param \App\Models\User $user
     * @return array
     */
    private function getSupervisorDashboardData(User $user)
    {
        // Get both sent and received instructions
        $sentInstructions = $user->sentInstructions;
        $receivedInstructions = $user->receivedInstructions;

        $totalInstructions = $sentInstructions->count() + $receivedInstructions->count();

        // Pending instructions (both sent and received without completion)
        $pendingSent = $sentInstructions->filter(function($instruction) {
            return !$instruction->activities()->where('action', 'completed')->exists();
        })->count();

        $pendingReceived = $receivedInstructions->filter(function($instruction) {
            return !$instruction->activities()->where('action', 'completed')->exists();
        })->count();

        $pendingInstructions = $pendingSent + $pendingReceived;
        $completedInstructions = $totalInstructions - $pendingInstructions;

        // Forwarded instructions count
        $forwardedInstructions = Instruction::whereHas('recipients', function($query) {
            $query->whereNotNull('instruction_user.forwarded_by_id');
        })
        ->where(function($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->orWhereHas('recipients', function($q) use ($user) {
                      $q->where('users.id', $user->id);
                  });
        })
        ->count();

        // Total feedback (replies)
        $feedbackCount = InstructionReply::whereHas('instruction', function($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->orWhereHas('recipients', function($q) use ($user) {
                      $q->where('users.id', $user->id);
                  });
        })->count();

        // Get upcoming deadlines
        $upcomingDeadlines = Instruction::where(function($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->orWhereHas('recipients', function($q) use ($user) {
                      $q->where('users.id', $user->id);
                  });
        })
        ->whereDate('created_at', '>=', now()->subDays(30))
        ->whereNotIn('id', function($query) {
            $query->select('instruction_id')
                ->from('instruction_activities')
                ->where('action', 'completed');
        })
        ->count();

        // Get trend data
        $trendData = $this->getInstructionTrendData($user, true);

        // Status distribution data
        $statusDistribution = $this->getStatusDistributionData($user, true);

        // Get recent assigned instructions
        $recentInstructions = Instruction::where(function($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->orWhereHas('recipients', function($q) use ($user) {
                      $q->where('users.id', $user->id);
                  });
        })
        ->with(['sender', 'recipients'])
        ->latest()
        ->take(5)
        ->get();

        // Get recent feedbacks
        $recentFeedbacks = InstructionReply::whereHas('instruction', function($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->orWhereHas('recipients', function($q) use ($user) {
                      $q->where('users.id', $user->id);
                  });
        })
        ->with(['user', 'instruction'])
        ->latest()
        ->take(4)
        ->get();

        // Get forwarded instructions
        $forwardedInstructionList = Instruction::whereHas('recipients', function($query) {
            $query->whereNotNull('instruction_user.forwarded_by_id');
        })
        ->where(function($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->orWhereHas('recipients', function($q) use ($user) {
                      $q->where('users.id', $user->id);
                  });
        })
        ->with(['sender', 'recipients'])
        ->latest()
        ->take(4)
        ->get();

        return [
            'totalInstructions' => $totalInstructions,
            'pendingInstructions' => $pendingInstructions,
            'completedInstructions' => $completedInstructions,
            'forwardedInstructions' => $forwardedInstructions,
            'feedbackCount' => $feedbackCount,
            'upcomingDeadlines' => $upcomingDeadlines,
            'trendData' => $trendData,
            'statusDistribution' => $statusDistribution,
            'recentInstructions' => $recentInstructions,
            'recentFeedbacks' => $recentFeedbacks,
            'forwardedInstructionList' => $forwardedInstructionList,
            'isSupervisor' => true,
        ];
    }

    /**
     * Get dashboard data for ADMIN role.
     *
     * @return array
     */
    private function getAdminDashboardData()
    {
        // For ADMIN, show statistics for all instructions
        $totalInstructions = Instruction::count();

        // Pending instructions (without completion)
        $pendingInstructions = Instruction::whereNotIn('id', function($query) {
            $query->select('instruction_id')
                ->from('instruction_activities')
                ->where('action', 'completed');
        })->count();

        $completedInstructions = $totalInstructions - $pendingInstructions;

        // Forwarded instructions
        $forwardedInstructions = Instruction::whereHas('recipients', function($query) {
            $query->whereNotNull('instruction_user.forwarded_by_id');
        })->count();

        // Total feedback (replies)
        $feedbackCount = InstructionReply::count();

        // Get upcoming deadlines
        $upcomingDeadlines = Instruction::whereDate('created_at', '>=', now()->subDays(30))
            ->whereNotIn('id', function($query) {
                $query->select('instruction_id')
                    ->from('instruction_activities')
                    ->where('action', 'completed');
            })
            ->count();

        // Get trend data
        $trendData = $this->getAdminInstructionTrendData();

        // Status distribution data
        $statusDistribution = $this->getAdminStatusDistributionData();

        // Top users by instruction count
        $topUsers = User::withCount(['sentInstructions', 'receivedInstructions'])
            ->orderByDesc(DB::raw('sent_instructions_count + received_instructions_count'))
            ->take(5)
            ->get();

        // Get recent instructions
        $recentInstructions = Instruction::with(['sender', 'recipients'])
            ->latest()
            ->take(5)
            ->get();

        // Get recent feedbacks
        $recentFeedbacks = InstructionReply::with(['user', 'instruction'])
            ->latest()
            ->take(4)
            ->get();

        // Get recent activities
        $recentActivities = InstructionActivity::with(['user', 'instruction'])
            ->latest()
            ->take(8)
            ->get();

        // Get forwarded instructions
        $forwardedInstructionList = Instruction::whereHas('recipients', function($query) {
            $query->whereNotNull('instruction_user.forwarded_by_id');
        })
        ->with(['sender', 'recipients'])
        ->latest()
        ->take(4)
        ->get();

        return [
            'totalInstructions' => $totalInstructions,
            'pendingInstructions' => $pendingInstructions,
            'completedInstructions' => $completedInstructions,
            'forwardedInstructions' => $forwardedInstructions,
            'feedbackCount' => $feedbackCount,
            'upcomingDeadlines' => $upcomingDeadlines,
            'trendData' => $trendData,
            'statusDistribution' => $statusDistribution,
            'recentInstructions' => $recentInstructions,
            'recentFeedbacks' => $recentFeedbacks,
            'recentActivities' => $recentActivities,
            'topUsers' => $topUsers,
            'forwardedInstructionList' => $forwardedInstructionList,
            'isAdmin' => true,
        ];
    }

    /**
     * Get dashboard data for SYSTEM_ADMIN role.
     *
     * @return array
     */
    private function getSystemAdminDashboardData()
    {
        // Similar to ADMIN but with more system-focused metrics
        $data = $this->getAdminDashboardData();

        // Add system-specific metrics
        $usersByRole = [
            'employees' => User::where('roles', UserRole::EMPLOYEE->value)->count(),
            'supervisors' => User::where('roles', UserRole::SUPERVISOR->value)->count(),
            'admins' => User::where('roles', UserRole::ADMIN->value)->count(),
            'systemAdmins' => User::where('roles', UserRole::SYSTEM_ADMIN->value)->count(),
        ];

        // Get system statistics by time
        $systemStats = [
            'dailyInstructions' => Instruction::whereDate('created_at', today())->count(),
            'weeklyInstructions' => Instruction::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'monthlyInstructions' => Instruction::whereMonth('created_at', now()->month)->count(),
        ];

        // Get active users count (with activity in last 7 days)
        $activeUsers = User::whereHas('activities', function($query) {
            $query->whereDate('created_at', '>=', now()->subDays(7));
        })->count();

        return array_merge($data, [
            'usersByRole' => $usersByRole,
            'systemStats' => $systemStats,
            'activeUsers' => $activeUsers,
            'isSystemAdmin' => true,
        ]);
    }

    /**
     * Get instruction trend data for user dashboard.
     *
     * @param \App\Models\User $user
     * @param bool $includeSent
     * @return array
     */
    private function getInstructionTrendData(User $user, $includeSent = false)
    {
        $dates = collect();
        $totalData = [];
        $completedData = [];
        $pendingData = [];

        // Get dates for last 7 entries (weekly)
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i * 5);
            $dates->push($date);

            // Query for instructions on this date
            $query = Instruction::query();

            if ($includeSent) {
                $query->where(function($q) use ($user) {
                    $q->where('sender_id', $user->id)
                      ->orWhereHas('recipients', function($r) use ($user) {
                          $r->where('users.id', $user->id);
                      });
                });
            } else {
                $query->whereHas('recipients', function($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            }

            // Count total instructions up to this date
            $totalCount = (clone $query)
                ->where('created_at', '<=', $date)
                ->count();

            // Count completed instructions up to this date
            $completedCount = (clone $query)
                ->where('created_at', '<=', $date)
                ->whereHas('activities', function($q) use ($date) {
                    $q->where('action', 'completed')
                      ->where('created_at', '<=', $date);
                })
                ->count();

            // Calculate pending
            $pendingCount = $totalCount - $completedCount;

            $totalData[] = $totalCount;
            $completedData[] = $completedCount;
            $pendingData[] = $pendingCount;
        }

        return [
            'labels' => $dates->map(function($date) {
                return $date->format('M d');
            })->toArray(),
            'totalData' => $totalData,
            'completedData' => $completedData,
            'pendingData' => $pendingData,
        ];
    }

    /**
     * Get admin-level instruction trend data.
     *
     * @return array
     */
    private function getAdminInstructionTrendData()
    {
        $dates = collect();
        $totalData = [];
        $completedData = [];
        $pendingData = [];

        // Get dates for last 7 entries (weekly)
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i * 5);
            $dates->push($date);

            // Count total instructions up to this date
            $totalCount = Instruction::where('created_at', '<=', $date)->count();

            // Count completed instructions up to this date
            $completedCount = Instruction::where('created_at', '<=', $date)
                ->whereHas('activities', function($q) use ($date) {
                    $q->where('action', 'completed')
                      ->where('created_at', '<=', $date);
                })
                ->count();

            // Calculate pending
            $pendingCount = $totalCount - $completedCount;

            $totalData[] = $totalCount;
            $completedData[] = $completedCount;
            $pendingData[] = $pendingCount;
        }

        return [
            'labels' => $dates->map(function($date) {
                return $date->format('M d');
            })->toArray(),
            'totalData' => $totalData,
            'completedData' => $completedData,
            'pendingData' => $pendingData,
        ];
    }

    /**
     * Get status distribution data for user dashboard.
     *
     * @param \App\Models\User $user
     * @param bool $includeSent
     * @return array
     */
    private function getStatusDistributionData(User $user, $includeSent = false)
    {
        // Base query
        $query = Instruction::query();

        if ($includeSent) {
            $query->where(function($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhereHas('recipients', function($r) use ($user) {
                      $r->where('users.id', $user->id);
                  });
            });
        } else {
            $query->whereHas('recipients', function($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        $total = $query->count();

        // Get completed count
        $completed = (clone $query)
            ->whereHas('activities', function($q) {
                $q->where('action', 'completed');
            })
            ->count();

        // Get forwarded count
        $forwarded = (clone $query)
            ->whereHas('recipients', function($q) {
                $q->whereNotNull('instruction_user.forwarded_by_id');
            })
            ->count();

        // Get in progress count (has replies but not completed)
        $inProgress = (clone $query)
            ->whereHas('replies')
            ->whereNotIn('id', function($q) {
                $q->select('instruction_id')
                  ->from('instruction_activities')
                  ->where('action', 'completed');
            })
            ->count();

        // Get delayed count (created more than 7 days ago, not completed)
        $delayed = (clone $query)
            ->where('created_at', '<=', now()->subDays(7))
            ->whereNotIn('id', function($q) {
                $q->select('instruction_id')
                  ->from('instruction_activities')
                  ->where('action', 'completed');
            })
            ->count();

        // Calculate pending (total minus others)
        $pending = $total - ($completed + $inProgress + $delayed);

        return [
            'labels' => ['Completed', 'Pending', 'In Progress', 'Forwarded', 'Delayed'],
            'data' => [$completed, $pending, $inProgress, $forwarded, $delayed],
        ];
    }

    /**
     * Get admin-level status distribution data.
     *
     * @return array
     */
    private function getAdminStatusDistributionData()
    {
        $total = Instruction::count();

        // Get completed count
        $completed = Instruction::whereHas('activities', function($q) {
            $q->where('action', 'completed');
        })->count();

        // Get forwarded count
        $forwarded = Instruction::whereHas('recipients', function($q) {
            $q->whereNotNull('instruction_user.forwarded_by_id');
        })->count();

        // Get in progress count (has replies but not completed)
        $inProgress = Instruction::whereHas('replies')
            ->whereNotIn('id', function($q) {
                $q->select('instruction_id')
                  ->from('instruction_activities')
                  ->where('action', 'completed');
            })
            ->count();

        // Get delayed count (created more than 7 days ago, not completed)
        $delayed = Instruction::where('created_at', '<=', now()->subDays(7))
            ->whereNotIn('id', function($q) {
                $q->select('instruction_id')
                  ->from('instruction_activities')
                  ->where('action', 'completed');
            })
            ->count();

        // Calculate pending (total minus others)
        $pending = $total - ($completed + $inProgress + $delayed);

        return [
            'labels' => ['Completed', 'Pending', 'In Progress', 'Forwarded', 'Delayed'],
            'data' => [$completed, $pending, $inProgress, $forwarded, $delayed],
        ];
    }
}
