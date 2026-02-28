<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        $data = $this->getChartData();
        return view('analytics.index', $data);
    }

    private function getChartData(): array
    {
        $sales = Operation::where('type', 'Продажа')
            ->where('operation_date', '>=', now()->subMonths(12))
            ->select(
                DB::raw('YEAR(operation_date) as year'),
                DB::raw('MONTH(operation_date) as month'),
                DB::raw('SUM(quantity) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $labels = [];
        $values = [];
        for ($i = 11; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $labels[] = $d->format('m.Y');
            $found = $sales->first(fn ($s) => (int) $s->year === (int) $d->format('Y') && (int) $s->month === (int) $d->format('n'));
            $values[] = $found ? (int) $found->total : 0;
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }
}
