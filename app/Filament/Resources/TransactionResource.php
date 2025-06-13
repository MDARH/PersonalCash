<?php

namespace App\Filament\Resources;

use App\Enums\TransactionType;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Contact;
use App\Models\Transaction;
use Filament\Forms;
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
                                    ->options([
                                        'income' => 'Income',
                                        'expense' => 'Expense',
                                        'loan_given' => 'Loan Given',
                                        'loan_taken' => 'Loan Taken',
                                        'payment' => 'Payment',
                                    ])->required(),
                                TextInput::make('amount')->numeric()->required(),
                            ]),
                        Section::make('Description')
                            ->columnSpan(2)
                            ->schema([
                                Textarea::make('reason')
                                    ->label('Description')
                                    ->nullable(),
                                DateTimePicker::make('date')
                                    ->default(Today())
                                    ->displayFormat('j M, Y h:i A')
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')->dateTime('j M, Y h:i A'),
                TextColumn::make('contact.name')->label('Contact'),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => TransactionType::from($state)->getColor())
                    ->formatStateUsing(fn(string $state): string => TransactionType::from($state)->getLabel())
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Description'),
                TextColumn::make('amount')
                    ->numeric()
                    ->prefix('৳ ')
                    ->label('Amount'),
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
