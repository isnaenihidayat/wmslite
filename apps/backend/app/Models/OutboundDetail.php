<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutboundDetail extends Model
{
    protected $table = 'el_outbound_details';

    // No primary key — composite indexed
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'hawb',
        'descr',
        'loc',
        'id',
        'flag',
        'scan_time',
        'created_by',
        'updated_by',
        'date_created',
        'date_updated',
    ];

    protected $casts = [
        'scan_time'    => 'datetime',
        'date_created' => 'datetime',
        'date_updated' => 'datetime',
        'flag'         => 'boolean',
    ];
}
