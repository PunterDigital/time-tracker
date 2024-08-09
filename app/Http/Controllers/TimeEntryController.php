<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimeEntryController extends Controller
{

    public function getWeekView()
    {
        $userId = auth()->id();
        $weekView = [];

        $startOfWeek = Carbon::now()->startOfWeek();

        // Loop through each day of the week (Mon - Sun)
        for ($dayOfWeek = 0; $dayOfWeek < 7; $dayOfWeek++) {
            $currentDate = $startOfWeek->copy()->addDays($dayOfWeek);

            // Retrieve the time entries for the specific day of the week
            $timeEntries = TimeEntry::where('user_id', $userId)
                ->whereDate('start_time', $currentDate)
                ->get();

            // Process the time entries to calculate the total time and project-specific time
            $dayView = [
                'name' => $currentDate->format('l'), // Get the full textual representation of the day
                'totalTime' => 0,
                'projects' => [],
            ];

            foreach ($timeEntries as $entry) {
                // Calculate the time for each entry
                $start = \Carbon\Carbon::parse($entry->start_time);
                $end = \Carbon\Carbon::parse($entry->end_time);

                // Calculate the duration in seconds
                $entryTime = $end->diffInSeconds($start);

                $dayView['totalTime'] += $entryTime;
                $dayView['projects'][] = [
                    'name' => $entry->project->name,
                    'time' => $entryTime,
                    'memo' => $entry->memo ?? '',
                ];
            }

            $weekView[] = $dayView;
        }

        return response()->json($weekView);
    }

    public function getEntriesForDay($day)
    {
        $this->authorize('view time entries');
        $userId = auth()->id();
        $dayOfWeek = $this->getDayOfWeek($day);

        if ($dayOfWeek === null) {
            return response()->json(['error' => 'Invalid day'], 400);
        }

        $entries = TimeEntry::where('user_id', $userId)
            ->whereRaw("DAYOFWEEK(start_time) = ?", [$dayOfWeek])
            ->get()
            ->map(function ($entry) {
                $start = Carbon::parse($entry->start_time);
                $end = Carbon::parse($entry->end_time);
                $duration = $end->diffInSeconds($start);

                return [
                    'id' => $entry->id,
                    'projectName' => $entry->project->name,
                    'duration' => $duration,
                ];
            });

        $totalHours = $entries->sum('duration') / 3600;

        return response()->json([
            'entries' => $entries,
            'totalHours' => $totalHours,
        ]);
    }

    private function getDayOfWeek($day)
    {
        $days = [
            'Mon' => 2,
            'Tue' => 3,
            'Wed' => 4,
            'Thu' => 5,
            'Fri' => 6,
            'Sat' => 7,
            'Sun' => 1,
        ];

        return $days[$day] ?? null;
    }

    public function apiIndex(Request $request)
    {
        $query = TimeEntry::query();

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $query->whereHas('project', function ($query) {
            $query->where('user_id', auth()->id());
        });

        $timeEntries = $query->with('project')->get();

        return response()->json($timeEntries);
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
            'projectId' => 'required|exists:projects,id',
            'startTime' => 'required|date',
            'endTime' => 'required|date',
            'duration' => 'required|integer',
            'isBillable' => 'required|boolean',
            'billingRate' => 'required',
            'memo' => 'nullable',
        ]);

        TimeEntry::create([
            'project_id' => $request->projectId,
            'start_time' => $request->startTime,
            'end_time' => $request->endTime,
            'description' => $request->description,
            'is_billable' => $request->isBillable,
            'user_id' => Auth::id(),
            'billing_rate' => $request->billingRate,
            'memo' => $request->memo,
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
