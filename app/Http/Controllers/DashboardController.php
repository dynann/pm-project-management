<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Sprint;
use App\Models\Issue;
use App\Models\Project;
use Psy\Command\WhereamiCommand;
use App\Models\Status;

class DashboardController extends Controller
{
    public function dashboardSummary()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'user not found',
                'success' => false
            ], 404);
        }

        $userID = $user->id;

        // Get projects owned by user
        $projects = Project::where('ownerID', $userID)->get();
        $countProjects = $projects->count();

        // Get project IDs for the user
        $projectIDs = $projects->pluck('id');

        // Get issues related to user's projects
        $issues = Issue::whereIn('projectID', $projectIDs)->get();
        $countIssues = $issues->count();

        $summaryData = [
            'allProjects' => $projects,
            'projectCount' => $countProjects,
            'allIssues' => $issues,
            'issuesCount' => $countIssues,
        ];

        return response()->json([
            'message' => 'Fetched successfully',
            'success' => true,
            'summaryData' => $summaryData
        ], 200);
    }

    public function dashboardRecentActivity()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'success' => false
            ], 404);
        }

        $userID = Auth::id();
        $projects = Project::where('ownerID', $userID)->pluck('id');

        $recentActivity = Issue::with(['status', 'project'])->whereIn('projectID', $projects)->where('updated_at', '>=', Carbon::now()->subDays(30))->orderBy('updated_at', 'desc')->limit(10)->get(['id', 'statusID', 'projectID', 'updated_at']);

        return response()->json([
            'message' => 'successfully',
            'success' => true,
            'data' => $recentActivity
        ], 201);


    }

    protected function combineDeadlines($sprintDeadlines, $issueDeadlines)
    {
        $combined = collect();

        // Add sprint deadlines
        foreach ($sprintDeadlines as $sprint) {
            $combined->push([
                'type' => 'sprint',
                'id' => $sprint->id,
                'name' => $sprint->name,
                'endDate' => $sprint->endDate,
                'goal' => $sprint->sprintGoal,
                'open_issues_count' => $sprint->issues->count(),
                'related_to' => null
            ]);
        }

        // Add issue deadlines
        foreach ($issueDeadlines as $issue) {
            $combined->push([
                'type' => 'issue',
                'id' => $issue->id,
                'title' => $issue->title,
                'endDate' => $issue->endDate,
                'priority' => $issue->priority,
                'status' => $issue->status->name,
                'assignee' => $issue->assignee ? $issue->assignee->name : null,
                'related_to' => [
                    'sprint' => $issue->sprint ? $issue->sprint->name : null,
                    'project' => $issue->project ? $issue->project->name : null
                ]
            ]);
        }

        // Sort all deadlines by date
        return $combined->sortBy('endDate')->values();
    }

    public function dashboardUpcomingDeadlines()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'success' => false
            ], 404);
        }

        $userID = Auth::id();

        // Get active sprints owned by the user with upcoming deadlines
        $sprintDeadlines = Sprint::with([
            'owner',
            'issues' => function ($query) {
                $query->whereIn('statusID', Status::whereIn('name', ['Open', 'In Progress'])->pluck('id'));
            }
        ])
            ->where('ownerID', $userID)
            ->whereNotNull('endDate')
            ->where('endDate', '>=', Carbon::now())
            ->where('endDate', '<=', Carbon::now()->addDays(7))
            ->orderBy('endDate', 'asc')
            ->get(['id', 'name', 'endDate', 'sprintGoal']);

        // Get issues with upcoming deadlines from user's projects
        $projects = Project::where('ownerID', $userID)->pluck('id');

        $issueDeadlines = Issue::with(['status', 'assignee', 'sprint', 'project'])
            ->whereIn('projectID', $projects)
            ->whereNotNull('endDate')
            ->where('endDate', '>=', Carbon::now())
            ->where('endDate', '<=', Carbon::now()->addDays(7))
            ->whereIn('statusID', Status::whereIn('name', ['Open', 'In Progress'])->pluck('id'))
            ->orderBy('endDate', 'asc')
            ->get(['id', 'title', 'endDate', 'priority', 'statusID', 'assigneeID', 'sprintID', 'projectID']);

        return response()->json([
            'message' => 'Upcoming deadlines fetched successfully',
            'success' => true,
            'data' => [
                'sprint_deadlines' => $sprintDeadlines,
                'issue_deadlines' => $issueDeadlines,
                'combined_deadlines' => $this->combineDeadlines($sprintDeadlines, $issueDeadlines)
            ]
        ], 200);
    }

    public function adminGetAllUsers()
    {
        $users = User::with(['projects', 'assignedIssues'])
            ->withCount(['projects', 'assignedIssues'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'successfully',
            'success' => true,
            'data' => $users,
            'total_count' => $users->count()
        ], 200);
    }

    public function adminGetAllProjects()
    {
        $projects = Project::with(['owner', 'issues', 'sprints'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'projects fetched successfully',
            'success' => true,
            'data' => $projects,
            'total_count' => $projects->count()
        ], 200);
    }

    public function adminGetAllIssues()
    {
        $issues = Issue::with(['project', 'status', 'assignee', 'sprint'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'successfully',
            'success' => true,
            'data' => $issues,
            'total_count' => $issues->count()
        ], 200);
    }

    public function adminGetAllSprints()
    {

        $sprints = Sprint::with(['owner', 'issues'])
            ->withCount('issues')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Sprints fetched successfully',
            'success' => true,
            'data' => $sprints,
            'total_count' => $sprints->count()
        ], 200);
    }

    /**
     * Get system activity logs for admin
     */
    public function adminSystemActivity()
    {

        $recentProjects = Project::with('owner')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'ownerID', 'created_at']);

        $recentIssues = Issue::with(['project', 'assignee'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'title', 'projectID', 'assigneeID', 'created_at']);

        $recentUsers = User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'username', 'email', 'created_at']);

        return response()->json([
            'message' => 'System activity fetched successfully',
            'success' => true,
            'data' => [
                'recent_projects' => $recentProjects,
                'recent_issues' => $recentIssues,
                'recent_users' => $recentUsers
            ]
        ], 200);
    }

    /**
     * Get user performance metrics for admin
     */
    public function adminUserPerformance()
    {

        $userPerformance = User::with(['projects', 'assignedIssues'])
            ->withCount([
                'projects',
                'assignedIssues',
                'assignedIssues as completed_issues_count' => function ($query) {
                    $query->whereHas('status', function ($q) {
                        $q->whereIn('name', ['Closed', 'Done']);
                    });
                },
                'assignedIssues as overdue_issues_count' => function ($query) {
                    $query->where('endDate', '<', Carbon::now())
                        ->whereHas('status', function ($q) {
                            $q->whereNotIn('name', ['Closed', 'Done']);
                        });
                }
            ])
            ->get()
            ->map(function ($user) {
                $completionRate = $user->assigned_issues_count > 0
                    ? round(($user->completed_issues_count / $user->assigned_issues_count) * 100, 2)
                    : 0;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'projects_count' => $user->projects_count,
                    'assigned_issues_count' => $user->assigned_issues_count,
                    'completed_issues_count' => $user->completed_issues_count,
                    'overdue_issues_count' => $user->overdue_issues_count,
                    'completion_rate' => $completionRate
                ];
            });

        return response()->json([
            'message' => 'User performance metrics fetched successfully',
            'success' => true,
            'data' => $userPerformance
        ], 200);
    }

    /**
     * Get project health overview for admin
     */
    public function adminProjectHealth()
    {

        $projectHealth = Project::with(['owner', 'issues', 'sprints'])
            ->withCount([
                'issues',
                'issues as open_issues_count' => function ($query) {
                    $query->whereHas('status', function ($q) {
                        $q->where('name', 'Open');
                    });
                },
                'issues as completed_issues_count' => function ($query) {
                    $query->whereHas('status', function ($q) {
                        $q->whereIn('name', ['Closed', 'Done']);
                    });
                },
                'issues as overdue_issues_count' => function ($query) {
                    $query->where('endDate', '<', Carbon::now())
                        ->whereHas('status', function ($q) {
                            $q->whereNotIn('name', ['Closed', 'Done']);
                        });
                },
                'sprints'
            ])
            ->get()
            ->map(function ($project) {
                $completionRate = $project->issues_count > 0
                    ? round(($project->completed_issues_count / $project->issues_count) * 100, 2)
                    : 0;

                $healthScore = $this->calculateProjectHealthScore(
                    $project->open_issues_count,
                    $project->completed_issues_count,
                    $project->overdue_issues_count,
                    $project->issues_count
                );

                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'owner' => $project->owner->username,
                    'total_issues' => $project->issues_count,
                    'open_issues' => $project->open_issues_count,
                    'completed_issues' => $project->completed_issues_count,
                    'overdue_issues' => $project->overdue_issues_count,
                    'sprints_count' => $project->sprints_count,
                    'completion_rate' => $completionRate,
                    'health_score' => $healthScore,
                    'health_status' => $this->getHealthStatus($healthScore)
                ];
            });

        return response()->json([
            'message' => 'Project health overview fetched successfully',
            'success' => true,
            'data' => $projectHealth
        ], 200);
    }

    /**
     * Calculate project health score
     */
    private function calculateProjectHealthScore($openIssues, $completedIssues, $overdueIssues, $totalIssues)
    {
        if ($totalIssues == 0)
            return 100;

        $completionScore = ($completedIssues / $totalIssues) * 40;
        $overdueScore = max(0, 30 - (($overdueIssues / $totalIssues) * 30));
        $activeScore = min(30, ($openIssues / $totalIssues) * 30);

        return round($completionScore + $overdueScore + $activeScore, 2);
    }

    /**
     * Get health status based on score
     */
    private function getHealthStatus($score)
    {
        if ($score >= 80)
            return 'Excellent';
        if ($score >= 60)
            return 'Good';
        if ($score >= 40)
            return 'Fair';
        return 'Poor';
    }
}
