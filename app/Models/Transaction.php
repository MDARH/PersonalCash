<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'transaction_type',
        'amount',
        'reason',
        'date',
        'type',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::saved(function ($transaction) {
            $transaction->contact->updateBalance();
        });

        static::deleted(function ($transaction) {
            $transaction->contact->updateBalance();
        });
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
