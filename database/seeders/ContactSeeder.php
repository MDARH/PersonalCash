<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contact;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Contact::create([
            'name' => 'Test Contact',
            'type' => 'individual',
            'phone' => '1234567890',
            'email' => 'test@contact.com',
        ]);
    }
}