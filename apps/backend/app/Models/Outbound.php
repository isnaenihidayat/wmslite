<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Outbound extends Model
{
    protected $table = 'el_outbound_header';

    protected $primaryKey = 'id';

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'date_updated';

    protected $fillable = [
        'qty',
        'po',
        'destination',
        'checker',
        'docfile',
        'status',
        'product_category_id',
        'created_by',
        'updated_by',
        'delivery_id',
        'transporter',
    ];

    protected $casts = [
        'date_created' => 'datetime',
        'date_updated' => 'datetime',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(OutboundDetail::class, 'id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }
}
