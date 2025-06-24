<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Filament\Resources\ContactResource\RelationManagers;
use App\Filament\Resources\ContactResource\RelationManagers\TransactionsRelationManager;
use App\Models\Contact;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('name')->required(),
                        Select::make('type')
                            ->options([
                                'individual' => 'Individual',
                                'shop' => 'Shop',
                                'business' => 'Business',
                            ])
                            ->default('individual'),
                        TextInput::make('phone')
                            ->tel()
                            ->regex('/^\+?[0-9\s\(\)\-\.]+$/'),
                        TextInput::make('email')->email(),
                        Textarea::make('address'),
                        Textarea::make('notes'),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => fn(?Contact $record) => $record === null ? 3 : 2]),


                Section::make()
                    ->schema([
                        Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn(Contact $record): ?string => $record->created_at?->format('j M, Y h:i A')),
                        Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn(Contact $record): ?string => $record->updated_at?->format('j M, Y h:i A')),
                        Placeholder::make('balance')
                            ->label('Current Due Amount')
                            ->content(fn(Contact $record): string => '৳ ' . number_format($record->balance, 2))
                            ->extraAttributes(fn(Contact $record) => [
                                'class' => $record->balance < 0 ? 'text-danger-500' : 'text-success-500',
                                'style' => 'font-weight: bold;',
                            ]),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn(?Contact $record) => $record === null),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('type'),
                TextColumn::make('phone'),
                TextColumn::make('email'),
                TextColumn::make('balance')
                    ->label('Balance (BDT)')
                    ->getStateUsing(fn($record) => number_format($record->balance, 2))
                    ->color(fn($state) => $state < 0 ? 'danger' : 'success')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'view' => Pages\ViewContact::route('/{record}'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
