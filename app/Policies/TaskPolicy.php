<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        // both office + client can view
        return $task->project->users()
            ->where('users.id', $user->id)
            ->exists();
    }

    public function setStatus(User $user, Task $task): bool
    {
        return $task->project->users()
            ->where('users.id', $user->id)
            ->wherePivotIn('role', ['office', 'worker', 'member'])
            ->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Project $project): bool
    {
        return $project->users()
            ->where('users.id', $user->id)
            ->wherePivot('role', 'office')
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
    if (!$task->project) {
        return false;
    }

    return $task->project->users()
        ->where('users.id', $user->id)
        ->wherePivotIn('role', ['office', 'worker'])
        ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return false;
    }
}
