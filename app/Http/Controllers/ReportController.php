<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Jobs\GenerateReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|string|max:255',
            'end_date' => 'required|string|max:255'
        ]);

        $report = Report::create([
            'title' => $request->title,
            'report_link' => ''
        ]);

        GenerateReport::dispatch($report, $request->start_date, $request->end_date);

        return response()->json([
            'message' => 'Report generation has been queued',
            'report_id' => $report->id
        ]);
    }

    public function show(Report $report)
    {
        if (!Storage::exists($report->report_link)) {
            return response()->json(['message' => 'Report file not found'], 404);
        }

        return Storage::download($report->report_link);
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $reports = Report::orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($reports);
    }
}
