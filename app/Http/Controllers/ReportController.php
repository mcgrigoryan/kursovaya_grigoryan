<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\Product;
use App\Models\Report;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private ActivityLogger $logger
    ) {}

    public function index(Request $request): View
    {
        $reportData = null;
        $query = Report::with('user')->orderByDesc('created_at');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $reports = $query->paginate(10)->withQueryString();

        if ($request->filled('month') && $request->filled('year')) {
            $reportData = $this->generateReportData((int) $request->year, (int) $request->month);
        }

        return view('reports.index', compact('reportData', 'reports'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $request->validate([
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2020', 'max:' . (date('Y') + 1)],
        ]);

        return redirect()->route('reports.index', [
            'month' => $request->month,
            'year' => $request->year,
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $request->validate([
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2020', 'max:' . (date('Y') + 1)],
        ]);

        $reportData = $this->generateReportData((int) $request->year, (int) $request->month);
        $period = sprintf('%04d-%02d', $request->year, $request->month);

        Report::create([
            'type' => 'monthly_report',
            'period' => $period,
            'user_id' => Auth::id(),
            'data' => $reportData,
        ]);

        $this->logger->log('Формирование отчета', "Период: {$period}");

        return redirect()->route('reports.index')->with('success', 'Отчет сохранён');
    }

    public function exportByPeriod(Request $request): Response|StreamedResponse
    {
        $request->validate([
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2020', 'max:' . (date('Y') + 1)],
            'format' => ['required', 'in:json,txt,xlsx'],
        ]);

        $role = Auth::user()->role;
        if ($role === 'accountant' && $request->format !== 'txt') {
            abort(403);
        }
        if ($role === 'director' && !in_array($request->format, ['xlsx', 'txt'])) {
            abort(403);
        }

        $reportData = $this->generateReportData((int) $request->year, (int) $request->month);
        $period = $reportData['period'];
        $format = $request->format;
        $filename = "report_{$period}.{$format}";

        $this->logger->log('Экспорт отчета', "Период: {$period}, Формат: " . strtoupper($format));

        return match ($format) {
            'json' => response()->streamDownload(
                function () use ($reportData) {
                    echo json_encode($reportData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                },
                $filename,
                ['Content-Type' => 'application/json']
            ),
            'txt' => $this->exportTxt($reportData, $period),
            'xlsx' => $this->exportXlsx($reportData, $period),
            default => abort(404),
        };
    }

    public function export(Report $report, string $format): Response|StreamedResponse
    {
        $allowedFormats = ['json', 'txt', 'xlsx'];
        if (!in_array(strtolower($format), $allowedFormats)) {
            abort(404);
        }

        $role = Auth::user()->role;
        if ($role === 'accountant' && strtolower($format) !== 'txt') {
            abort(403);
        }
        if ($role === 'director' && !in_array(strtolower($format), ['xlsx', 'txt'])) {
            abort(403);
        }

        $data = $report->data;
        $period = $report->period;
        $filename = "report_{$period}.{$format}";

        $this->logger->log('Экспорт отчета', "Период: {$period}, Формат: " . strtoupper($format));

        return match (strtolower($format)) {
            'json' => response()->streamDownload(
                function () use ($data) {
                    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                },
                $filename,
                ['Content-Type' => 'application/json']
            ),
            'txt' => $this->exportTxt($data, $period),
            'xlsx' => $this->exportXlsx($data, $period),
            default => abort(404),
        };
    }

    private function generateReportData(int $year, int $month): array
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = date('Y-m-t', strtotime($start));

        $products = Product::all();
        $items = [];
        $totals = [
            'opening_stock' => 0,
            'produced' => 0,
            'purchased' => 0,
            'sold' => 0,
            'closing_stock' => 0,
            'profit' => 0,
        ];

        foreach ($products as $product) {
            $ops = Operation::where('product_id', $product->id)
                ->whereBetween('operation_date', [$start, $end])
                ->get();

            $produced = $ops->where('type', 'Производство')->sum('quantity');
            $purchased = $ops->where('type', 'Закупка')->sum('quantity');
            $sold = $ops->where('type', 'Продажа')->sum('quantity');

            $closing = $product->stock;
            $opening = $closing - $produced - $purchased + $sold;
            $profit = $sold * ((float) $product->sale_price - (float) $product->cost_price);

            $items[] = [
                'product_name' => $product->name,
                'category' => $product->category,
                'opening_stock' => (int) $opening,
                'produced' => $produced,
                'purchased' => $purchased,
                'sold' => $sold,
                'closing_stock' => (int) $closing,
                'profit' => round($profit, 2),
            ];

            $totals['opening_stock'] += (int) $opening;
            $totals['produced'] += $produced;
            $totals['purchased'] += $purchased;
            $totals['sold'] += $sold;
            $totals['closing_stock'] += (int) $closing;
            $totals['profit'] += round($profit, 2);
        }

        return [
            'period' => sprintf('%04d-%02d', $year, $month),
            'items' => $items,
            'totals' => $totals,
        ];
    }

    private function exportTxt(array $data, string $period): Response
    {
        $lines = ["Отчет за период {$period}\n"];
        $lines[] = str_repeat('-', 80) . "\n";
        foreach ($data['items'] ?? [] as $row) {
            $lines[] = sprintf(
                "%s | %s | Нач: %d | Пр-во: %d | Закуп: %d | Прод: %d | Кон: %d | Прибыль: %.2f\n",
                $row['product_name'],
                $row['category'],
                $row['opening_stock'],
                $row['produced'],
                $row['purchased'],
                $row['sold'],
                $row['closing_stock'],
                $row['profit']
            );
        }
        $lines[] = str_repeat('-', 80) . "\n";
        $t = $data['totals'] ?? [];
        $lines[] = sprintf("ИТОГО: Нач: %d | Пр-во: %d | Закуп: %d | Прод: %d | Кон: %d | Прибыль: %.2f\n",
            $t['opening_stock'] ?? 0, $t['produced'] ?? 0, $t['purchased'] ?? 0,
            $t['sold'] ?? 0, $t['closing_stock'] ?? 0, $t['profit'] ?? 0);

        return response(implode('', $lines), 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="report_' . $period . '.txt"',
        ]);
    }

    private function exportXlsx(array $data, string $period): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Отчет');

        $headers = ['Наименование', 'Категория', 'Остаток на нач.', 'Произведено', 'Закуплено', 'Продано', 'Остаток на кон.', 'Прибыль'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '1', $h);
            $col++;
        }

        $row = 2;
        foreach ($data['items'] ?? [] as $item) {
            $sheet->setCellValue('A' . $row, $item['product_name']);
            $sheet->setCellValue('B' . $row, $item['category']);
            $sheet->setCellValue('C' . $row, $item['opening_stock']);
            $sheet->setCellValue('D' . $row, $item['produced']);
            $sheet->setCellValue('E' . $row, $item['purchased']);
            $sheet->setCellValue('F' . $row, $item['sold']);
            $sheet->setCellValue('G' . $row, $item['closing_stock']);
            $sheet->setCellValue('H' . $row, $item['profit']);
            $row++;
        }

        $t = $data['totals'] ?? [];
        $sheet->setCellValue('A' . $row, 'ИТОГО');
        $sheet->setCellValue('C' . $row, $t['opening_stock'] ?? 0);
        $sheet->setCellValue('D' . $row, $t['produced'] ?? 0);
        $sheet->setCellValue('E' . $row, $t['purchased'] ?? 0);
        $sheet->setCellValue('F' . $row, $t['sold'] ?? 0);
        $sheet->setCellValue('G' . $row, $t['closing_stock'] ?? 0);
        $sheet->setCellValue('H' . $row, $t['profit'] ?? 0);

        $filename = "report_{$period}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
