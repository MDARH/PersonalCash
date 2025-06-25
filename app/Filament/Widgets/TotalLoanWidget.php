<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Widget to display the total amount of loans given
 * Shows the sum of all transactions with type 'loan_given'
 */
class TotalLoanWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 1;
    // Set widget to span 6 columns for a wider layout
    protected int | string | array $columnSpan = 6;

    protected function getStats(): array
    {
        // Calculate total loan given amount
        $totalLoanGiven = Transaction::query()
            ->where('type', 'loan_given')
            ->sum('amount');

        // Calculate total loan taken amount
        $totalLoanTaken = Transaction::query()
            ->where('type', 'loan_taken')
            ->sum('amount');

        // Format the amount with Bengali currency symbol
        $formattedGivenAmount = '৳ ' . number_format($totalLoanGiven, 2);
        $formattedTakenAmount = '৳ ' . number_format($totalLoanTaken, 2);

        return [
            Stat::make('মোট ঋণ প্রদান', $formattedGivenAmount)
                ->description('সর্বমোট ঋণ প্রদানের পরিমাণ')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('warning'), // Using the same color as defined in TransactionType enum
            Stat::make('মোট ঋণ গ্রহণ', $formattedTakenAmount)
                ->description('সর্বমোট ঋণ গ্রহণের পরিমাণ')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'), // Using the same color as defined in TransactionType enum
        ];
    }
}
