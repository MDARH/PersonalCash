<?php

namespace App\Enums;

enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';
    case LoanGiven = 'loan_given';
    case LoanTaken = 'loan_taken';
    case Payment = 'payment';

    public function getLabel(): string
    {
        return match ($this) {
            self::Income => 'Income',
            self::Expense => 'Expense',
            self::LoanGiven => 'দিলাম',
            self::LoanTaken => 'পেলাম',
            self::Payment => 'Payment',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Income => 'success',
            self::Expense => 'danger',
            self::LoanGiven => 'warning',
            self::LoanTaken => 'info',
            self::Payment => 'primary',
        };
    }
}
