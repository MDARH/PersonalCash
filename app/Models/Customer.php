<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'mobile',
        'description',
        'notes'
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
    public function credit()
    {
        return Transaction::amount('transaction_type', 'credit')->get();
    }
}
