<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contact;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    /**
     * Seed the contacts table with 10 random contacts.
     */
    public function run(): void
    {
        \App::setLocale('en');
        $faker = \Faker\Factory::create();
        $types = ['individual', 'shop', 'business'];
        for ($i = 1; $i <= 10; $i++) {
            Contact::create([
                'name' => $faker->name,
                'type' => $faker->randomElement($types),
                'phone' => $faker->phoneNumber,
                'email' => $faker->unique()->safeEmail,
            ]);
        }
    }
}