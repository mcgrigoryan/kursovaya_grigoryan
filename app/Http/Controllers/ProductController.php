<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private ActivityLogger $logger
    ) {}

    public function index(Request $request): View
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $products = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:products,name'],
            'category' => ['required', Rule::in(['Для дома', 'Для автомобиля'])],
            'cost_price' => ['required', 'numeric', 'min:0.01'],
            'sale_price' => ['required', 'numeric', 'gt:cost_price'],
            'stock' => ['nullable', 'integer', 'min:0'],
        ], [
            'sale_price.gt' => 'Цена продажи должна быть больше себестоимости.',
        ]);

        $data['stock'] = $data['stock'] ?? 0;

        Product::create($data);

        $this->logger->log('Добавление товара', "Товар: {$data['name']}");

        return redirect()->route('products.index')->with('success', 'Товар успешно добавлен');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', Rule::in(['Для дома', 'Для автомобиля'])],
            'cost_price' => ['required', 'numeric', 'min:0.01'],
            'sale_price' => ['required', 'numeric', 'gt:cost_price'],
            'stock' => ['nullable', 'integer', 'min:0'],
        ], [
            'sale_price.gt' => 'Цена продажи должна быть больше себестоимости.',
        ]);

        $product->update($data);

        $this->logger->log('Редактирование товара', "Товар: {$product->name}");

        return redirect()->route('products.index')->with('success', 'Товар обновлен');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;

        try {
            $product->delete();
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'foreign key') || str_contains($e->getMessage(), 'a foreign key')) {
                return redirect()->route('products.index')->with('error', 'Невозможно удалить: по товару есть операции');
            }
            throw $e;
        }

        $this->logger->log('Удаление товара', "Товар: {$name}");

        return redirect()->route('products.index')->with('success', 'Товар удалён');
    }
}
