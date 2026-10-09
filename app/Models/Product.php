<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'sku',
        'hsn_sac_code',
        'description',
        'uom',
        'unit_price',
        'gst_rate',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'gst_rate' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
