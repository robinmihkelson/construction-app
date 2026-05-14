<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskProgressImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskProgressImageController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $task->loadMissing('project');
        $this->authorize('view', $task);

        $validated = $request->validate([
            'images' => ['required', 'array', 'max:8'],
            'images.*' => ['image', 'max:10240'],
            'captions' => ['sometimes', 'array'],
            'captions.*' => ['nullable', 'string', 'max:500'],
        ]);

        $captions = $validated['captions'] ?? [];

        foreach ($validated['images'] as $index => $image) {
            $path = $image->store("tasks/{$task->id}/progress", 'public');
            $caption = isset($captions[$index]) ? trim((string) $captions[$index]) : null;

            $task->progressImages()->create([
                'user_id' => $request->user()->id,
                'disk' => 'public',
                'path' => $path,
                'original_name' => $image->getClientOriginalName(),
                'caption' => $caption !== '' ? $caption : null,
                'mime' => $image->getMimeType(),
                'size' => $image->getSize(),
            ]);
        }

        return back()->with('success', 'Progress image uploaded.');
    }

    public function update(Request $request, Task $task, TaskProgressImage $image)
    {
        $task->loadMissing('project');
        $this->authorize('view', $task);

        if ((int) $image->task_id !== (int) $task->id) {
            abort(404);
        }

        $canManageTask = $request->user()->can('update', $task);
        abort_unless($canManageTask || (int) $image->user_id === (int) $request->user()->id, 403);

        $validated = $request->validate([
            'caption' => ['nullable', 'string', 'max:500'],
        ]);

        $caption = isset($validated['caption']) ? trim((string) $validated['caption']) : null;
        $image->update(['caption' => $caption !== '' ? $caption : null]);

        return back()->with('success', 'Caption updated.');
    }

    public function destroy(Task $task, TaskProgressImage $image, Request $request)
    {
        $task->loadMissing('project');
        $this->authorize('view', $task);

        if ((int) $image->task_id !== (int) $task->id) {
            abort(404);
        }

        $canManageTask = $request->user()->can('update', $task);
        abort_unless($canManageTask || (int) $image->user_id === (int) $request->user()->id, 403);

        Storage::disk($image->disk)->delete($image->path);
        $image->delete();

        return back()->with('success', 'Progress image deleted.');
    }
}
