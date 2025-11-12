<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceHistory;
use App\Models\RestCorrection;
use App\Models\RestHistory;
use App\Http\Requests\AttendanceRequest;
use Carbon\Carbon;

class AdminCorrectionController extends Controller
{
    /**
     * 日次勤怠情報の管理者修正機能
     */
    public function update(AttendanceRequest $request, $id)
    {
        $isNew = ($id === 'new');

        if ($isNew) {
            $date = Carbon::parse($request->input('date'));

            $attendance = new Attendance([
                'user_id'   => $request->input('user_id'),
                'date'      => $date->toDateString(),
                'clock_in'  => $request->clock_in,
                'clock_out' => $request->clock_out,
                'remark'    => $request->remark,
            ]);

            $attendance->save();
        } else {
            $attendance = Attendance::with('rests')->findOrFail($id);
        }

        DB::transaction(function () use ($attendance, $request, $isNew) {

            $adminId = auth()->id();

            $attendanceHistory = AttendanceHistory::create([
                'attendance_id'   => $attendance->id,
                'updated_by'      => $adminId,
                'before_clock_in' => $isNew ? null : $attendance->clock_in,
                'before_clock_out'=> $isNew ? null : $attendance->clock_out,
                'before_remark'   => $isNew ? null : $attendance->remark,
                'after_clock_in'  => $request->clock_in,
                'after_clock_out' => $request->clock_out,
                'after_remark'    => $request->remark,
            ]);

            $attendanceCorrection = AttendanceCorrection::create([
                'attendance_id' => $attendance->id,
                'user_id'       => $attendance->user_id,
                'date'          => $attendance->date,
                'clock_in'      => $request->clock_in,
                'clock_out'     => $request->clock_out,
                'remark'        => $request->remark,
                'status'        => 2, // 管理者修正
            ]);

            $requestRestIds = $request->rest_id ?? [];

            foreach ($attendance->rests as $rest) {
                if (!in_array($rest->id, $requestRestIds)) {
                    RestHistory::create([
                        'attendance_history_id' => $attendanceHistory->id,
                        'rest_id'               => $rest->id,
                        'before_rest_in'        => $rest->rest_in,
                        'before_rest_out'       => $rest->rest_out,
                    ]);
                    $rest->delete();
                }
            }

            if ($request->has('rest_id')) {
                foreach ($request->rest_id as $index => $restId) {
                    $restIn  = $request->rest_in[$index] ?? null;
                    $restOut = $request->rest_out[$index] ?? null;

                    if (empty($restId) && empty($restIn) && empty($restOut)) continue;

                    $exists = RestCorrection::where('attendance_correction_id', $attendanceCorrection->id)
                        ->where('rest_id', $restId ?: null)
                        ->where('rest_in', $restIn)
                        ->where('rest_out', $restOut)
                        ->exists();

                    if (!$exists) {
                        $newRestCorrection = RestCorrection::create([
                            'attendance_correction_id' => $attendanceCorrection->id,
                            'rest_id'                  => $restId ?: null,
                            'rest_in'                  => $restIn,
                            'rest_out'                 => $restOut,
                        ]);
                    }

                    if ($restId) {
                        $rest = Rest::find($restId);

                        if ($restIn != $rest->rest_in || $restOut != $rest->rest_out) {
                            RestHistory::create([
                                'attendance_history_id' => $attendanceHistory->id,
                                'rest_id'               => $rest->id,
                                'before_rest_in'        => $rest->rest_in,
                                'before_rest_out'       => $rest->rest_out,
                                'after_rest_in'         => $restIn,
                                'after_rest_out'        => $restOut,
                            ]);
                        }

                        if ($restIn || $restOut) {
                            $rest->update([
                                'rest_in'  => $restIn,
                                'rest_out' => $restOut,
                            ]);
                        } else {
                            $rest->delete();
                        }
                    } elseif ($restIn || $restOut) {
                        $newRest = Rest::create([
                            'attendance_id' => $attendance->id,
                            'rest_in'       => $restIn,
                            'rest_out'      => $restOut,
                        ]);

                        RestHistory::create([
                            'attendance_history_id' => $attendanceHistory->id,
                            'rest_id'               => $newRest->id,
                            'after_rest_in'         => $restIn,
                            'after_rest_out'        => $restOut,
                        ]);

                        if (isset($newRestCorrection)) {
                            $newRestCorrection->update([
                                'rest_id' => $newRest->id
                            ]);
                        }
                    }
                }
            }

            $attendance->update([
                'clock_in'  => $request->clock_in,
                'clock_out' => $request->clock_out,
                'remark'    => $request->remark,
            ]);

        });

        return redirect()->route('admin.attendance.show', ['id' => $attendance->id])->with('success', '勤怠情報を修正しました');
    }


    /**
     * 修正申請一覧の表示
     */
    public function index(Request $request)
    {
        $status = (int) $request->query('status', 0);

        $query = AttendanceCorrection::with(['attendance.user'])
            ->when($status === 0, fn($q) => $q->where('status', 0))
            ->when($status === 1, fn($q) => $q->where('status', 1));

        $corrections = $query->orderBy('created_at', 'desc')->paginate(10);
        $corrections->getCollection()->transform(function ($correction) {
            $correction->displayDate = $correction->date
                ? Carbon::parse($correction->date)->format('Y/m/d')
                : '-';
            return $correction;
        });

        return view('admin.attendance.correction-list', compact('status', 'corrections'));
    }


    /**
     * 修正申請詳細の表示
     */
    public function show($attendance_correct_request_id)
    {
        $attendanceCorrection = AttendanceCorrection::with(['attendance', 'user', 'restCorrections'])->findOrFail($attendance_correct_request_id);
        $displayYear = Carbon::parse($attendanceCorrection->date)->format('Y年');
        $displayMonthDay = Carbon::parse($attendanceCorrection->date)->format('m月d日');

        $displayClockIn  = $attendanceCorrection->clock_in  ? Carbon::parse($attendanceCorrection->clock_in)->format('H:i') : '';
        $displayClockOut = $attendanceCorrection->clock_out ? Carbon::parse($attendanceCorrection->clock_out)->format('H:i') : '';
        $displayRemark   = $attendanceCorrection->remark;
        $displayRests    = [];

        foreach ($attendanceCorrection->restCorrections as $restCorrection) {
            if ($restCorrection->rest_in || $restCorrection->rest_out) {
                $displayRests[] = [
                    'id'       => null,
                    'rest_in'  => $restCorrection->rest_in  ? Carbon::parse($restCorrection->rest_in)->format('H:i')  : '',
                    'rest_out' => $restCorrection->rest_out ? Carbon::parse($restCorrection->rest_out)->format('H:i') : '',
                ];
            }
        }

        return view('admin.attendance.correction-approval', compact(
            'attendanceCorrection',
            'displayYear',
            'displayMonthDay',
            'displayClockIn',
            'displayClockOut',
            'displayRemark',
            'displayRests'
        ));
    }


    /**
     * 修正申請の承認機能
     */
    public function approve(Request $request, $attendance_correct_request_id)
    {
        $attendanceCorrection = AttendanceCorrection::with('restCorrections')->findOrFail($attendance_correct_request_id);

        if ($request->input('action') !== 'approve') {
            return redirect()->back()->with('error', '不正な操作です');
        }

        if ($attendanceCorrection->status !== 0) {
            return redirect()->back()->with('error', 'この申請はすでに処理済みです');
        }

        DB::transaction(function () use ($attendanceCorrection) {

            $adminId = auth()->id();

            $attendanceCorrection->status = 1; // 承認済み
            $attendanceCorrection->save();

            $attendance = Attendance::firstOrNew(
                [
                    'id' => $attendanceCorrection->attendance_id
                ],[
                    'user_id' => $attendanceCorrection->user_id,
                    'date'    => $attendanceCorrection->date,
                ]
            );

            $beforeClockIn  = $attendance->clock_in;
            $beforeClockOut = $attendance->clock_out;
            $beforeRemark   = $attendance->remark;

            $attendance->clock_in  = $attendanceCorrection->clock_in;
            $attendance->clock_out = $attendanceCorrection->clock_out;
            $attendance->remark    = $attendanceCorrection->remark;

            $attendance->save();
            $attendance->refresh();

            $attendanceHistory = AttendanceHistory::create([
                'attendance_id'   => $attendance->id,
                'updated_by'      => $adminId,
                'before_clock_in' => $beforeClockIn,
                'before_clock_out'=> $beforeClockOut,
                'before_remark'   => $beforeRemark,
                'after_clock_in'  => $attendanceCorrection->clock_in,
                'after_clock_out' => $attendanceCorrection->clock_out,
                'after_remark'    => $attendanceCorrection->remark,
            ]);

            if (!$attendanceCorrection->attendance_id) {
                $attendanceCorrection->attendance_id = $attendance->id;
                $attendanceCorrection->save();
            }

            $existingRestIds = Rest::where('attendance_id', $attendance->id)->pluck('id')->toArray();
            $correctionRestIds = $attendanceCorrection->restCorrections->pluck('rest_id')->filter()->toArray();

            $deleteTargetIds = array_diff($existingRestIds, $correctionRestIds);
            if (!empty($deleteTargetIds)) {
                foreach ($deleteTargetIds as $restId) {
                    $rest = Rest::find($restId);

                    if ($rest) {
                        RestHistory::create([
                            'attendance_history_id' => $attendanceHistory->id,
                            'rest_id'               => $rest->id,
                            'before_rest_in'        => $rest->rest_in,
                            'before_rest_out'       => $rest->rest_out,
                        ]);
                        $rest->delete();
                    }
                }
            }

            foreach ($attendanceCorrection->restCorrections as $restCorrection) {
                $restIn = $restCorrection->rest_in;
                $restOut = $restCorrection->rest_out;

                if (empty($restIn) && empty($restOut)) {
                    if ($restCorrection->rest_id) {
                        $rest = Rest::find($restCorrection->rest_id);

                        if ($rest) {
                            RestHistory::create([
                                'attendance_history_id' => $attendanceHistory->id,
                                'rest_id'               => $rest->id,
                                'before_rest_in'        => $rest->rest_in,
                                'before_rest_out'       => $rest->rest_out,
                            ]);
                            $rest->delete();
                        }
                    }
                    continue;
                }

                $beforeRest = $restCorrection->rest_id ? Rest::find($restCorrection->rest_id) : null;

                $rest = Rest::updateOrCreate(
                    [
                        'attendance_id' => $attendance->id,
                        'id' => $restCorrection->rest_id,
                    ],[
                        'rest_in' => $restIn,
                        'rest_out' => $restOut,
                    ]
                );

                if ($beforeRest) {
                    if ($beforeRest && ($beforeRest->rest_in !== $restIn || $beforeRest->rest_out !== $restOut)) {
                        RestHistory::create([
                            'attendance_history_id' => $attendanceHistory->id,
                            'rest_id'               => $rest->id,
                            'before_rest_in'        => $beforeRest->rest_in,
                            'before_rest_out'       => $beforeRest->rest_out,
                            'after_rest_in'         => $restIn,
                            'after_rest_out'        => $restOut,
                        ]);
                    }
                } else {
                    RestHistory::create([
                        'attendance_history_id' => $attendanceHistory->id,
                        'rest_id'               => $rest->id,
                        'after_rest_in'         => $restIn,
                        'after_rest_out'        => $restOut,
                    ]);
                }

                if (is_null($restCorrection->rest_id)) {
                    $restCorrection->update([
                        'rest_id' => $rest->id,
                    ]);
                }
            }

        });

        return redirect()->back()->with('success', '修正申請を承認しました');
    }
}
