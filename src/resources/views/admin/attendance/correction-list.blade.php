@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/correction-list.css') }}" />
@endsection

@section('content')
<div class="all__wrapper">
    <h2 class="correction-list__title">申請一覧</h2>

    <div class="tabs">
        <a href="{{ route('attendance.correction-list', ['status' => 0]) }}" class="tab {{ $status == 0 ? 'active' : '' }}">承認待ち</a>
        <a href="{{ route('attendance.correction-list', ['status' => 1]) }}" class="tab {{ $status == 1 ? 'active' : '' }}">承認済み</a>
    </div>

    <table class="correction-list__container">
        <tr class="correction-list__row-label">
            <th class="correction-list__label">状態</th>
            <th class="correction-list__label">名前</th>
            <th class="correction-list__label">対象日時</th>
            <th class="correction-list__label">申請理由</th>
            <th class="correction-list__label">申請日時</th>
            <th class="correction-list__label">詳細</th>
        </tr>
        @forelse($corrections as $correction)
        <tr class="correction-list__row-item">
            <td class="correction-list__item">
                @if($correction->status === 0)
                    <span class="badge">承認待ち</span>
                @elseif($correction->status === 1)
                    <span class="badge">承認済み</span>
                @endif
            </td>
            <td class="correction-list__item">{{ $correction->user?->name ?? '' }}</td>
            <td class="correction-list__item correction-list__item--date">
                @if($correction->date)
                    {{ $correction->displayDate }}
                @endif
            </td>
            <td class="correction-list__item">{{ $correction->remark ?? '-' }}</td>
            <td class="correction-list__item correction-list__item--date">{{ $correction->created_at->format('Y/m/d') }}</td>
            <td class="correction-list__item">
                <form method="get" action="{{ route('admin.correction-approval.show', ['attendance_correct_request_id' => $correction->id]) }}">
                    <button type="submit" class="correction-list__button-detail">詳細</button>
                </form>
            </td>
        </tr>
        @empty
        <tr class="correction-list__row-message">
            <td colspan="6" class="correction-list__message">
                @if($status === 0)
                    承認待ち申請はありません
                @elseif($status === 1)
                    承認済み申請はありません
                @endif
            </td>
        </tr>
        @endforelse
    </table>

    <div class="pagination">
        {{ $corrections->appends(request()->query())->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection