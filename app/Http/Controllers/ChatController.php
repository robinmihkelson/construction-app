<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Project;
use App\Models\ProjectMessage;
use App\Models\ProjectMessageRead;
use App\Models\ProjectMessageAttachment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function index(Request $request)
    {
    $userId = $request->user()->id;

    $projects = $request->user()->projects()
        ->select('projects.id', 'projects.name')
        ->selectSub(function ($q) use ($userId) {
            $q->from('project_messages as pm')
                ->leftJoin('project_message_reads as r', function ($join) use ($userId) {
                    $join->on('r.project_id', '=', 'pm.project_id')
                        ->where('r.user_id', '=', $userId);
                })
                ->whereColumn('pm.project_id', 'projects.id')
                ->where('pm.user_id', '!=', $userId)
                ->where(function ($w) {
                    $w->whereNull('r.last_read_at')
                      ->orWhereColumn('pm.created_at', '>', 'r.last_read_at');
                })
                ->selectRaw('count(*)');
        }, 'unread_count')
        ->orderBy('projects.name')
        ->get();

    return Inertia::render('Chat/Index', [
        'projects' => $projects,
    ]);
    }   

    public function show(Project $project, Request $request)
    {
    $this->authorize('view', $project);

    $read = ProjectMessageRead::updateOrCreate(
        ['project_id' => $project->id, 'user_id' => $request->user()->id],
        ['last_read_at' => now()]
    );

    $messages = $project->messages()
        ->with(['user:id,name', 'attachments'])
        ->orderByDesc('id')
        ->take(30)
        ->get()
        ->reverse()
        ->values();

    $hasMore = $project->messages()->count() > 30;

    return Inertia::render('Chat/Show', [
        'project' => $project->only('id', 'name'),
        'messages' => $messages,
        'has_more' => $hasMore,
        'last_read_at' => optional($read->last_read_at)->toISOString(),
    ]);
    }

    public function store(Project $project, Request $request)
    {
    $this->authorize('view', $project);

    $data = $request->validate([
        'body' => ['nullable', 'string', 'max:5000'],
        'attachments' => ['nullable', 'array', 'max:10'],
        'attachments.*' => ['file', 'max:20480'], // 20MB each
    ]);

    if (empty($data['body']) && !$request->hasFile('attachments')) {
        return back()->with('error', 'Write a message or attach a file.');
    }

    $message = $project->messages()->create([
        'user_id' => $request->user()->id,
        'body' => $data['body'] ?? '',
    ]);

    if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
            $path = $file->store("chat/{$project->id}", 'public');

            $message->attachments()->create([
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    return back()->with('success', 'Message sent.');
    }

    public function destroy(Project $project, ProjectMessage $message)
    {
        $this->authorize('view', $project);

        if ($message->project_id !== $project->id) {
            abort(404);
        }

        $this->authorize('delete', $message);

        $message->delete();

        return back()->with('success', 'Message deleted.');
    }

    public function messages(Project $project, Request $request)
    {
        $this->authorize('view', $project);

        $data = $request->validate([
            'before_id' => ['nullable', 'integer'],
            'limit' => ['nullable', 'integer', 'min:10', 'max:200'],
        ]);

        $limit = $data['limit'] ?? 30;

        $q = $project->messages()
            ->with(['user:id,name', 'attachments'])
            ->orderByDesc('id');

        if (!empty($data['before_id'])) {
            $q->where('id', '<', $data['before_id']);
        }

        $batch = $q->limit($limit)->get();

        $hasMore = false;
        if ($batch->isNotEmpty()) {
            $oldestIdInBatch = $batch->last()->id;
            $hasMore = $project->messages()->where('id', '<', $oldestIdInBatch)->exists();
        }

        return response()->json([
            'messages' => $batch->reverse()->values(),
            'has_more' => $hasMore,
        ]);
    }


    
    
    public function viewAttachment(Project $project, ProjectMessageAttachment $attachment)
    {
    $this->authorize('view', $project);

    $attachment->loadMissing('message');

    if (!$attachment->message || $attachment->message->project_id !== $project->id) {
        abort(404);
    }

    if (!Storage::disk($attachment->disk)->exists($attachment->path)) {
        abort(404);
    }

    return Storage::disk($attachment->disk)->response($attachment->path);
    }

    public function download(Project $project, ProjectMessageAttachment $attachment, Request $request)
    {
    $this->authorize('view', $project);

    if ($attachment->message->project_id !== $project->id) {
        abort(404);
    }

    return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }
}
