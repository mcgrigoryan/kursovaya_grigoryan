@extends('layouts.app')

@section('title', 'Редактировать товар')

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Редактировать товар</h4>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('products.update', $product) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Наименование</label>
                <input type="text" id="name" class="form-control" value="{{ $product->name }}" disabled>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Категория</label>
                <select id="category" name="category" class="form-select @error('category') is-invalid @enderror" required>
                    <option value="Для дома" {{ old('category', $product->category) === 'Для дома' ? 'selected' : '' }}>Для дома</option>
                    <option value="Для автомобиля" {{ old('category', $product->category) === 'Для автомобиля' ? 'selected' : '' }}>Для автомобиля</option>
                </select>
                @error('category')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="cost_price" class="form-label">Себестоимость</label>
                <input type="number" id="cost_price" name="cost_price" step="0.01" min="0.01"
                       class="form-control @error('cost_price') is-invalid @enderror"
                       value="{{ old('cost_price', $product->cost_price) }}" required>
                @error('cost_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="sale_price" class="form-label">Цена продажи</label>
                <input type="number" id="sale_price" name="sale_price" step="0.01" min="0.01"
                       class="form-control @error('sale_price') is-invalid @enderror"
                       value="{{ old('sale_price', $product->sale_price) }}" required>
                @error('sale_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="stock" class="form-label">Остаток</label>
                <input type="number" id="stock" name="stock" min="0" class="form-control @error('stock') is-invalid @enderror"
                       value="{{ old('stock', $product->stock) }}">
                @error('stock')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Обновить</button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
    </div>
</div>
@endsection
