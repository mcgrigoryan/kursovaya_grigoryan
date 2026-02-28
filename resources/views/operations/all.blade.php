@extends('layouts.app')

@section('title', 'Все операции')

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Все операции</h4>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-auto">
                <select name="type" class="form-select">
                    <option value="">Все типы</option>
                    <option value="Производство" {{ request('type') === 'Производство' ? 'selected' : '' }}>Производство</option>
                    <option value="Закупка" {{ request('type') === 'Закупка' ? 'selected' : '' }}>Закупка</option>
                    <option value="Продажа" {{ request('type') === 'Продажа' ? 'selected' : '' }}>Продажа</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="product_id" class="form-select">
                    <option value="">Все товары</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="С">
            </div>
            <div class="col-auto">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="По">
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
                        <th>Дата операции</th>
                        <th>Тип</th>
                        <th>Товар</th>
                        <th>Количество</th>
                        <th>Пользователь</th>
                        <th>Дата создания</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operations as $op)
                        <tr>
                            <td>{{ $op->id }}</td>
                            <td>{{ $op->operation_date->format('d.m.Y') }}</td>
                            <td>{{ $op->type }}</td>
                            <td>{{ $op->product->name ?? '-' }}</td>
                            <td>{{ $op->quantity }}</td>
                            <td>{{ $op->user->full_name ?? $op->user->login ?? '-' }}</td>
                            <td>{{ $op->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Нет операций</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">{{ $operations->links() }}</div>
    </div>
</div>
@endsection
