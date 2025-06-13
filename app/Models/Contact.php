<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'name',
        'type',
        'phone',
        'email',
        'notes',
        'address',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getBalanceAttribute(): float
    {
        $incomeTypes = ['income', 'loan_taken', 'payment']; // আমি যা পেলাম
        $expenseTypes = ['expense', 'loan_given'];          // আমি যা দিলাম

        $income = $this->transactions()->whereIn('type', $incomeTypes)->sum('amount');
        $expense = $this->transactions()->whereIn('type', $expenseTypes)->sum('amount');

        return $income - $expense; // পজিটিভ মান মানে সে আমাকে বাকি আছে, নেগেটিভ মান মানে আমি তার কাছে বাকি
    }

    public function updateBalance(): void
    {
        $this->balance = $this->getBalanceAttribute();
        $this->save();
    }
}
