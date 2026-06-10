<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inbound extends Model
{
    protected $table = 'el_inbound_header';

    protected $primaryKey = 'id';

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'date_updated';

    protected $fillable = [
        'hawb',
        'descr',
        'product_category_id',
        'modality',
        'delivery_id',
        'qty',
        'po',
        'locator',
        'checker',
        'status',
        'ship_method',
        'etd',
        'eta',
        'ata',
        'pib_number',
        'sppb_date',
        'created_by',
        'updated_by',
        'from_shipment',
    ];

    protected $casts = [
        'etd'          => 'datetime',
        'eta'          => 'datetime',
        'ata'          => 'datetime',
        'sppb_date'    => 'datetime',
        'date_created' => 'datetime',
        'date_updated' => 'datetime',
        'from_shipment' => 'boolean',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(InboundDetail::class, 'hawb', 'hawb');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * Scope: shipments only (from_shipment = 1)
     */
    public function scopeShipments($query)
    {
        return $query->where('from_shipment', 1);
    }

    /**
     * Scope: inbound only (from_shipment = 0 or null)
     */
    public function scopeInboundOnly($query)
    {
        return $query->where(function ($q) {
            $q->where('from_shipment', 0)->orWhereNull('from_shipment');
        });
    }
}
