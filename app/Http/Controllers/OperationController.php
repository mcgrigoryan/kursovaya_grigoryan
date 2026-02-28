<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OperationController extends Controller
{
    public function __construct(
        private ActivityLogger $logger
    ) {}

    public function index(Request $request): View
    {
        $query = Operation::with(['product', 'user'])->orderByDesc('created_at');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('operation_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('operation_date', '<=', $request->date_to);
        }

        $operations = $query->limit(25)->get();
        $products = Product::active()->orderBy('name')->get();

        return view('operations.index', compact('operations', 'products'));
    }

    public function all(Request $request): View
    {
        $query = Operation::with(['product', 'user'])->orderByDesc('created_at');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('operation_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('operation_date', '<=', $request->date_to);
        }

        $operations = $query->paginate(20)->withQueryString();
        $products = Product::active()->orderBy('name')->get();

        return view('operations.all', compact('operations', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['Производство', 'Закупка', 'Продажа'])],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'operation_date' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($data) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);

            if ($product->status === 'inactive') {
                throw ValidationException::withMessages([
                    'product_id' => 'Нельзя провести операцию по неактивному товару.',
                ]);
            }

            if ($data['type'] === 'Продажа' && $product->stock < $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'Недостаточно товара на складе',
                ]);
            }

            if (in_array($data['type'], ['Производство', 'Закупка'], true)) {
                $product->stock += $data['quantity'];
            } else {
                $product->stock -= $data['quantity'];
            }
            $product->save();

            Operation::create([
                'type' => $data['type'],
                'product_id' => $product->id,
                'quantity' => $data['quantity'],
                'operation_date' => $data['operation_date'],
                'user_id' => Auth::id(),
            ]);

            $this->logger->log('Проведение операции',
                "Тип: {$data['type']}, Товар: {$product->name}, Количество: {$data['quantity']}");
        });

        return redirect()->route('operations.index')->with('success', 'Операция успешно записана');
    }
}
