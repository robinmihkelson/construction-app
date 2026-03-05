<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function index()
    {

        $projects = auth()->user()->projects()->latest()->get();

        return Inertia::render('Projects/Index', [
            'projects' => $projects
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $project = Project::create([
            'name' => $request->name,
        ]);

        $project->users()->attach(auth()->id(), ['role' => 'office']);

        return redirect()->back();
    }

    public function show(Project $project)
    {

    $this->authorize('view', $project);

    $members = $project->users()
        ->select('users.id','users.name')
        ->withPivot('role')
        ->orderBy('users.name')
        ->get();

    $project->load([
        'tasks.assignee:id,name',
    ]);

    $comments = $project->comments()
    ->with('user:id,name')
    ->latest()
    ->get();


    $users = User::select('id', 'name')->orderBy('name')->get();

    return Inertia::render('Projects/Show', [
        'project' => $project,
        'tasks' => $project->tasks()->latest()->get()->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'due_date' => $task->due_date,
                'assignee' => $task->assignee ? [
                    'id' => $task->assignee->id,
                    'name' => $task->assignee->name,
                ] : null,
            ];
        }),
        'users' => $users,
        'members' => $members,
        'comments' => $comments,

        'can' => [
            'manageTasks' => Gate::allows('create', [Task::class, $project]),
            'changeTaskStatus' => $project->users()
                ->where('users.id', auth()->id())
                ->wherePivotIn('role', ['office', 'worker', 'member'])
                ->exists(),
            'manageMembers' => $project->users()
                ->where('users.id', auth()->id())
                ->wherePivot('role', 'office')
                ->exists(),
        ],
    ]);
    }

    public function storeTask(Request $request, Project $project)
    {
    $this->authorize('view', $project);

    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'assigned_to' => 'nullable|exists:users,id',
        'due_date' => 'nullable|date',
    ]);

    $project->tasks()->create([
        'title' => $validated['title'],
        'assigned_to' => $validated['assigned_to'] ?? null,
        'due_date' => $validated['due_date'] ?? null,
        'status' => 'todo',
    ]);

    return redirect()->back()->with('success', 'Task created.');;
    }

    
    public function destroyTask(Task $task)
    {
    $task->loadMissing('project.users');

    $project = $task->project;

    $member = $project->users->firstWhere('id', auth()->id());
    abort_unless($member, 403);

    $role = $member->pivot->role ?? null;
    abort_unless(in_array($role, ['office'], true), 403);

    $task->delete();

    return to_route('projects.show', $project, 303)->with('success', 'Task deleted.');
    }


    public function updateTask(Task $task, Request $request)
    {
    if ($request->has('status') && count($request->all()) === 1) {
        $this->authorize('setStatus', $task);

        $data = $request->validate([
            'status' => ['required', 'in:todo,doing,done'],
        ]);

        $task->update($data);

        return to_route('projects.show', $task->project_id, 303)->with('success', 'Task updated.');
    }

    $this->authorize('update', $task);

    $data = $request->validate([
        'title' => ['sometimes', 'string', 'max:255'],
        'assigned_to' => ['nullable', 'exists:users,id'],
        'due_date' => ['nullable', 'date'],
    ]);

    $task->update($data);

    return to_route('projects.show', $task->project_id, 303)->with('success', 'Task updated.');
    }


    public function addMember(Request $request, Project $project)
    {
    abort_unless(
        $project->users()
            ->where('users.id', auth()->id())
            ->wherePivot('role', 'office')
            ->exists(),
        403
    );

    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'role' => 'nullable|in:client,worker,office,member',
    ]);

    if ((int) $validated['user_id'] === (int) $request->user()->id) {
        return back()->with('error', "You can't change your own role here.");
    }

    $project->users()->syncWithoutDetaching([
        $validated['user_id'] => ['role' => $validated['role'] ?? 'member'],
    ]);

    return back()->with('success', 'Member added.');
    }
    

    public function storeMember(Project $project, Request $request)
    {
    abort_unless(
        $project->users()
            ->where('users.id', auth()->id())
            ->wherePivot('role', 'office')
            ->exists(),
        403
    );

    $data = $request->validate([
        'user_id' => ['required', 'exists:users,id'],
        'role' => ['nullable', 'in:member,client,worker,office'],
    ]);

    $project->users()->syncWithoutDetaching([
        $data['user_id'] => ['role' => $data['role'] ?: 'member'],
    ]);

    return back();
    }

    public function updateMember(Project $project, User $user, Request $request)
    {
    abort_unless(
        $project->users()
            ->where('users.id', auth()->id())
            ->wherePivot('role', 'office')
            ->exists(),
        403
    );

    $data = $request->validate([
        'role' => ['required', 'in:member,client,worker,office'],
    ]);

    $project->users()->updateExistingPivot($user->id, ['role' => $data['role']]);

    return back()->with('success', 'Member updated.');
    }

    public function destroyMember(Project $project, User $user)
    {
    abort_unless(
        $project->users()
            ->where('users.id', auth()->id())
            ->wherePivot('role', 'office')
            ->exists(),
        403
    );

    if ($user->id === auth()->id()) {
        return back()->with('error', 'You cannot remove yourself from the project.');
    }

    $project->users()->detach($user->id);

    return back()->with('success', 'Member removed.');
    }

    public function storeComment(Request $request, Project $project)
    {
    $this->authorize('view', $project);

    $validated = $request->validate([
        'body' => 'required|string|max:5000',
    ]);

    $project->comments()->create([
        'user_id' => auth()->id(),
        'body' => $validated['body'],
    ]);

    return redirect()->back()->with('success', 'Comment added.');;
    }

    public function update(Request $request, Project $project)
    {   
    $this->authorize('update', $project);

    $data = $request->validate([
        'name' => ['sometimes', 'string', 'max:255'],
        'status' => ['sometimes', 'string', 'max:50'],
    ]);

    $project->update($data);

    return back()->with('success', 'Project updated.');
    }


}
