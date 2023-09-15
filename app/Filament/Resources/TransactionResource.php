<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Customer;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('customer_id')
                    ->label('Customer Info')
                    ->required()
                    ->relationship(
                        name: 'customer',
                        titleAttribute: 'name',
                    )
                    ->getOptionLabelFromRecordUsing(fn (Customer $record) => "{$record->name} - {$record->mobile}")
                    ->searchable(['name', 'mobile']),

                // Select::make('customer_id')->relationship('customer', 'name'),
                Textarea::make('description')->columnSpan(2),
                TextInput::make('amount')
                    ->required()
                    ->reactive(),
                Select::make('transaction_type')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        $new_amount = (int) $get('amount');
                        $transactionType = $get('transaction_type');
                        $currentBalance = Transaction::latest('id')->first();
                        $checkCurrentBalanceIsNotEmpty = (empty($currentBalance->balance)) ? 0 : $currentBalance->balance;

                        // Due & Payable
                        $checkCustomerByID = $get('customer_id');
                        $lastDue = Transaction::where('customer_id', $checkCustomerByID)
                            ->orderBy('created_at', 'desc')
                            ->value('current_due');
                        $lastPayable = Transaction::where('customer_id', $checkCustomerByID)
                            ->orderBy('created_at', 'desc')
                            ->value('payable');

                        if ($transactionType === 'debit') {
                            $set('balance', (int) $checkCurrentBalanceIsNotEmpty - $new_amount);

                            $current_due = $lastDue + $new_amount;
                            $set('current_due', $current_due);

                            $payable = $lastPayable - $new_amount;
                            if ($payable < 0) {
                                $set('payable', 0);
                            } else {
                                $set('payable', $payable);
                            }
                        } else {
                            $set('balance', (int) $checkCurrentBalanceIsNotEmpty +  $new_amount);

                            $current_due = $lastDue - $new_amount;
                            if ($current_due < 0) {
                                $set('current_due', 0);
                            } else {
                                $set('current_due', $current_due);
                            }
                            $set('payable', $lastPayable + $new_amount);
                        }
                    })
                    ->options([
                        'debit' => 'Debit',
                        'credit' => 'Credit',
                    ]),

                TextInput::make('balance')
                    ->label('New Balance'),
                TextInput::make('current_due')->default(0),
                TextInput::make('payable')->default(0)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')->label('Transaction By')->searchable(),
                TextColumn::make('created_at')->label('Date')->date(),
                TextColumn::make('debit')
                    ->label('Debit')
                    ->state(fn (Transaction $record) => ($record->transaction_type == 'debit') ? "{$record->amount}" : '-'),
                TextColumn::make('credit')
                    ->label('Credit')
                    ->state(fn (Transaction $record) => ($record->transaction_type == 'credit') ? "{$record->amount}" : '-'),
                TextColumn::make('balance'),
                TextColumn::make('current_due')
                    ->label('Due'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
