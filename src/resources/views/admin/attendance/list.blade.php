@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}" />
@endsection

@section('content')
<div class="all__wrapper">
    <h2 class="attendance-list__title">{{ $currentDate->format('Y年m月d日') }}の勤怠</h2>

    <!-- 月次ナビゲーション -->
    <div class="date-navigation__container">
        <a class="date-navigation__button-back" href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}">
            <img class="date-navigation__image-left" src="{{ asset('images/left.png') }}" alt="left">
            <span class="date-navigation__label-back">前日</span>
        </a>
        <div>
            <img class="date-navigation__image-calendar" src="{{ asset('images/calendar.png') }}" alt="calendar">
            <span class="date-navigation__current-date">{{ $currentDate->format('Y/m/d') }}</span>
        </div>
        <a class="date-navigation__button-next" href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}">
            <span class="date-navigation__label-next">翌日</span>
            <img class="date-navigation__image-right" src="{{ asset('images/right.png') }}" alt="right">
        </a>
    </div>

    <!-- 月次勤怠一覧 -->
    <table class="attendance-list__container">
        <tr class="attendance-list__row-label">
            <th class="attendance-list__label">名前</th>
            <th class="attendance-list__label">出勤</th>
            <th class="attendance-list__label">退勤</th>
            <th class="attendance-list__label">休憩</th>
            <th class="attendance-list__label">合計</th>
            <th class="attendance-list__label">詳細</th>
        </tr>
        @foreach($attendances as $attendance)
        <tr class="attendance-list__row-item">
            <!-- 名前 -->
            <td class="attendance-list__item">
                {{ $attendance->user->name }}
            </td>

            <!-- 出勤 -->
            <td class="attendance-list__item">
                {{ $attendance->clock_in_formatted ?: '' }}
            </td>

            <!-- 退勤 -->
            <td class="attendance-list__item">
                {{ $attendance->clock_out_formatted ?: '' }}
            </td>

            <!-- 休憩 -->
            <td class="attendance-list__item">
                @if($attendance->rests->isNotEmpty())
                    {{ preg_replace('/^0(\d:)/', '$1', $attendance->current_rest_duration) }}
                @endif
            </td>

            <!-- 合計 -->
            <td class="attendance-list__item">
                @if($attendance->clock_in_formatted && $attendance->clock_out_formatted)
                    {{ $attendance->work_duration }}
                @endif
            </td>

            <!-- 詳細ボタン -->
            <td class="attendance-list__item">
                <form method="get" action="{{ $attendance->detailRoute }}">
                    @if(!$attendance->exists)
                        <input type="hidden" name="date" value="{{ $attendance->hiddenDate }}">
                        <input type="hidden" name="user_id" value="{{ $attendance->user->id }}">
                    @endif
                    <button type="submit" class="attendance-list__button-detail">詳細</button>
                </form>
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection
