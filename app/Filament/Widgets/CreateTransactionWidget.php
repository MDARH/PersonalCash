<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\Contact;
use App\Models\Transaction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

/**
 * Widget to create new transactions directly from the dashboard
 */
class CreateTransactionWidget extends Widget
{
    use InteractsWithForms;

    protected static ?int $sort = 3;
    protected static ?string $heading = 'নতুন লেনদেন যোগ করুন';
    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament-panels::widgets.form-widget';

    public ?array $data = [];
    
    /**
     * The form instance for the widget.
     *
     * @var Form|null
     */
    protected ?Form $form = null;

    /**
     * Mount the widget and initialize the form.
     */
    public function mount(): void
    {
        $this->form = $this->makeForm();
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('contact_id')
                    ->label('কন্টাক্ট')
                    ->relationship('contact', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
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
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columnSpan(1),
                Select::make('type')
                    ->label('লেনদেনের ধরণ')
                    ->options(collect(TransactionType::cases())->mapWithKeys(fn($type) => [$type->value => $type->getLabel()]))
                    ->required()
                    ->columnSpan(1),
                TextInput::make('amount')
                    ->label('পরিমাণ')
                    ->required()
                    ->numeric()
                    ->columnSpan(1),
                DateTimePicker::make('date')
                    ->label('তারিখ')
                    ->default(now()->setTimezone('Asia/Dhaka'))
                    ->displayFormat('j M, Y h:i A')
                    ->timezone('Asia/Dhaka')
                    ->required()
                    ->columnSpan(1),
                Textarea::make('reason')
                    ->label('বিবরণ')
                    ->nullable()
                    ->columnSpan(2),
            ])
            ->columns(2);
    }

    /**
     * Handle the creation of a new transaction.
     */
    public function create(): void
    {
        $data = $this->form->getState();
        $transaction = Transaction::create($data);
        $this->form->fill();
        Notification::make()
            ->title('লেনদেন সফলভাবে তৈরি করা হয়েছে')
            ->success()
            ->send();
    }

    /**
     * Define the actions for the form widget.
     */
    public function getFormActions(): array
    {
        return [
            \Filament\Forms\Components\Actions\Action::make('create')
                ->label('নতুন লেনদেন যোগ করুন')
                ->action(fn () => $this->create())
                ->color('primary'),
        ];
    }
}
