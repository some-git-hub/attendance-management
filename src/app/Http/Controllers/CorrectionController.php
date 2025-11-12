<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\RestCorrection;
use App\Http\Requests\AttendanceRequest;
use Carbon\Carbon;

class CorrectionController extends Controller
{
    /**
     * 修正申請の送信機能
     */
    public function update(AttendanceRequest $request, $id)
    {
        if ($id === 'new') {
            $date = Carbon::parse($request->input('date'));

            $attendance = new Attendance([
                'user_id' => Auth::id(),
                'date'    => $date->toDateString(),
                'clock_in'  => null,
                'clock_out' => null,
            ]);

            $attendance->exists = false;
        } else {
            $attendance = Attendance::with('rests')->findOrFail($id);
        }

        $attendanceCorrection = AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id'       => Auth::id(),
            'date'          => $attendance->date ?? $request->input('date'),
            'status'        => 0, // 承認待ち
            'clock_in'      => $request->input('clock_in'),
            'clock_out'     => $request->input('clock_out'),
            'remark'        => $request->input('remark'),
        ]);

        if ($request->has('rest_id')) {
            $restIds  = $request->rest_id;
            $restIns  = $request->rest_in;
            $restOuts = $request->rest_out;

            foreach ($restIds as $index => $restId) {
                $restIn  = $restIns[$index]  ?? null;
                $restOut = $restOuts[$index] ?? null;

                if (empty($restId) && empty($restIn) && empty($restOut)) {
                    continue;
                }

                RestCorrection::create([
                    'attendance_correction_id' => $attendanceCorrection->id,
                    'rest_id'                  => $restId,
                    'rest_in'                  => $restIn,
                    'rest_out'                 => $restOut,
                ]);
            }
        }

        if ($attendance->exists) {
            return redirect()->to(
                route('attendance.show', ['id' => $attendance->id])
            )->with('success', '修正申請を送信しました');
        } else {
            return redirect()->to(
                route('attendance.show', ['id' => 'new']) . '?date=' . urlencode($request->input('date'))
            )->with('success', '修正申請を送信しました');
        }
    }


    /**
     * 修正申請一覧の表示
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = (int) $request->query('status', 0);

        $query = AttendanceCorrection::with('attendance.user')->where('user_id', $user->id);

        if ($status == 0) {
            $query->where('status', 0); // 承認待ち
        } elseif ($status == 1) {
            $query->where('status', 1); // 承認済み
        }

        $corrections = $query->orderBy('created_at', 'desc')->paginate(10);
        $corrections->getCollection()->transform(function ($correction) {
            $correction->displayDate = $correction->date
                ? Carbon::parse($correction->date)->format('Y/m/d')
                : '-';
            return $correction;
        });

        return view('attendance.correction-list', compact('status', 'corrections'));
    }
}