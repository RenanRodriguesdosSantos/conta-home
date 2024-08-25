<?php

namespace App\Filament\Resources\ExpenseResource\Widgets;

use App\Models\Expense;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class ExpensesChart extends ChartWidget
{
    protected static ?string $heading = 'Despesas';
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';
    public ?string $filter = '2024';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            '2024' => '2024',
            '2025' => '2025',
        ];
    }

    protected function getData(): array
    {
        $firstDay = new Carbon("{$this->filter}-01-01");
        $lastDay = new Carbon("{$this->filter}-12-31");

        $data = Trend::query(Expense::query())
            ->between($firstDay, $lastDay)
            ->perMonth()
            ->sum('total');

        return [
            'datasets' => [
                [
                    'label' => 'Despesas',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'green',
                    'borderColor' => 'green'
                ]
            ],
            'labels' => [
                'Jan',
                'Fev',
                'Mar',
                'Abr',
                'Mai',
                'Jun',
                'Jul',
                'Ago',
                'Set',
                'Out',
                'Nov',
                'Dez',
            ],
        ];
    }
}
