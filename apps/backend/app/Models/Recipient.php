<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
    protected $table      = 'el_recipient';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $fillable = ['name', 'email', 'created_by', 'updated_by'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
