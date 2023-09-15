<?php

namespace App\Filament\Resources\DebitVoucherResource\Pages;

use App\Filament\Resources\DebitVoucherResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDebitVoucher extends EditRecord
{
    protected static string $resource = DebitVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
