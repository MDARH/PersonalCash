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
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
