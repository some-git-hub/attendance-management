<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceCorrection;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     *  勤怠登録画面の表示
     */
    public function create()
    {
        $now = Carbon::now();
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];

        $attendance = Attendance::where('user_id', Auth::id())
        ->where('date', Carbon::today())
        ->with('rests')
        ->first();

        $hasAttendance = $attendance && $attendance->exists;
        $rests = $hasAttendance ? $attendance->rests : collect();

        return view('attendance.create', compact('now', 'weekdays', 'attendance', 'hasAttendance', 'rests'));
    }


    /**
     *  出勤処理
     */
    public function start(Request $request)
    {
        $attendance = Attendance::firstOrCreate(
            [
                'user_id'   => Auth::id(),
                'date'      => Carbon::today(),
            ],[
                'clock_in'  => Carbon::now()->format('H:i:s'),
                'clock_out' => null,
            ]
        );

        if ($attendance->clock_in === null) {
            $attendance->update(['clock_in' => Carbon::now()->format('H:i:s')]);
        }

        return redirect()->route('attendance.create');
    }


    /**
     *  休憩入処理
     */
    public function rest()
    {
        $attendance = Attendance::firstOrCreate(
            [
                'user_id'   => Auth::id(),
                'date'      => Carbon::today(),
            ],[
                'clock_in'  => null,
                'clock_out' => null,
            ]
        );

        Rest::create([
            'attendance_id'  => $attendance->id,
            'rest_in'        => Carbon::now()->format('H:i:s'),
        ]);

        return redirect()->route('attendance.create');
    }


    /**
     *  休憩戻処理
     */
    public function resume()
    {
        $attendance = Attendance::firstOrCreate(
            [
                'user_id'   => Auth::id(),
                'date'      => Carbon::today(),
            ],[
                'clock_in'  => null,
                'clock_out' => null,
            ]
        );

        $rest = Rest::where('attendance_id', $attendance->id)->whereNull('rest_out')->first();

        if ($rest && !$rest->rest_out) {
            $rest->update([
                'rest_out' => Carbon::now()->format('H:i:s'),
            ]);
        }

        return redirect()->route('attendance.create');
    }


    /**
     *  退勤処理
     */
    public function end()
    {
        $attendance = Attendance::where('user_id', Auth::id())->where('date', Carbon::today())->first();
        $attendance->update([
            'clock_out' => Carbon::now()->format('H:i:s'),
        ]);

        return redirect()->route('attendance.create');
    }


    /**
     *  勤怠一覧画面の表示
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        [$year, $month] = explode('-', $request->query('month', now()->format('Y-m')));

        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with('rests')
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

            $attendance->detailRoute = route('attendance.show', [
                'id'   => $attendance->exists ? $attendance->id : 'new',
                'date' => $date,
            ]);

            $attendancesForMonth->push($attendance);
        }

        return view('attendance.list', [
            'attendances'  => $attendancesForMonth,
            'currentMonth' => $currentMonth,
            'prevMonth'    => $currentMonth->copy()->subMonth()->format('Y-m'),
            'nextMonth'    => $currentMonth->copy()->addMonth()->format('Y-m'),
        ]);
    }


    /**
     *  勤怠詳細画面の表示
     */
    public function show(Request $request, $id)
    {
        $user = Auth::user();

        if ($id === 'new') {
            $date = Carbon::parse($request->query('date') ?? Carbon::today()->toDateString());

            $existingAttendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $date->toDateString())
                ->first();

            if ($existingAttendance) {
                return redirect()->route('attendance.show', ['id' => $existingAttendance->id]);
            }

            $attendance = new Attendance([
                'user_id' => $user->id,
                'date' => $date->toDateString(),
                'clock_in' => null,
                'clock_out' => null,
            ]);

            $attendance->exists = false;

            $attendanceCorrection = AttendanceCorrection::where('user_id', $user->id)
                ->where('attendance_id', null)
                ->whereDate('date', $date->toDateString())
                ->where('status', 0)
                ->latest()
                ->first();

            $isLocked = $attendanceCorrection && $attendanceCorrection->status === 0;

            $displayYear = $attendanceCorrection
                ? Carbon::parse($attendanceCorrection->date)->format('Y年')
                : $date->format('Y年');
            $displayMonthDay = $attendanceCorrection
                ? Carbon::parse($attendanceCorrection->date)->format('m月d日')
                : $date->format('m月d日');

            $displayClockIn = $attendanceCorrection && $attendanceCorrection->clock_in
                ? Carbon::parse($attendanceCorrection->clock_in)->format('H:i')
                : '';
            $displayClockOut = $attendanceCorrection && $attendanceCorrection->clock_out
                ? Carbon::parse($attendanceCorrection->clock_out)->format('H:i')
                : '';
            $displayRemark = $attendanceCorrection ? $attendanceCorrection->remark ?? '' : '';
            $displayRests = [];

            if ($attendanceCorrection && $attendanceCorrection->status === 0 && $attendanceCorrection->restCorrections->isNotEmpty()) {
                foreach ($attendanceCorrection->restCorrections as $restCorrection) {
                    if (is_null($restCorrection->rest_id) && ($restCorrection->rest_in || $restCorrection->rest_out)) {
                        $displayRests[] = [
                            'id'       => null,
                            'rest_in'  => $restCorrection->rest_in  ? Carbon::parse($restCorrection->rest_in)->format('H:i')  : '',
                            'rest_out' => $restCorrection->rest_out ? Carbon::parse($restCorrection->rest_out)->format('H:i') : '',
                        ];
                    }
                }
            }

            if (!$isLocked) {
                $displayRests[] = [
                    'id' => null,
                    'rest_in' => '',
                    'rest_out' => '',
                ];
            }

            return view('attendance.detail', compact(
                'user',
                'attendance',
                'attendanceCorrection',
                'isLocked',
                'displayYear',
                'displayMonthDay',
                'displayClockIn',
                'displayClockOut',
                'displayRemark',
                'displayRests'
            ));
        }

        $attendance = Attendance::where('user_id', $user->id)
            ->with(['rests', 'corrections.restCorrections'])
            ->find($id);

        if (!$attendance) {
            abort(404);
        }

        $attendanceCorrection = AttendanceCorrection::where('attendance_id', $attendance->id)->latest()->first();

        $isLocked = $attendanceCorrection && $attendanceCorrection->status === 0;

        $displayYear = Carbon::parse($attendance->date)->format('Y年');
        $displayMonthDay = Carbon::parse($attendance->date)->format('m月d日');

        $displayClockIn = ($isLocked)
            ? ($attendanceCorrection->clock_in ? Carbon::parse($attendanceCorrection->clock_in)->format('H:i') : '')
            : ($attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '');
        $displayClockOut = ($isLocked)
            ? ($attendanceCorrection->clock_out ? Carbon::parse($attendanceCorrection->clock_out)->format('H:i') : '')
            : ($attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '');
        $displayRemark = ($isLocked)
            ? $attendanceCorrection->remark
            : ($attendance->remark ?? '');
        $displayRests = [];

        foreach ($attendance->rests as $rest) {
            $restCorrection = $rest->restCorrections()->latest()->first();

            $restIn  = $restCorrection ? $restCorrection->rest_in  : $rest->rest_in;
            $restOut = $restCorrection ? $restCorrection->rest_out : $rest->rest_out;

            if (empty($restIn) && empty($restOut)) continue;

            $displayRests[] = [
                'id'       => $rest->id,
                'rest_in'  => $restIn  ? Carbon::parse($restIn)->format('H:i')  : '',
                'rest_out' => $restOut ? Carbon::parse($restOut)->format('H:i') : '',
            ];
        }

        if ($isLocked && $attendanceCorrection->restCorrections->isNotEmpty()) {
            foreach ($attendanceCorrection->restCorrections as $restCorrection) {
                if (is_null($restCorrection->rest_id) && ($restCorrection->rest_in || $restCorrection->rest_out)) {
                    $displayRests[] = [
                        'id'       => null,
                        'rest_in'  => $restCorrection->rest_in  ? Carbon::parse($restCorrection->rest_in)->format('H:i')  : '',
                        'rest_out' => $restCorrection->rest_out ? Carbon::parse($restCorrection->rest_out)->format('H:i') : '',
                    ];
                }
            }
        }

        if (!$isLocked) {
            $displayRests[] = [
                'id'       => null,
                'rest_in'  => '',
                'rest_out' => ''
            ];
        }

        return view('attendance.detail', compact(
            'user',
            'attendance',
            'attendanceCorrection',
            'isLocked',
            'displayYear',
            'displayMonthDay',
            'displayClockIn',
            'displayClockOut',
            'displayRemark',
            'displayRests'
        ));
    }
}