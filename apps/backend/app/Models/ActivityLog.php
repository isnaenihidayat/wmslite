<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'el_log';

    protected $primaryKey = 'id';

    const CREATED_AT = 'date_created';
    const UPDATED_AT = null; // no updated_at column

    // Read-only model — no fillable needed
    protected $fillable = [];

    protected $casts = [
        'date_created' => 'datetime',
    ];
}
