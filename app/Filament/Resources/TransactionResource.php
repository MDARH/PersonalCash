<?php

namespace App\Filament\Resources;

use App\Enums\TransactionType;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Contact;
use App\Models\Transaction;
use Filament\Forms;
use Illuminate\Support\Str;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
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
                Grid::make(5)
                    ->schema([
                        Section::make('Transaction Details')
                            ->columnSpan(3)
                            ->schema([
                                Select::make('contact_id')
                                    ->relationship('contact', 'name')
                                    ->required()
                                    ->searchable()
                                    ->createOptionForm([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->required()
                                                    ->maxLength(255),
                                                Select::make('type')
                                                    ->options([
                                                        'individual' => 'Individual',
                                                        'shop' => 'Shop',
                                                        'business' => 'Business',
                                                    ])
                                                    ->default('individual'),
                                                TextInput::make('phone')
                                                    ->tel()
                                                    ->maxLength(255)
                                                    ->regex('/^\+?[0-9\s\(\)\-\.]+$/'),
                                                TextInput::make('email')
                                                    ->email()
                                                    ->maxLength(255),
                                                Textarea::make('address')
                                                    ->maxLength(65535)
                                                    ->columnSpanFull(),
                                                Textarea::make('notes')
                                                    ->maxLength(65535)
                                                    ->columnSpanFull(),
                                            ]),
                                    ])
                                    ->preload(),
                                Select::make('type')
                                    ->options(collect(TransactionType::cases())->mapWithKeys(fn($type) => [$type->value => $type->getLabel()]))
                                    ->required(),
                                TextInput::make('amount')
                                ->afterStateUpdated(function ($state, callable $set) {
                                    // Skip if empty or already a number without operators
                                    if (empty($state) || is_numeric($state)) {
                                        return;
                                    }

                                    // Check if the input contains any arithmetic operators
                                    if (preg_match('/[\+\-\*\/]/', $state)) {
                                        try {
                                            // Remove any non-numeric, non-operator characters for security
                                            $sanitized = preg_replace('/[^0-9\+\-\*\/\.]/', '', $state);

                                            // Use eval() to calculate the expression (with safety checks)
                                            if ($sanitized === $state) { // Only proceed if sanitization didn't change anything
                                                $result = eval('return ' . $sanitized . ';');
                                                if (is_numeric($result)) {
                                                    $set('amount', $result);
                                                }
                                            }
                                        } catch (\Throwable $e) {
                                            // If there's an error in evaluation, keep the original input
                                            // This allows users to continue typing their expression
                                        }
                                    }
                                })
                                ->live(onBlur: true)
                                ->dehydrateStateUsing(fn($state) => is_numeric($state) ? $state : null),
                            ]),
                        Section::make('Description')
                            ->columnSpan(2)
                            ->schema([
                                Textarea::make('reason')
                                    ->label('Description')
                                    ->nullable(),
                                DateTimePicker::make('date')
                                    ->default(now()->setTimezone('Asia/Dhaka'))
                                    ->displayFormat('j M, Y h:i A')
                                    ->timezone('Asia/Dhaka')
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('লেনদেন বিবরণ')
                    ->dateTime('j M, Y h:i A')
                    ->description(fn(Transaction $record): string => $record->reason ? Str::limit($record->reason, 30) : '')
                    ->tooltip(fn(Transaction $record): string => $record->reason ?? ''),
                TextColumn::make('contact.name')->label('Contact'),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => TransactionType::from($state)->getColor())
                    ->formatStateUsing(fn(string $state): string => TransactionType::from($state)->getLabel())
                    ->sortable(),
                TextColumn::make('amount')
                    ->numeric()
                    ->prefix('৳ ')
                    ->label('Amount'),
                TextColumn::make('balance')
                    ->label('Balance')
                    ->getStateUsing(function (Transaction $record) {
                        $allTransactions = Transaction::query()
                            ->where(function ($query) use ($record) {
                                $query->where('created_at', '<', $record->created_at)
                                      ->orWhere(function ($query) use ($record) {
                                          $query->where('created_at', $record->created_at)
                                                ->where('id', '<=', $record->id);
                                      });
                            })
                            ->orderBy('created_at')
                            ->orderBy('id')
                            ->get();

                        $currentBalance = 0;
                        foreach ($allTransactions as $transaction) {
                            if (in_array($transaction->type, ['income', 'loan_taken', 'payment'])) {
                                $currentBalance += $transaction->amount;
                            } elseif (in_array($transaction->type, ['expense', 'loan_given'])) {
                                $currentBalance -= $transaction->amount;
                            }
                        }
                        return number_format($currentBalance, 2);
                    })
                    ->color(fn($state) => $state < 0 ? 'danger' : 'success')
                    ->sortable(),
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
