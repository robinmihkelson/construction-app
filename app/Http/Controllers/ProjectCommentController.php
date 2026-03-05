<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Comment;
use Illuminate\Http\Request;

class ProjectCommentController extends Controller
{
    public function destroy(Project $project, Comment $comment)
    {
        if ($comment->project_id !== $project->id) {
            abort(404);
        }

        $this->authorize('delete', $comment);

        $comment->delete();

        return to_route('projects.show', $project, 303)->with('success', 'Comment deleted.');
    }
}
