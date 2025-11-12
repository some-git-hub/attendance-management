<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    /**
     * 全スタッフの日次勤怠一覧の表示
     */
    public function index(Request $request)
    {
        $currentDate = Carbon::parse($request->query('date', now()->toDateString()));

        $prevDate = $currentDate->copy()->subDay()->toDateString();
        $nextDate = $currentDate->copy()->addDay()->toDateString();

        $users = User::where('role', '<>', 1)
            ->with(['attendances' => function ($query) use ($currentDate) {
                $query->whereDate('date', $currentDate)->with('rests');
            }])
            ->orderBy('id')
            ->get();

        $attendances = $users->map(function ($user) use ($currentDate) {
            $attendance = $user->attendances->first();

            if (!$attendance) {
                $attendance = new Attendance([
                    'user_id' => $user->id,
                    'date'    => $currentDate->toDateString(),
                ]);
            }

            $attendance->hiddenDate = $currentDate->toDateString();
            $attendance->detailRoute = route('admin.attendance.show', [
                'id'   => $attendance->exists ? $attendance->id : 'new',
                'date' => $currentDate->toDateString(),
            ]);

            return $attendance;
        });

        return view('admin.attendance.list', compact('attendances', 'currentDate', 'prevDate', 'nextDate'));
    }


    /**
     * 各スタッフの日次勤怠詳細の表示
     */
    public function show(Request $request, $id)
    {
        if ($id === 'new') {
            $user = User::findOrFail($request->query('user_id'));
            $date = Carbon::parse($request->query('date') ?? Carbon::today()->toDateString());

            $attendance = new Attendance([
                'user_id'   => $user->id,
                'date'      => $date->toDateString(),
                'clock_in'  => null,
                'clock_out' => null,
            ]);

            $attendance->exists = false;

            $attendanceCorrection = AttendanceCorrection::where('user_id', $user->id)
                ->where('attendance_id', null)
                ->whereDate('date', $date->toDateString())
                ->where('status', 0)
                ->orderBy('created_at', 'desc')
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

            return view('admin.attendance.detail', compact(
                'user',
                'attendance',
                'isLocked',
                'displayYear',
                'displayMonthDay',
                'displayClockIn',
                'displayClockOut',
                'displayRemark',
                'displayRests',
            ));
        }

        $attendance = Attendance::with('user', 'rests')->findOrFail($id);
        $user = $attendance->user;

        $attendanceCorrection = AttendanceCorrection::where('attendance_id', $attendance->id)->latest()->first();

        $isLocked = $attendanceCorrection && $attendanceCorrection->status === 0;

        $date = $attendance->date ? Carbon::parse($attendance->date) : Carbon::today();
        $displayYear = Carbon::parse($attendanceCorrection?->date ?? $date)->format('Y年');
        $displayMonthDay = Carbon::parse($attendanceCorrection?->date ?? $date)->format('m月d日');

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

        return view('admin.attendance.detail', compact(
            'user',
            'attendance',
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
