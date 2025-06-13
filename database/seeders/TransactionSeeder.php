<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Transaction::create([
            'contact_id' => 1, // Assuming a contact with ID 1 exists
            'type' => 'income',
            'amount' => 1000,
            'date' => now(),
            'reason' => 'Initial credit transaction',
        ]);
    }
}
