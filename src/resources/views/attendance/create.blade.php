@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/create.css') }}" />
@endsection

@section('content')
<div class="all__wrapper">
    <div class="attendance-form">

        <!-- 勤怠状況 -->
        @if (!$hasAttendance)
        <p class="attendance-status">勤務外</p>
        @elseif ($rests->isNotEmpty() && $rests->last()?->rest_out === null)
        <p class="attendance-status">休憩中</p>
        @elseif ($hasAttendance && $attendance->clock_in && !$attendance->clock_out)
        <p class="attendance-status">出勤中</p>
        @elseif ($hasAttendance && $attendance->clock_out)
        <p class="attendance-status">退勤済</p>
        @endif

        <!-- 現在の日時情報 -->
        <p class="current-date">
            {{ $now->format('Y年m月d日') }}({{ $weekdays[$now->dayOfWeek] }})
        </p>
        <p class="current-time">
            {{ $now->format('H:i') }}
        </p>

        <!-- 出勤前 -->
        @if (!$hasAttendance)
        <form method="post" action="{{ route('attendance.start') }}">
            @csrf
            <button type="submit" class="button button-start">出勤</button>
        </form>

        <!-- 休憩中 -->
        @elseif ($rests->isNotEmpty() && $rests->last()?->rest_out === null)
        <form method="post" action="{{ route('attendance.resume') }}">
            @csrf
            <button type="submit" class="button button-resume">休憩戻</button>
        </form>

        <!-- 出勤中 -->
        @elseif ($hasAttendance && $attendance->clock_in && !$attendance->clock_out)
        <div class="attendance-form__inner">
            <form method="post" action="{{ route('attendance.end') }}">
                @csrf
                <button type="submit" class="button button-end">退勤</button>
            </form>
            <form method="post" action="{{ route('attendance.rest') }}">
                @csrf
                <button type="submit" class="button button-rest">休憩入</button>
            </form>
        </div>

        <!-- 退勤済 -->
        @elseif ($hasAttendance && $attendance->clock_out)
        <p class="message-end">お疲れ様でした。</p>

        @endif
    </div>
</div>
@endsection