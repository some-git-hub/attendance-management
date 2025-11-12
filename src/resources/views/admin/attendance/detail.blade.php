@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}" />
@endsection

@section('content')
<div class="all__wrapper">
    @if (session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
    @endif
    <form method="post" action="{{ route('admin.attendance.update', ['id' => $attendance->exists ? $attendance->id : 'new']) }}">
        @csrf
        @method('PUT')
        <h2 class="detail-table__title">勤怠詳細</h2>
        <table class="detail-table__container">
            <tr class="detail-table__row">
                <th class="detail-table__label">名前</th>
                <td class="detail-table__item">
                    <span class="detail-table__name">{{ $user->name }}</span>
                </td>
            </tr>
            <tr class="detail-table__row">
                <th class="detail-table__label">日付</th>
                <td class="detail-table__item">
                    <span class="detail-table__year">{{ $displayYear }}</span>
                    <span class="detail-table__date">{{ $displayMonthDay }}</span>
                </td>
            </tr>
            <tr class="detail-table__row">
                <th class="detail-table__label">出勤・退勤</th>
                <td class="detail-table__item">
                    <input type="text" name="clock_in" value="{{ old('clock_in', $displayClockIn) }}" class="detail-table__input {{ $isLocked ? 'locked' : '' }}" {{ $isLocked ? 'readonly' : '' }}>
                    <span class="detail-table__wave">～</span>
                    <input type="text" name="clock_out" value="{{ old('clock_out', $displayClockOut) }}" class="detail-table__input {{ $isLocked ? 'locked' : '' }}" {{ $isLocked ? 'readonly' : '' }}>
                    @error('clock_in')
                    <div class="detail-table__error-message">
                        {{ $message }}
                    </div>
                    @enderror
                    @error('clock_out')
                    <div class="detail-table__error-message">
                        {{ $message }}
                    </div>
                    @enderror
                </td>
            </tr>
            @foreach($displayRests as $index => $rest)
            <tr class="detail-table__row">
                <th class="detail-table__label">休憩{{ $loop->iteration }}</th>
                <td class="detail-table__item">
                    <input type="hidden" name="rest_id[]" value="{{ $rest['id'] }}">
                    <input type="text" name="rest_in[]" value="{{ old('rest_in.' . $index, $rest['rest_in']) }}" class="detail-table__input {{ $isLocked ? 'locked' : '' }}" {{ $isLocked ? 'readonly' : '' }}>
                    <span class="detail-table__wave">～</span>
                    <input type="text" name="rest_out[]" value="{{ old('rest_out.' . $index, $rest['rest_out']) }}" class="detail-table__input {{ $isLocked ? 'locked' : '' }}" {{ $isLocked ? 'readonly' : '' }}>
                    @error('rest_in.'.$index)
                    <div class="detail-table__error-message">
                        {{ $message }}
                    </div>
                    @enderror
                    @error('rest_out.'.$index)
                    <div class="detail-table__error-message">
                        {{ $message }}
                    </div>
                    @enderror
                </td>
            </tr>
            @endforeach
            <tr class="detail-table__row-last">
                <th class="detail-table__label">備考</th>
                <td class="detail-table__item">
                    <textarea name="remark" class="detail-table__textarea {{ $isLocked ? 'locked' : '' }}" {{ $isLocked ? 'readonly' : '' }}>{{ old('remark', $displayRemark) }}</textarea>
                    @error('remark')
                    <div class="detail-table__error-message">
                        {{ $message }}
                    </div>
                    @enderror
                </td>
            </tr>
        </table>
        @if ($isLocked)
        <div class="message-pending">
            <span>*承認待ちのため修正はできません。</span>
        </div>
        @else
        <div class="button-area">
            @if(!$attendance->exists)
                <input type="hidden" name="date" value="{{ $attendance->date }}">
                <input type="hidden" name="user_id" value="{{ $attendance->user_id }}">
            @endif
            <button type="submit" class="button-submit">修正</button>
        </div>
        @endif
    </form>
</div>
@endsection
