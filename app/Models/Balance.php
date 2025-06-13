<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Balance extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'description',
        'transaction_type',
        'amount',
        'balance',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
    public function debit(): BelongsTo
    {
        return $this->belongsTo(DebitVoucher::class);
    }
    public function credit(): BelongsTo
    {
        return $this->belongsTo(CreditVoucher::class);
    }
}
