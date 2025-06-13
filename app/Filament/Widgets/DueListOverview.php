<?php

namespace App\Filament\Widgets;

use App\Models\Contact;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;

class DueListOverview extends BaseWidget
{
    protected static ?string $heading = 'Due List';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Contact::query()
                    ->whereHas('transactions', function ($query) {
                        $query->whereIn('type', ['income', 'loan_taken', 'payment'])
                              ->orWhereIn('type', ['expense', 'loan_given']);
                    })
                    ->whereRaw('(
                        SELECT SUM(CASE
                            WHEN type IN (\'income\', \'loan_taken\', \'payment\') THEN amount
                            WHEN type IN (\'expense\', \'loan_given\') THEN -amount
                            ELSE 0
                        END)
                        FROM transactions
                        WHERE contact_id = contacts.id
                    ) != 0')
            )
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('balance')
                    ->getStateUsing(fn(Contact $record) => number_format($record->balance, 2))
                    ->color(fn($state) => $state < 0 ? 'danger' : 'success')
                    ->prefix('৳ ')
                    ->sortable(),
            ])
            ->actions([
                //
            ]);
    }
}
