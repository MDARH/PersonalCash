<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Widget to display the total amount of loans given
 * Shows the sum of all transactions with type 'loan_given'
 */
class TotalLoanGivenWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 2;
    // Set widget to span 6 columns for a wider layout
    protected int | string | array $columnSpan = 6;

    protected function getStats(): array
    {
        // Calculate total loan given amount
        $totalLoanGiven = Transaction::query()
            ->where('type', 'loan_given')
            ->sum('amount');

        // Format the amount with Bengali currency symbol
        $formattedAmount = '৳ ' . number_format($totalLoanGiven, 2);

        return [
            Stat::make('মোট ঋণ প্রদান', $formattedAmount)
                ->description('সর্বমোট ঋণ প্রদানের পরিমাণ')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('warning'), // Using the same color as defined in TransactionType enum
        ];
    }
}
