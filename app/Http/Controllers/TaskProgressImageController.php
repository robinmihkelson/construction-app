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
        ]);

        foreach ($validated['images'] as $image) {
            $path = $image->store("tasks/{$task->id}/progress", 'public');

            $task->progressImages()->create([
                'user_id' => $request->user()->id,
                'disk' => 'public',
                'path' => $path,
                'original_name' => $image->getClientOriginalName(),
                'mime' => $image->getMimeType(),
                'size' => $image->getSize(),
            ]);
        }

        return back()->with('success', 'Progress image uploaded.');
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
