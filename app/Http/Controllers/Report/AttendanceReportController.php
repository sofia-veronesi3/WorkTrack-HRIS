<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Departemen;


class AttendanceReportController extends Controller
{
    public function index(Request $request): View
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
        $departementId = $request->input('departement_id');

        $employees = User::where('role', 'employee')
        ->when($departementId, fn ($q) => $q->where('departement_id', $departementId))
        ->get();

        $attendances = Attendance::selectRaw("
                user_id,
                COUNT(CASE WHEN status = 'hadir' THEN 1 END) as present,
                COUNT(CASE WHEN status = 'terlambat' THEN 1 END) as late,
                COUNT(CASE WHEN status = 'cuti' THEN 1 END) as cuti,
                COUNT(CASE WHEN status = 'sakit' THEN 1 END) as sick,
                COUNT(CASE WHEN status = 'alpha' THEN 1 END) as absent
            ")
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $summary = $employees->map(function ($employee) use ($attendances) {
            $data = $attendances->get($employee->id);
            return (object) [
                'name' => $employee->name,
                'present' => $data->present ?? 0,
                'late' => $data->late ?? 0,
                'cuti' => $data->cuti ?? 0,
                'sick' => $data->sick ?? 0,
                'absent' => $data->absent ?? 0,
            ];
        });

        $months = collect(range(1, 12))->mapWithKeys(fn($m) => [
            $m => Carbon::create()->month($m)->translatedFormat('F'),
        ]);

        $years = range(Carbon::now()->year - 2, Carbon::now()->year + 1);
        $departments = Departemen::orderBy('name')->get();
        return view('reports.attendance', compact('summary', 'month', 'year', 'months', 'years', 'departments', 'departementId'));
    }

    public function export(Request $request)
{
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
        $departementId = $request->input('departement_id');

        $attendances = Attendance::with('user') //busco las asistencias y los datos del empleado 
        ->whereYear('attendance_date', $year) 
        ->whereMonth('attendance_date', $month)
        ->when($departementId, fn ($q) => $q->whereHas('user', fn ($q2) => $q2->where('departement_id', $departementId))) //si filtre por dpto veo q ese usuario este en ese dpto y lo muestro en la asistencia
        ->orderBy('attendance_date', 'asc')
        ->get();

    $filename = "attendance_report_{$month}_{$year}.csv";

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];

    return response()->stream(function () use ($attendances) {   //genera la respuesta del archivo como flujo de datos (para no cargar todo en memoria) y lo envia al navegador para descargarlo
        $handle = fopen('php://output', 'w');

        fputcsv($handle, ['Empleado', 'Fecha', 'Hora Ingreso', 'Hora Salida', 'Estado']);

        foreach ($attendances as $attendance) {
            fputcsv($handle, [
                $attendance->user->name ?? 'N/A',
                $attendance->attendance_date->format('d/m/Y'),
                $attendance->check_in ?? '-',
                $attendance->check_out ?? '-',
                $attendance->status,
            ]);
        }

        fclose($handle);
    }, 200, $headers);
}
}