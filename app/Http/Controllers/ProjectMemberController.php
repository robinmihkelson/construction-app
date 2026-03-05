<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;

class ProjectMemberController extends Controller
{
    public function destroy(Project $project, User $user)
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

        return to_route('projects.show', $project, 303)->with('success', 'Member removed.');
    }

    public function update(Project $project, User $user, Request $request)
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

        if ($user->id === auth()->id() && $data['role'] !== 'office') {
            return back()->with('error', 'You cannot change your own role.');
        }

        $project->users()->updateExistingPivot($user->id, [
            'role' => $data['role'],
        ]);

        return to_route('projects.show', $project, 303)->with('success', 'Role updated.');
}
}
