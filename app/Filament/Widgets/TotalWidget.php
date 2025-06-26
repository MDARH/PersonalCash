<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Widget to display the total amount of loans given
 * Shows the sum of all transactions with type 'loan_given'
 */
class TotalWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 1;
    // Set widget to span 6 columns for a wider layout
    protected int | string | array $columnSpan = 8;

    protected function getStats(): array
    {
        // Calculate total income amount
        $totalIncome = Transaction::query()
            ->where('type', 'income')
            ->sum('amount');

        // Calculate total expense amount
        $totalExpense = Transaction::query()
            ->where('type', 'expense')
            ->sum('amount');

        // Calculate total loan given amount
        $totalLoanGiven = Transaction::query()
            ->where('type', 'loan_given')
            ->sum('amount');

        // Calculate total loan taken amount
        $totalLoanTaken = Transaction::query()
            ->where('type', 'loan_taken')
            ->sum('amount');

        // Calculate current balance (income - expense)
        $currentBalance = $totalIncome - $totalExpense;

        // Format the amounts with Bengali currency symbol
        $formattedCurrentBalance = '৳ ' . number_format($currentBalance, 2);
        $formattedTotalExpense = '৳ ' . number_format($totalExpense, 2);
        $formattedLoanGiven = '৳ ' . number_format($totalLoanGiven, 2);
        $formattedLoanTaken = '৳ ' . number_format($totalLoanTaken, 2);

        return [
            Stat::make('বর্তমান ব্যালেন্স', $formattedCurrentBalance)
                ->description('আয় - খরচ = বর্তমান ব্যালেন্স')
                ->descriptionIcon($currentBalance >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($currentBalance >= 0 ? 'success' : 'danger'),
            Stat::make('মোট খরচ', $formattedTotalExpense)
                ->description('সর্বমোট খরচের পরিমাণ')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('মোট ঋণ প্রদান', $formattedLoanGiven)
                ->description('সর্বমোট ঋণ প্রদানের পরিমাণ')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('warning'),
            Stat::make('মোট ঋণ গ্রহণ', $formattedLoanTaken)
                ->description('সর্বমোট ঋণ গ্রহণের পরিমাণ')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
        ];
    }
}
