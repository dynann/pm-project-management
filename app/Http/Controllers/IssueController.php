<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\User;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class IssueController extends Controller
{
    protected array $updateRules = [
        'title' => 'sometimes|string|max:255',
        'description' => 'sometimes|string',
        'startDate' => 'sometimes|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'duration' => 'nullable|integer',
        'statusID' => 'sometimes|exists:statuses,id',
        'sprintID' => 'nullable|exists:sprints,id',
        'projectID' => 'sometimes|exists:projects,id',
        'priority' => 'sometimes|in:low,medium,high,critical',
    ];

    protected array $withRelations = [
        'status',
        'sprint',
        'project',
        'creator',
        'assignee',
        'comments',
        'attachments'
    ];

    public function index(): JsonResponse
    {
        $issues = Issue::with($this->withRelations)->get();
        return response()->json($issues);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->only([
            'title',
            'description',
            'startDate',
            'endDate',
            'duration',
            'statusID',
            'sprintID',
            'projectID',
            'userID',
            'assigneeID',
            'assignerID',
            'priority',
        ]);
    
        $issue = DB::transaction(function () use ($data) {
            return Issue::create($data);
        });
    
        return response()->json($issue->load($this->withRelations), 201);
    }

    public function show(Issue $issue): JsonResponse
    {
        return response()->json($issue->load($this->withRelations));
    }

   public function update(Request $request, Issue $issue): JsonResponse
{
    // Validate request data
    $validator = Validator::make($request->all(), $this->updateRules);
    
    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        DB::transaction(function () use ($issue, $request) {
            $issue->update($request->only([
                'title',
                'description',
                'startDate',
                'endDate',
                'duration',
                'statusID',
                'sprintID',
                'projectID',
                'priority',
                'assigneeID',
                'assignerID'
            ]));
        });

        return response()->json([
            'message' => 'Issue updated successfully',
            'data' => $issue->load($this->withRelations)
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to update issue',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function destroy(Issue $issue): JsonResponse
    {
        DB::transaction(function () use ($issue) {
            $issue->delete();
        });

        return response()->json(null, 204);
    }

    public function assign(Issue $issue, int $userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        DB::transaction(function () use ($issue, $user) {
            $issue->update([
                'assigneeID' => $user->id,
                'assignerID' => auth()->id() ?? $issue->userID
            ]);
        });

        return response()->json($issue->load('assignee'));
    }

    public function updateStatus(Issue $issue, int $statusId): JsonResponse
    {
        $status = Status::findOrFail($statusId);

        DB::transaction(function () use ($issue, $status) {
            $issue->update(['statusID' => $status->id]);
        });

        return response()->json($issue->load('status'));
    }

    public function comments(Issue $issue): JsonResponse
    {
        $comments = $issue->comments()->with('user')->get();
        return response()->json($comments);
    }

    public function getByProject(int $projectId): JsonResponse
    {
        $issues = Issue::with($this->withRelations)
            ->where('projectID', $projectId)
            ->get();

        return response()->json($issues);
    }
}