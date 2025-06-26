<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    /**
     * Seed the transactions table with 100 random transactions.
     */
    public function run(): void
    {
        \App::setLocale('en');
        $faker = \Faker\Factory::create();
        $contactIds = \App\Models\Contact::pluck('id')->toArray();
        $types = ['income', 'expense', 'loan_given', 'loan_taken'];
        for ($i = 1; $i <= 100; $i++) {
            Transaction::create([
                'contact_id' => $faker->randomElement($contactIds),
                'type' => $faker->randomElement($types),
                'amount' => $faker->numberBetween(100, 10000),
                'date' => $faker->dateTimeBetween('-1 year', 'now'),
                'reason' => $faker->sentence,
            ]);
        }
    }
}
