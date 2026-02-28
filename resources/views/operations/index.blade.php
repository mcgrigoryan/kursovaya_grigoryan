@extends('layouts.app')

@section('title', 'Операции')

@section('content')
@if(Auth::user()->role === 'manager')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Добавить операцию</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('operations.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Тип операции</label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="">Выберите</option>
                        <option value="Производство" {{ old('type') === 'Производство' ? 'selected' : '' }}>Производство</option>
                        <option value="Закупка" {{ old('type') === 'Закупка' ? 'selected' : '' }}>Закупка</option>
                        <option value="Продажа" {{ old('type') === 'Продажа' ? 'selected' : '' }}>Продажа</option>
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Товар</label>
                    <select name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                        <option value="">Выберите товар</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                    @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Количество</label>
                    <input type="number" name="quantity" min="1" class="form-control @error('quantity') is-invalid @enderror"
                           value="{{ old('quantity') }}" required>
                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Дата операции</label>
                    <input type="date" name="operation_date" class="form-control @error('operation_date') is-invalid @enderror"
                           value="{{ old('operation_date', date('Y-m-d')) }}" required>
                    @error('operation_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Записать операцию</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Журнал операций</h4>
        <a href="{{ route('operations.all') }}" class="btn btn-outline-primary btn-sm">Все операции</a>
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
    </div>
</div>
@endsection
