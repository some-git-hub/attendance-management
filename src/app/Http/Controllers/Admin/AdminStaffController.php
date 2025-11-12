<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffController extends Controller
{
    /**
     * スタッフ一覧の表示
     */
    public function index()
    {
        $users = User::where('role', 0)->paginate(10);

        return view('admin.staff.list', compact('users'));
    }


    /**
     * スタッフ別月次勤怠一覧の表示
     */
    public function attendances(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $targetMonth = $request->query('month', now()->format('Y-m'));
        [$year, $month] = explode('-', $targetMonth);

        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with('rests')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->date)->toDateString());

        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $currentMonth = Carbon::parse("{$year}-{$month}-01");
        $lastDay = $currentMonth->copy()->endOfMonth()->day;

        $attendancesForMonth = collect();

        for ($day = 1; $day <= $lastDay; $day++) {
            $date = Carbon::create($year, $month, $day)->toDateString();

            $attendance = $attendances->get($date) ?? new Attendance([
                'user_id' => $user->id,
                'date'    => $date,
            ]);
            $attendance->exists = $attendances->has($date);

            $carbonDate = Carbon::parse($date);
            $attendance->formatted_date = $carbonDate->format('m/d') . ' (' . $weekdays[$carbonDate->dayOfWeek] . ')';
            $attendance->hiddenDate = $date;

            $attendance->detailRoute = route('admin.attendance.show', [
                'id'   => $attendance->exists ? $attendance->id : 'new',
                'date' => $date,
            ]);

            $attendancesForMonth->push($attendance);
        }

        return view('admin.staff.attendance-list', [
            'user'         => $user,
            'attendances'  => $attendancesForMonth,
            'currentMonth' => $currentMonth,
            'prevMonth'    => $currentMonth->copy()->subMonth()->format('Y-m'),
            'nextMonth'    => $currentMonth->copy()->addMonth()->format('Y-m'),
        ]);
    }


    /**
     * 各スタッフの月次勤怠一覧の CSV 出力機能
     */
    public function exportCsv($id, Request $request)
    {
        $month = $request->query('month')
            ? Carbon::parse($request->query('month'))
            : Carbon::now();

        $user = User::findOrFail($id);

        $attendances = Attendance::with('rests')
            ->where('user_id', $user->id)
            ->whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->get()
            ->mapWithKeys(function ($attendance) {
                return [Carbon::parse($attendance->date)->format('Y-m-d') => $attendance];
            });

        $csvHeader = ['日付', '出勤', '退勤', '休憩', '合計'];
        $csvData = [];

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth   = $month->copy()->endOfMonth();

        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $attendance = $attendances->get($dateKey);

            if ($attendance) {
                $clockIn  = $attendance->clock_in ? Carbon::parse($attendance->clock_in) : null;
                $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out) : null;

                $rest = '';
                $work = '';

                if ($clockIn && $clockOut) {
                    $totalMinutes = $clockOut->diffInMinutes($clockIn);

                    $restMinutes = $attendance->rests->sum(function ($rest) {
                        if ($rest->rest_in && $rest->rest_out) {
                            return Carbon::parse($rest->rest_out)->diffInMinutes(Carbon::parse($rest->rest_in));
                        }
                        return 0;
                    });

                    $rest = sprintf('%d:%02d', floor($restMinutes / 60), $restMinutes % 60);
                    $workMinutes = max(0, $totalMinutes - $restMinutes);
                    $work = sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60);
                }

                $csvData[] = [
                    $date->format('Y/m/d'),
                    $clockIn ? $clockIn->format('H:i') : '',
                    $clockOut ? $clockOut->format('H:i') : '',
                    $rest,
                    $work,
                ];
            } else {
                $csvData[] = [
                    $date->format('Y/m/d'),
                    '', '', '', '',
                ];
            }
        }

        // 出力処理
        $filename = "{$user->name}_{$month->format('Y_m')}_勤怠.csv";
        $output = fopen('php://temp', 'r+');
        fputcsv($output, $csvHeader);
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        $csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
