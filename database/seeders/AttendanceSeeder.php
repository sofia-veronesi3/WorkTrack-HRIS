<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = User::whereIn('role', ['employee'])->get();

        $statuses = [
            'hadir' => 70,
            'terlambat' => 15,
            'cuti' => 5,
            'sakit' => 5,
            'alpha' => 5,
        ];

        $periods = [
            [Carbon::now()->startOfMonth(), Carbon::now()],
            [Carbon::now()->subMonth(1)->startOfMonth(), Carbon::now()->subMonth(1)->endOfMonth()],
        ];

        foreach ($periods as [$start, $end]) {
            Attendance::whereDate('attendance_date', '>=', $start)
                ->whereDate('attendance_date', '<=', $end)
                ->delete();

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                if ($date->isWeekend()) {
                    continue;
                }

                foreach ($employees as $employee) {
                    $status = $this->pickStatus($statuses);

                    $checkIn = null;
                    $checkOut = null;
                    $lateMinutes = 0;

                    if ($status === 'hadir') {
                        $checkIn = '08:00:00';
                        $checkOut = '17:00:00';
                    } elseif ($status === 'terlambat') {
                        $lateMinutes = rand(10, 90);
                        $checkIn = Carbon::parse('08:00:00')->addMinutes($lateMinutes)->format('H:i:s');
                        $checkOut = '17:00:00';
                    } elseif ($status === 'cuti' || $status === 'sakit') {
                        $checkIn = null;
                        $checkOut = null;
                    } // alpha: sin check-in/out queda con los valores iniciales q declare

                    Attendance::create([
                        'user_id' => $employee->id,
                        'attendance_date' => $date->toDateString(),
                        'status' => $status,
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'late_minutes' => $lateMinutes,
                    ]);
                }
            }
        }
    }

    private function pickStatus(array $statuses): string
    {
        $total = array_sum($statuses);
        $rand = rand(1, $total);

        foreach ($statuses as $status => $weight) {
            if ($rand <= $weight) {
                return $status;
            }
            $rand -= $weight;
        }

        return 'hadir';
    }
}
