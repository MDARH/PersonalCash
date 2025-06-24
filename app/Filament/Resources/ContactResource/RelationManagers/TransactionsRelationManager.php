<?php

namespace App\Filament\Resources\contactResource\RelationManagers;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';
    protected static ?string $recordTitleAttribute = 'contact.name';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('type')
                ->enum(TransactionType::class)
                ->options(collect(TransactionType::cases())->mapWithKeys(fn($type) => [$type->value => $type->getLabel()]))
                ->required(),
            TextInput::make('amount')->numeric()->required(),
            Textarea::make('reason')->label('Description')->nullable(),
            DateTimePicker::make('date')
                ->displayFormat('j M, Y h:i A')
                ->default(Now())
                ->required(),
        ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('contact.name')
            ->columns([
                TextColumn::make('date')
                    ->dateTime('j M, Y h:i A')
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Description')
                    ->limit(30)
                    ->searchable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => TransactionType::from($state)->getColor())
                    ->formatStateUsing(fn(string $state): string => TransactionType::from($state)->getLabel())
                    ->sortable(),
                TextColumn::make('amount')
                    ->numeric()
                    ->prefix('৳ ')
                    ->sortable(),
                TextColumn::make('balance')
                    ->numeric()
                    ->prefix('৳ ')
                    ->color(fn($state) => $state < 0 ? 'danger' : 'success')
                    ->state(function (Transaction $record, $livewire): float {
                        // Calculate running balance based on transaction type and amount
                        $transactions = $livewire->getOwnerRecord()->transactions()
                            ->where('created_at', '<=', $record->created_at)
                            ->orderBy('created_at')
                            ->orderBy('id')
                            ->get();
                        $balance = 0;
                        foreach ($transactions as $transaction) {
                            $type = TransactionType::from($transaction->type);

                            if ($type === TransactionType::Income || $type === TransactionType::LoanTaken) {
                                $balance += $transaction->amount;
                            } elseif ($type === TransactionType::Expense || $type === TransactionType::LoanGiven) {
                                $balance -= $transaction->amount;
                            } elseif ($type === TransactionType::Payment) {
                                // Determine if payment is incoming or outgoing based on context
                                // This is a simplified approach - you may need to adjust based on your business logic
                                $balance += $transaction->amount; // Assuming payment is incoming by default
                            }

                            if ($transaction->id === $record->id) {
                                break;
                            }
                        }

                        return $balance;
                    }),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, string $model): Transaction {
                        return Transaction::create([
                            ...$data,
                            'contact_id' => $this->getOwnerRecord()->id,
                        ]);
                    })
                    ->form(fn(Form $form) => $this->form($form))
                    ->modalHeading('Create Transaction')
                    ->visible(fn(): bool => true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
