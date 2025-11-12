@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/list.css') }}" />
@endsection

@section('content')
<div class="all__wrapper">
    <h2 class="attendance-list__title">勤怠一覧</h2>

    <!-- 月次ナビゲーション -->
    <div class="month-navigation__container">
        <a class="month-navigation__button-back" href="{{ route('attendance.list', ['month' => $prevMonth]) }}">
            <img class="month-navigation__image-left" src="{{ asset('images/left.png') }}" alt="left">
            <span class="month-navigation__label-back">前月</span>
        </a>
        <div>
            <img class="month-navigation__image-calendar" src="{{ asset('images/calendar.png') }}" alt="calendar">
            <span class="month-navigation__current-month">{{ $currentMonth->format('Y/m') }}</span>
        </div>
        <a class="month-navigation__button-next" href="{{ route('attendance.list', ['month' => $nextMonth]) }}">
            <span class="month-navigation__label-next">翌月</span>
            <img class="month-navigation__image-right" src="{{ asset('images/right.png') }}" alt="right">
        </a>
    </div>

    <!-- 月次勤怠一覧 -->
    <table class="attendance-list__container">
        <tr class="attendance-list__row-label">
            <th class="attendance-list__label-date">日付</th>
            <th class="attendance-list__label-arrival">出勤</th>
            <th class="attendance-list__label-departure">退勤</th>
            <th class="attendance-list__label-rest">休憩</th>
            <th class="attendance-list__label-total">合計</th>
            <th class="attendance-list__label-detail">詳細</th>
        </tr>
        @foreach($attendances as $attendance)
        <tr class="attendance-list__row-item">
            <!-- 日付 -->
            <td class="attendance-list__item-date">
                {{ $attendance->formatted_date }}
            </td>

            <!-- 出勤 -->
            <td class="attendance-list__item-arrival">
                {{ $attendance->clock_in_formatted ?: '' }}
            </td>

            <!-- 退勤 -->
            <td class="attendance-list__item-departure">
                {{ $attendance->clock_out_formatted ?: '' }}
            </td>

            <!-- 休憩 -->
            <td class="attendance-list__item-rest">
                @if($attendance->rests->isNotEmpty())
                    {{ preg_replace('/^0(\d:)/', '$1', $attendance->current_rest_duration) }}
                @endif
            </td>

            <!-- 合計 -->
            <td class="attendance-list__item-total">
                @if($attendance->clock_in_formatted && $attendance->clock_out_formatted)
                    {{ $attendance->work_duration }}
                @endif
            </td>

            <!-- 詳細ボタン -->
            <td class="attendance-list__item-detail">
                <form method="get" action="{{ $attendance->detailRoute }}">
                    @if (!$attendance->exists)
                        <input type="hidden" name="date" value="{{ $attendance->hiddenDate }}">
                    @endif
                    <button type="submit" class="attendance-list__button-detail">詳細</button>
                </form>
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection