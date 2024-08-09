<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::with('users')->where('team_id', $request->user()->currentTeam->id)
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

        $timeEntryController = new TimeEntryController();
        $weekView = $timeEntryController->getWeekView();

        return Inertia::render('Dashboard', ['projects' => $projects, 'weekView' => $weekView]);
    }
}
