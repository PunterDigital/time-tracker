<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Provides a breakdown of the week based on the time entries.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reportWeekBreakdown(Request $request)
    {
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');
        $userId = auth()->id();
        $weekBreakdown = [];

        // Loop through each day of the week (Mon - Sun)
        for ($dayOfWeek = 2; $dayOfWeek <= 8; $dayOfWeek++) {
            $mysqlDayOfWeek = $dayOfWeek % 7; // Adjust for MySQL DAYOFWEEK function

            $timeEntries = TimeEntry::where('user_id', $userId)
                ->whereDate('start_time', '>=', $startDate)
                ->whereDate('start_time', '<=', $endDate)
                ->whereRaw('DAYOFWEEK(start_time) = ?', [$mysqlDayOfWeek])
                ->get();

            $totalHours = 0;
            $totalBillableHours = 0;
            $chargeable = 0;

            foreach ($timeEntries as $entry) {
                $start = \Carbon\Carbon::parse($entry->start_time);
                $end = \Carbon\Carbon::parse($entry->end_time);
                $durationInSeconds = $end->diffInSeconds($start);
                $hours = $durationInSeconds / 3600;

                $totalHours += $hours;
                if ($entry->is_billable) {

                    $totalBillableHours += $hours;
                    $chargeable += $hours * floatval($entry->billing_rate);
                }
            }


            $billablePercentage = $totalHours ? ($totalBillableHours / $totalHours) * 100 : 0;

            $weekBreakdown[] = [
                'name' => jddayofweek($dayOfWeek - 2, 1),
                'totalHours' => $totalHours * 3600,
                'billablePercentage' => $billablePercentage,
                'chargeable' => $chargeable,
            ];
        }

        return response()->json($weekBreakdown);
    }

    public function reportProjects()
    {
        $userId = auth()->id();

        $projects = Project::where('user_id', $userId)->get()->map(function ($project) {
            // Calculate total time, billable time, and non-billable time
            $totalTime = 0;
            $totalBillableTime = 0;

            foreach ($project->timeEntries as $entry) {
                $start = \Carbon\Carbon::parse($entry->start_time);
                $end = \Carbon\Carbon::parse($entry->end_time);
                $duration = $end->diffInSeconds($start);

                $totalTime += $duration;

                if ($entry->is_billable) {
                    $totalBillableTime += $duration;
                }
            }

            $totalBillableAmount = $totalBillableTime * $project->billing_rate / 3600; // Convert seconds to hours
            $remainingBudget = $project->budget ? $project->budget - $totalBillableAmount : null; // Calculate remaining budget

            return [
                'id' => $project->id,
                'client' => $project->client,
                'name' => $project->name,
                'totalTime' => $totalTime, // Total time in seconds
                'totalBillableTime' => $totalBillableTime,
                'totalNonBillableTime' => $totalTime - $totalBillableTime,
                'totalBillableAmount' => $totalBillableAmount,
                'remainingBudget' => $remainingBudget, // Remaining budget
            ];
        });

        return response()->json($projects);
    }

    public function index() {
        $this->authorize('view reports');
        return Inertia::render('Reporting');
    }
}
