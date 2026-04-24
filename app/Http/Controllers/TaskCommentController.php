<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $task->loadMissing('project');
        $this->authorize('view', $task);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $task->comments()->create([
            'project_id' => $task->project_id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Comment added.');
    }

    public function destroy(Task $task, Comment $comment)
    {
        $task->loadMissing('project');
        $this->authorize('view', $task);

        if ((int) $comment->task_id !== (int) $task->id) {
            abort(404);
        }

        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }
}
