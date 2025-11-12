<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\VerifyController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminCorrectionController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// ゲストのみアクセス可能
Route::middleware('guest')->group(function () {

    // 新規登録（一般ユーザー）
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    // ログイン（一般ユーザー）
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    // ログイン（管理者）
    Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminLoginController::class, 'store']);

});



// メール認証誘導画面
Route::get('/email/verify', [VerifyController::class, 'verifyNotice'])
    ->middleware('auth')
    ->name('verification.notice');

// メール認証リンク
Route::get('/email/verify/{id}/{hash}', [VerifyController::class, 'verify'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

// 認証メール再送信
Route::post('/email/verification-notification', [VerifyController::class, 'resend'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');



// 一般ユーザーのみアクセス可能
Route::middleware('auth')->group(function () {

    // 勤怠登録（一般ユーザー）
    Route::get('/attendance', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/rest', [AttendanceController::class, 'rest'])->name('attendance.rest');
    Route::post('/attendance/resume', [AttendanceController::class, 'resume'])->name('attendance.resume');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');

    // 勤務一覧（一般ユーザー）
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.list');

    // 勤務詳細（一般ユーザー）
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::put('/attendance/detail/{id}/update', [CorrectionController::class, 'update'])->name('attendance.update');

    // ログアウト（一般ユーザー）
    Route::post('/logout', function () {
        auth()->logout();
        return redirect('/login');
    })->name('logout');

});



// 管理者のみアクセス可能
Route::middleware(['auth', 'admin'])->group(function () {

    // 勤怠一覧（管理者）
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');

    // 勤怠詳細（管理者）
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');
    Route::put('/admin/attendance/{id}/update', [AdminCorrectionController::class, 'update'])->name('admin.attendance.update');

    // 申請承認（管理者）
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminCorrectionController::class, 'show'])->name('admin.correction-approval.show');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminCorrectionController::class, 'approve'])->name('admin.correction-approval.approve');

    // スタッフ一覧（管理者）
    Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])->name('admin.staff.list');

    // スタッフ別勤怠一覧（管理者）
    Route::get('/admin/attendance/staff/{id}', [AdminStaffController::class, 'attendances'])->name('admin.staff-attendance.list');

    // CSV 出力（管理者）
    Route::get('/admin/staff-attendance/{id}/export', [AdminStaffController::class, 'exportCsv'])->name('admin.staff-attendance.export');

    // ログアウト
    Route::post('/admin/logout', function () {
        auth()->logout();
        return redirect('/admin/login');
    })->name('admin.logout');

});



// 申請一覧（一般ユーザーおよび管理者）
Route::middleware('auth')->get('/stamp_correction_request/list', function () {

    $user = auth()->user();

    if ($user->role === 1) {
        return app(AdminCorrectionController::class)->index(request());
    } else {
        return app(CorrectionController::class)->index(request());
    }

})->name('attendance.correction-list');