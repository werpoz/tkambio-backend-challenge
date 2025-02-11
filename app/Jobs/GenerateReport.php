<?php

namespace App\Jobs;

use App\Models\Report;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $report;
    protected $startDate;
    protected $endDate;

    public function __construct(Report $report, string $startDate, string $endDate)
    {
        $this->report = $report;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function handle(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Birth Date');
        $sheet->setCellValue('E1', 'Created At');

        // Get users data filtered by birth date range
        $users = User::select('id', 'name', 'email', 'birth_date', 'created_at')
            ->whereBetween('birth_date', [$this->startDate, $this->endDate])
            ->orderBy('birth_date', 'desc')
            ->get();

        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue('A' . $row, $user->id);
            $sheet->setCellValue('B' . $row, $user->name);
            $sheet->setCellValue('C' . $row, $user->email);
            $sheet->setCellValue('D' . $row, $user->birth_date);
            $sheet->setCellValue('E' . $row, $user->created_at);
            $row++;
        }

        $fileName = 'report_' . time() . '.xlsx';
        $filePath = $fileName;

        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path('app/private/' . $filePath));

        $this->report->update([
            'report_link' => $filePath
        ]);
    }
}
