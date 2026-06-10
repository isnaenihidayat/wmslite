<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'el_loc';

    protected $primaryKey = 'loc_id';

    public $timestamps = false;

    protected $fillable = [
        'loc_name',
        'loc_descr',
    ];
}
