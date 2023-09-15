<?php

namespace App\Filament\Resources\DebitVoucherResource\Pages;

use App\Filament\Resources\DebitVoucherResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDebitVoucher extends CreateRecord
{
    protected static string $resource = DebitVoucherResource::class;
    protected function getRedirectUrl(): string

    {
        return $this->getResource()::getUrl('index');
    }
}
