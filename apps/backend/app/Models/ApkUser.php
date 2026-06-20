<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class ApkUser extends Authenticatable
{
    use HasFactory;

    protected $table = 'el_apk';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'username',
        'password',
        'last_login',
        'token',
    ];

    protected $hidden = [
        'password',
        'token',
    ];

    protected $casts = [
        'last_login' => 'datetime',
    ];
}
