<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Moving extends Model
{
    protected $table = 'el_moving';

    protected $primaryKey = 'id';

    const CREATED_AT = 'date_created';
    const UPDATED_AT = null; // no updated_at column

    protected $fillable = [
        'hawb',
        'hawb_descr',
        'loc_before',
        'loc_after',
        'users',
    ];

    protected $casts = [
        'date_created' => 'datetime',
    ];
}
