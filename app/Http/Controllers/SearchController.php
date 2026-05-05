<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\Project;
use App\Models\ProjectMessage;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['query' => $q, 'results' => []]);
        }

        $like = '%' . $q . '%';
        $projectIds = $request->user()->projects()->pluck('projects.id');

        $projects = Project::query()
            ->whereIn('id', $projectIds)
            ->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                  ->orWhere('description', 'like', $like);
            })
            ->latest('updated_at')
            ->limit(5)
            ->get(['id', 'name', 'status'])
            ->map(fn (Project $p) => [
                'type' => 'project',
                'id' => $p->id,
                'title' => $p->name,
                'subtitle' => Str::headline((string) ($p->status ?? 'project')),
                'url' => route('projects.show', $p),
            ]);

        $tasks = Task::query()
            ->whereIn('project_id', $projectIds)
            ->where(function ($w) use ($like) {
                $w->where('title', 'like', $like)
                  ->orWhere('description', 'like', $like);
            })
            ->with('project:id,name')
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (Task $t) => [
                'type' => 'task',
                'id' => $t->id,
                'title' => $t->title,
                'subtitle' => trim(($t->project?->name ?? 'Task') . ' · ' . Str::headline((string) $t->status)),
                'url' => route('projects.show', $t->project_id) . '?task=' . $t->id,
            ]);

        $messages = ProjectMessage::query()
            ->whereIn('project_id', $projectIds)
            ->where('body', 'like', $like)
            ->with(['project:id,name', 'user:id,name'])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (ProjectMessage $m) => [
                'type' => 'message',
                'id' => $m->id,
                'title' => Str::limit(trim((string) $m->body), 80) ?: 'Attachment message',
                'subtitle' => trim(($m->project?->name ?? 'Chat') . ' · ' . ($m->user?->name ?? 'Unknown')),
                'url' => route('chat.show', $m->project_id),
            ]);

        $inquiries = Inquiry::query()
            ->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                  ->orWhere('email', 'like', $like)
                  ->orWhere('message', 'like', $like);
            })
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (Inquiry $i) => [
                'type' => 'inquiry',
                'id' => $i->id,
                'title' => $i->name,
                'subtitle' => trim($i->email . ' · ' . Str::headline((string) $i->status)),
                'url' => route('inquiries.show', $i),
            ]);

        $results = $projects
            ->concat($tasks)
            ->concat($messages)
            ->concat($inquiries)
            ->values();

        return response()->json([
            'query' => $q,
            'results' => $results,
        ]);
    }
}
