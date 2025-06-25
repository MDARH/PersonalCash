<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Widget to display the total amount of loans taken
 * Shows the sum of all transactions with type 'loan_taken'
 */
class TotalLoanTakenWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';
    
    protected function getStats(): array
    {
        // Calculate total loan taken amount
        $totalLoanTaken = Transaction::query()
            ->where('type', 'loan_taken')
            ->sum('amount');
            
        // Format the amount with Bengali currency symbol
        $formattedAmount = '৳ ' . number_format($totalLoanTaken, 2);
        
        return [
            Stat::make('মোট ঋণ গ্রহণ', $formattedAmount)
                ->description('সর্বমোট ঋণ গ্রহণের পরিমাণ')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'), // Using the same color as defined in TransactionType enum
        ];
    }
}