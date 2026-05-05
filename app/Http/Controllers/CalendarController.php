<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $memberships = $user->projects()
            ->select('projects.id', 'projects.name')
            ->withPivot('role')
            ->get();

        $projectIds = $memberships->pluck('id');

        $editableProjectIds = $memberships
            ->filter(fn ($p) => in_array($p->pivot->role, ['office', 'worker'], true))
            ->pluck('id')
            ->all();

        $tasks = Task::query()
            ->whereIn('project_id', $projectIds)
            ->whereNotNull('due_date')
            ->with(['project:id,name', 'assignee:id,name'])
            ->orderBy('due_date')
            ->get()
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description ? Str::limit($task->description, 240) : null,
                'status' => $task->status,
                'due_date' => Carbon::parse($task->due_date)->toDateString(),
                'project' => $task->project ? [
                    'id' => $task->project->id,
                    'name' => $task->project->name,
                ] : null,
                'assignee' => $task->assignee ? [
                    'id' => $task->assignee->id,
                    'name' => $task->assignee->name,
                ] : null,
                'can_edit' => in_array($task->project_id, $editableProjectIds, true),
            ])
            ->values();

        $projects = $memberships
            ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name])
            ->sortBy('name')
            ->values();

        return Inertia::render('Calendar/Index', [
            'tasks' => $tasks,
            'projects' => $projects,
        ]);
    }
}
