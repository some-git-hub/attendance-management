@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/staff/list.css') }}" />
@endsection

@section('content')
<div class="all__wrapper">
    <h2 class="staff-list__title">スタッフ一覧</h2>

    <table class="staff-list__container">
        <tr class="staff-list__row-label">
            <th class="staff-list__label">名前</th>
            <th class="staff-list__label">メールアドレス</th>
            <th class="staff-list__label">月次勤怠</th>
        </tr>
        @foreach ($users as $user)
            <tr class="staff-list__row-item">
                <!-- 名前 -->
                <td class="staff-list__item">
                    {{ $user->name }}
                </td>

                <!-- メールアドレス -->
                <td class="staff-list__item">
                    {{ $user->email }}
                </td>

                <!-- 月次勤怠（詳細ボタン） -->
                <td class="staff-list__item">
                    <form method="get" action="{{ route('admin.staff-attendance.list', $user->id) }}">
                        <button type="submit" class="staff-list__button-detail">詳細</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>

    <!-- ページネーション -->
    <div class="pagination">
        {{ $users->appends(request()->query())->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection
