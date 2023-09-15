<?php

namespace App\Filament\Resources\CreditVoucherResource\Pages;

use App\Filament\Resources\CreditVoucherResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCreditVoucher extends CreateRecord
{
    protected static string $resource = CreditVoucherResource::class;
    protected function getRedirectUrl(): string

    {
        return $this->getResource()::getUrl('index');
    }
}
