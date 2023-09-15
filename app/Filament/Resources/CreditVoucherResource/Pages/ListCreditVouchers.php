<?php

namespace App\Filament\Resources\CreditVoucherResource\Pages;

use App\Filament\Resources\CreditVoucherResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCreditVouchers extends ListRecords
{
    protected static string $resource = CreditVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
