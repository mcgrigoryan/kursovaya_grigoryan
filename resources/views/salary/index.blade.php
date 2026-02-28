@extends('layouts.app')

@section('title', 'Зарплата')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Зарплатная ведомость</h4>
        <a href="{{ route('salary.export') }}" class="btn btn-primary">Экспорт в TXT</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Должность</th>
                        <th>Оклад (руб.)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salaries as $row)
                        <tr>
                            <td>{{ $row['Должность'] }}</td>
                            <td>{{ number_format($row['Оклад']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
