<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SalaryController extends Controller
{
    public function __construct(
        private ActivityLogger $logger
    ) {}

    private function getSalaryData(): array
    {
        return [
            ['Должность' => 'Директор', 'Оклад' => 80000],
            ['Должность' => 'Менеджер', 'Оклад' => 55000],
            ['Должность' => 'Бухгалтер', 'Оклад' => 50000],
            ['Должность' => 'Мастер цеха', 'Оклад' => 45000],
            ['Должность' => 'Кладовщик', 'Оклад' => 35000],
        ];
    }

    public function index(): View
    {
        $salaries = $this->getSalaryData();
        return view('salary.index', compact('salaries'));
    }

    public function export(): Response
    {
        $this->logger->log('Экспорт зарплатной ведомости');

        $salaries = $this->getSalaryData();
        $lines = ["Зарплатная ведомость ООО «Айрус»\n", str_repeat('-', 40) . "\n"];
        foreach ($salaries as $row) {
            $lines[] = sprintf("%s — %d руб.\n", $row['Должность'], $row['Оклад']);
        }
        $lines[] = str_repeat('-', 40) . "\n";

        return response(implode('', $lines), 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="salary.txt"',
        ]);
    }
}
