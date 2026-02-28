@extends('layouts.app')

@section('title', 'Журнал действий')

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Журнал действий пользователей</h4>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-auto">
                <select name="user_id" class="form-select">
                    <option value="">Все пользователи</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->full_name ?? $u->login }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <input type="text" name="action" class="form-control" placeholder="Действие" value="{{ request('action') }}">
            </div>
            <div class="col-auto">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-auto">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">Поиск</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Дата/время</th>
                        <th>Пользователь</th>
                        <th>Действие</th>
                        <th>Детали</th>
                        <th>IP-адрес</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>{{ $log->created_at?->format('d.m.Y H:i:s') }}</td>
                            <td>{{ $log->user->full_name ?? $log->user->login ?? '-' }}</td>
                            <td>{{ $log->action }}</td>
                            <td>{{ Str::limit($log->details, 50) }}</td>
                            <td>{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Нет записей</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
