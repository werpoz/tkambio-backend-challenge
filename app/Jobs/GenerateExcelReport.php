<?php

namespace App\Jobs;

use App\Models\Report;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateExcelReport implements ShouldQueue
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
        // Crear una nueva hoja de cálculo
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Establecer encabezados
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Nombre');
        $sheet->setCellValue('C1', 'Correo Electrónico');
        $sheet->setCellValue('D1', 'Fecha de Nacimiento');
        $sheet->setCellValue('E1', 'Fecha de Creación');

        // Obtener usuarios filtrados por fecha de nacimiento
        $users = User::select('id', 'name', 'email', 'birth_date', 'created_at')
            ->whereBetween('birth_date', [$this->startDate, $this->endDate])
            ->orderBy('birth_date', 'desc')
            ->get();

        // Rellenar datos
        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue("A$row", $user->id);
            $sheet->setCellValue("B$row", $user->name);
            $sheet->setCellValue("C$row", $user->email);
            $sheet->setCellValue("D$row", $user->birth_date);
            $sheet->setCellValue("E$row", $user->created_at);
            $row++;
        }

        // Ajustar automáticamente las columnas
        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Definir el nombre del archivo
        $fileName = 'report_' . time() . '.xlsx';

        // Guardar el archivo en almacenamiento privado
        $filePath = "reports/$fileName"; // Carpeta dentro de 'private'
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);

        // Guardar en el disco de almacenamiento de Laravel
        Storage::disk('private')->put($filePath, file_get_contents($tempFile));
        unlink($tempFile);

        // Actualizar el enlace del reporte en la base de datos
        $this->report->update([
            'report_link' => $filePath
        ]);
    }
}
