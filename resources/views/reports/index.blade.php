@extends('layouts.app')

@section('title', 'Отчеты')

@section('content')
@if(Auth::user()->role === 'manager')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Сформировать отчет</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('reports.generate') }}" class="row g-3">
            @csrf
            <div class="col-auto">
                <label class="form-label">Месяц</label>
                <select name="month" class="form-select" required>
                    @php $months = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь']; @endphp
                    @foreach($months as $i => $name)
                        <option value="{{ $i + 1 }}" {{ (int) request('month', date('n')) === $i + 1 ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label">Год</label>
                <select name="year" class="form-select" required>
                    @foreach(range(2024, date('Y') + 1) as $y)
                        <option value="{{ $y }}" {{ (int) request('year', date('Y')) === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Сформировать отчет</button>
            </div>
        </form>
    </div>
</div>
@endif

@if($reportData)
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h5 class="mb-0">Отчет за {{ $reportData['period'] ?? '' }}</h5>
        @if(Auth::user()->role === 'manager')
            <form method="POST" action="{{ route('reports.save') }}" class="d-inline">
                @csrf
                <input type="hidden" name="month" value="{{ request('month', date('n')) }}">
                <input type="hidden" name="year" value="{{ request('year', date('Y')) }}">
                <button type="submit" class="btn btn-success btn-sm">Сохранить отчет</button>
            </form>
        @endif
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Наименование</th>
                        <th>Категория</th>
                        <th>Остаток на нач.</th>
                        <th>Произведено</th>
                        <th>Закуплено</th>
                        <th>Продано</th>
                        <th>Остаток на кон.</th>
                        <th>Прибыль</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData['items'] ?? [] as $row)
                        <tr>
                            <td>{{ $row['product_name'] }}</td>
                            <td>{{ $row['category'] }}</td>
                            <td>{{ $row['opening_stock'] }}</td>
                            <td>{{ $row['produced'] }}</td>
                            <td>{{ $row['purchased'] }}</td>
                            <td>{{ $row['sold'] }}</td>
                            <td>{{ $row['closing_stock'] }}</td>
                            <td>{{ number_format($row['profit'], 2) }} ₽</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="2">ИТОГО</td>
                        <td>{{ $reportData['totals']['opening_stock'] ?? 0 }}</td>
                        <td>{{ $reportData['totals']['produced'] ?? 0 }}</td>
                        <td>{{ $reportData['totals']['purchased'] ?? 0 }}</td>
                        <td>{{ $reportData['totals']['sold'] ?? 0 }}</td>
                        <td>{{ $reportData['totals']['closing_stock'] ?? 0 }}</td>
                        <td>{{ number_format($reportData['totals']['profit'] ?? 0, 2) }} ₽</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @if(Auth::user()->role === 'manager')
            @php
                $pm = request('month', date('n'));
                $py = request('year', date('Y'));
            @endphp
            <div class="mt-3">
                <a href="{{ route('reports.export-by-period', ['month' => $pm, 'year' => $py, 'format' => 'json']) }}" class="btn btn-outline-secondary btn-sm">Экспорт в JSON</a>
                <a href="{{ route('reports.export-by-period', ['month' => $pm, 'year' => $py, 'format' => 'txt']) }}" class="btn btn-outline-secondary btn-sm">Экспорт в TXT</a>
                <a href="{{ route('reports.export-by-period', ['month' => $pm, 'year' => $py, 'format' => 'xlsx']) }}" class="btn btn-outline-secondary btn-sm">Экспорт в XLSX</a>
            </div>
        @endif
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Сохранённые отчёты</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-auto">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="Дата с">
            </div>
            <div class="col-auto">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="Дата по">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">Фильтр</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Период</th>
                        <th>Создан</th>
                        <th>Пользователь</th>
                        <th>Экспорт</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $r)
                        <tr>
                            <td>{{ $r->id }}</td>
                            <td>{{ $r->period }}</td>
                            <td>{{ $r->created_at?->format('d.m.Y H:i') }}</td>
                            <td>{{ $r->user->full_name ?? $r->user->login ?? '-' }}</td>
                            <td>
                                @if(Auth::user()->role === 'manager')
                                    <a href="{{ route('reports.export', [$r, 'json']) }}" class="btn btn-sm btn-outline-secondary">JSON</a>
                                    <a href="{{ route('reports.export', [$r, 'txt']) }}" class="btn btn-sm btn-outline-secondary">TXT</a>
                                    <a href="{{ route('reports.export', [$r, 'xlsx']) }}" class="btn btn-sm btn-outline-secondary">XLSX</a>
                                @elseif(Auth::user()->role === 'accountant')
                                    <a href="{{ route('reports.export', [$r, 'txt']) }}" class="btn btn-sm btn-outline-primary">TXT</a>
                                @else
                                    <a href="{{ route('reports.export', [$r, 'xlsx']) }}" class="btn btn-sm btn-outline-primary">XLSX</a>
                                    <a href="{{ route('reports.export', [$r, 'txt']) }}" class="btn btn-sm btn-outline-secondary">TXT</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Нет сохранённых отчётов</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-3">{{ $reports->links() }}</div>
    </div>
</div>
@endsection
