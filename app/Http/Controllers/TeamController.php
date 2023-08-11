<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function getUsers(Request $request)
    {
        $this->authorize('add users to projects');
        // Get the current team from the authenticated user
        $team = $request->user()->currentTeam;

        // Fetch the users associated with the team
        $users = $team->users;

        return response()->json($users);
    }
}
