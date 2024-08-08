<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Check if the user is an owner of the current team.
        return $user->ownsCurrentTeam() || $user->hasTeamPermission($user->currentTeam(), 'projects:viewAll');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        return $user->ownsCurrentTeam() || $user->belongsToTeam($project->team) &&
            $user->hasTeamPermission($project->team, 'projects:view') &&
            $user->tokenCan('projects:view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->ownsCurrentTeam() || $user->hasTeamPermission($user->currentTeam(), 'projects:create') &&
            $user->tokenCan('projects:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->ownsCurrentTeam() || $user->belongsToTeam($project->team) &&
            $user->hasTeamPermission($project->team, 'projects:edit') &&
            $user->tokenCan('projects:edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->ownsCurrentTeam() || $user->belongsToTeam($project->team) &&
            $user->hasTeamPermission($project->team, 'projects:delete') &&
            $user->tokenCan('projects:delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return $user->ownsCurrentTeam();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $user->ownsCurrentTeam();
    }
}
