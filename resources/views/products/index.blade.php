@extends('layouts.app')

@section('title', 'Товары')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="mb-0">Товары</h4>
        @if(Auth::user()->role === 'manager')
            <a href="{{ route('products.create') }}" class="btn btn-primary">Добавить товар</a>
        @endif
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-auto">
                <input type="text" name="search" class="form-control" placeholder="Поиск по наименованию"
                       value="{{ request('search') }}">
            </div>
            <div class="col-auto">
                <select name="category" class="form-select">
                    <option value="">Все категории</option>
                    <option value="Для дома" {{ request('category') === 'Для дома' ? 'selected' : '' }}>Для дома</option>
                    <option value="Для автомобиля" {{ request('category') === 'Для автомобиля' ? 'selected' : '' }}>Для автомобиля</option>
                </select>
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
                        <th>Наименование</th>
                        <th>Категория</th>
                        <th>Себестоимость</th>
                        <th>Цена продажи</th>
                        <th>Остаток</th>
                        <th>Статус</th>
                        @if(Auth::user()->role === 'manager')
                            <th>Действия</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category }}</td>
                            <td>{{ number_format($product->cost_price, 2) }} ₽</td>
                            <td>{{ number_format($product->sale_price, 2) }} ₽</td>
                            <td>{{ $product->stock }}</td>
                            <td>{{ $product->status === 'active' ? 'Активен' : 'Неактивен' }}</td>
                            @if(Auth::user()->role === 'manager')
                                <td>
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Редактировать</a>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Удалить товар?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Удалить</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->role === 'manager' ? 8 : 7 }}" class="text-center">Нет товаров</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">{{ $products->links() }}</div>
    </div>
</div>
@endsection
