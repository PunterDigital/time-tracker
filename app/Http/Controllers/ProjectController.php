<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProjectController extends Controller
{
    /**
     * Adds a relationship for a user to a project
     *
     * @param Request $request
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function addUsers(Request $request, Project $project)
    {
        $this->authorize('projects:modifyUsers');
        $userIds = $request->input('users');
        $project->users()->attach($userIds);

        return response()->json(['message' => 'Users added successfully']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $team = $request->user()->currentTeam;

        $projects = Project::with('users')->where('team_id', $team->id)
            ->where(function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->orWhereHas('users', function ($query) use ($request) {
                        $query->where('users.id', $request->user()->id);
                    });
            })
            ->withCount(['timeEntries as total_time' => function ($query) {
                $query->select(DB::raw('SUM(TIMESTAMPDIFF(SECOND, start_time, end_time))'));
            }])
            ->get();

        return Inertia::render('Projects/Index', ['projects' => $projects, 'team_users' => $team->allUsers()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'client' => 'required',
            'name' => 'required',
            'budget' => 'required',
            'billing_rate' => 'nullable|numeric', // Validate billing rate
        ]);

        Project::create([
            'user_id' => auth()->id(),
            'client' => $request->client,
            'name' => $request->name,
            'budget' => $request->budget,
            'billing_rate' => $request->billing_rate, // Save billing rate
            'team_id' => Auth::user()->currentTeam->id, // Access the ID of the current team
        ]);

        return redirect()->route('projects.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'client' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'budget' => 'nullable|numeric',
            'billing_rate' => 'nullable|numeric',
        ]);

        $project = Project::find($id);

        if (!$project || $project->user_id !== auth()->id()) {
            return response()->json(['error' => 'Project not found or unauthorized'], 404);
        }

        $project->client = $request->client;
        $project->name = $request->name;
        $project->budget = $request->budget;
        $project->billing_rate = $request->billing_rate;

        $project->save();

        return redirect()->route('projects.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index');
    }
}
