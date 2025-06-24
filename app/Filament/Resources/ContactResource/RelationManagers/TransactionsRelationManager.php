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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use function Livewire\on;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';
    protected static ?string $recordTitleAttribute = 'contact.name';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('type')
                ->enum(TransactionType::class)
                ->options(collect(TransactionType::cases())->mapWithKeys(fn($type) => [$type->value => $type->getLabel()]))
                ->required(),
            TextInput::make('amount')
                ->required()
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
            Textarea::make('reason')
                ->label('Description')
                ->nullable()
                ->rows(3)
                ->placeholder('Enter transaction details here...')
                ->helperText('Click on a tag below to add it to your description')
                ->afterStateUpdated(function (string $operation, $state, Set $set) {
                    if ($operation !== 'create' && $operation !== 'edit') {
                        return;
                    }
                    
                    // We'll use this to trigger the reactive tag suggestions
                    $set('reason_search', $state);
                })
                ->reactive(),
                
            Forms\Components\Hidden::make('reason_search')
                ->reactive(),
                
            Forms\Components\View::make('filament.forms.components.reason-tags')
                ->viewData(function (Get $get) {
                    // Common predefined tags
                    $allTags = [
                        'Mobile Recharge',
                        'Electricity Bill',
                        'Water Bill',
                        'Gas Bill',
                        'Internet Bill',
                        'Rent',
                        'Salary',
                        'Grocery',
                        'Transport',
                        'Medical',
                        'Education',
                        'Entertainment',
                        'Shopping',
                        'Food',
                        'Travel',
                        'Loan',
                        'Investment',
                        'Savings',
                        'Gift',
                        'Donation',
                        'Other'
                    ];
                    
                    // Get recent reasons from this contact's transactions
                    if ($this->getOwnerRecord()) {
                        $recentTransactions = $this->getOwnerRecord()
                            ->transactions()
                            ->whereNotNull('reason')
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->get();
                            
                        foreach ($recentTransactions as $transaction) {
                            if (!empty($transaction->reason) && !in_array($transaction->reason, $allTags)) {
                                $allTags[] = $transaction->reason;
                            }
                        }
                    }
                    
                    // Filter tags based on what's typed in the reason field
                    $reasonSearch = $get('reason_search') ?? '';
                    $filteredTags = [];
                    
                    if (!empty($reasonSearch)) {
                        foreach ($allTags as $tag) {
                            if (stripos($tag, $reasonSearch) !== false) {
                                $filteredTags[] = $tag;
                            }
                        }
                        // Limit to 10 tags
                        $filteredTags = array_slice($filteredTags, 0, 10);
                    } else {
                        // Show most common tags if no search
                        $filteredTags = array_slice($allTags, 0, 10);
                    }
                    
                    return [
                        'tags' => $filteredTags,
                        'livewire' => $this,
                    ];
                }),
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
                SelectFilter::make('type')
                    ->label('Transaction Type')
                    ->options(collect(TransactionType::cases())->mapWithKeys(fn($type) => [$type->value => $type->getLabel()]))
                    ->multiple()
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
