<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboundDetail extends Model
{
    use HasFactory;

    protected $table = 'el_inbound_details';

    protected $primaryKey = 'id';

    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $fillable = [
        'hawb',
        'descr',
        'loc',
        'weight',
        'long',
        'wide',
        'high',
        'flag',
        'scan_time',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'scan_time' => 'datetime',
        'flag'      => 'boolean',
    ];
}
