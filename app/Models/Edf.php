<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Edf extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'edfs';

    protected $fillable = [
        'company_id',
        'invoice_id',
        'edf_number',
        'iec_number',
        'ad_code',
        'port_of_export',
        'shipping_bill_number',
        'shipping_bill_date',
        'cha_name',
        'cha_license_number',
        'vessel_flight_no',
        'port_of_loading',
        'port_of_discharge',
        'nature_of_contract',
        'currency_of_realization',
        'exchange_rate',
        'invoice_value_fcy',
        'total_realizable_value_fcy',
        'total_realizable_value_inr',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'shipping_bill_date' => 'date',
            'exchange_rate' => 'decimal:4',
            'invoice_value_fcy' => 'decimal:2',
            'total_realizable_value_fcy' => 'decimal:2',
            'total_realizable_value_inr' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
