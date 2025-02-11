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

class GenerateTXTReport implements ShouldQueue
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
        // Obtener los usuarios filtrados por rango de fecha de nacimiento
        $users = User::select('id', 'name', 'email', 'birth_date', 'created_at')
            ->whereBetween('birth_date', [$this->startDate, $this->endDate])
            ->orderBy('birth_date', 'desc')
            ->get();

        // Crear el contenido del archivo TXT
        $content = "ID\tName\tEmail\tBirth Date\tCreated At\n";
        $content .= str_repeat('-', 80) . "\n";

        foreach ($users as $user) {
            $content .= "{$user->id}\t{$user->name}\t{$user->email}\t{$user->birth_date}\t{$user->created_at}\n";
        }

        // Definir el nombre y la ruta del archivo
        $fileName = 'report_' . time() . '.txt';

        // Guardar el archivo en el disco 'private'
        Storage::disk('private')->put($fileName, $content);

        // Guardar la ruta en la base de datos
        $this->report->update([
            'report_link' => $fileName
        ]);
    }
}
