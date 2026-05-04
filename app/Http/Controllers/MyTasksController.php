<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class MyTasksController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $projectIds = $user->projects()->pluck('projects.id');

        $tasks = Task::query()
            ->where('assigned_to', $user->id)
            ->whereIn('project_id', $projectIds)
            ->with(['project:id,name'])
            ->orderByRaw("CASE status WHEN 'doing' THEN 0 WHEN 'todo' THEN 1 WHEN 'done' THEN 2 ELSE 3 END")
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description ?? '',
                'status' => $task->status,
                'due_date' => $task->due_date
                    ? Carbon::parse($task->due_date)->toDateString()
                    : null,
                'project' => $task->project ? [
                    'id' => $task->project->id,
                    'name' => $task->project->name,
                ] : null,
            ])
            ->values();

        return Inertia::render('MyTasks/Index', [
            'tasks' => $tasks,
        ]);
    }
}
