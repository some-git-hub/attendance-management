@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')
<div class="all__wrapper">

    <!-- 送信成功メッセージ -->
    @if (session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>

    <!-- 送信失敗メッセージ -->
    @elseif (session('error'))
    <div class="alert-error">
        {{ session('error') }}
    </div>
    @endif

    <!-- 申請内容 -->
    <h2 class="detail-table__title">勤怠詳細</h2>
    <table class="detail-table__container">
        <!-- 基本情報 -->
        <tr class="detail-table__row">
            <th class="detail-table__label">名前</th>
            <td class="detail-table__item">
                <span class="detail-table__name">{{ $attendanceCorrection->user->name }}</span>
            </td>
        </tr>
        <tr class="detail-table__row">
            <th class="detail-table__label">日付</th>
            <td class="detail-table__item">
                <span class="detail-table__year">{{ $displayYear }}</span>
                <span class="detail-table__date">{{ $displayMonthDay }}</span>
            </td>
        </tr>

        <!-- 修正内容 -->
        <tr class="detail-table__row">
            <th class="detail-table__label">出勤・退勤</th>
            <td class="detail-table__item">
                <input type="text" name="clock_in" value="{{ $displayClockIn }}" class="detail-table__input readonly" readonly>
                <span class="detail-table__wave">～</span>
                <input type="text" name="clock_out" value="{{ $displayClockOut }}" class="detail-table__input readonly" readonly>
            </td>
        </tr>
        @foreach($displayRests as $index => $rest)
        <tr class="detail-table__row">
            <th class="detail-table__label">休憩{{ $loop->iteration }}</th>
            <td class="detail-table__item">
                <input type="hidden" name="rest_id[]" value="{{ $rest['id'] }}">
                <input type="text" name="rest_in[]" value="{{ $rest['rest_in'] }}" class="detail-table__input readonly" readonly>
                <span class="detail-table__wave">～</span>
                <input type="text" name="rest_out[]" value="{{ $rest['rest_out'] }}" class="detail-table__input readonly" readonly>
            </td>
        </tr>
        @endforeach
        <tr class="detail-table__row-last">
            <th class="detail-table__label">備考</th>
            <td class="detail-table__item">
                <textarea class="detail-table__textarea readonly" readonly>{{ $displayRemark }}</textarea>
            </td>
        </tr>
    </table>

    <!-- 承認ボタン -->
    <form method="post" action="{{ route('admin.correction-approval.approve', ['attendance_correct_request_id' => $attendanceCorrection->id]) }}">
        @csrf
        <div class="button-area">
            @if ($attendanceCorrection->status === 0)
            <button type="submit" name="action" value="approve" class="button-submit">承認</button>
            @elseif ($attendanceCorrection->status === 1)
            <button type="submit" name="action" value="approve" class="button-disable" disabled>承認済み</button>
            @endif
        </div>
    </form>
</div>
@endsection