<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Company extends Model implements HasMedia, HasName
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'legal_name',
        'trade_name',
        'gstin',
        'pan',
        'iec',
        'registered_address',
        'state',
        'state_code',
        'authorized_signatory_name',
        'authorized_signatory_designation',
        'bank_name',
        'bank_branch',
        'bank_account_number',
        'bank_ifsc',
        'bank_ad_code',
        'logo_path',
        'signature_path',
        'invoice_prefix',
        'invoice_suffix',
    ];

    public function getFilamentName(): string
    {
        return $this->trade_name ?: $this->legal_name;
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user')->withPivot('role')->withTimestamps();
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function edfs(): HasMany
    {
        return $this->hasMany(Edf::class);
    }

    public function invoiceSequences(): HasMany
    {
        return $this->hasMany(InvoiceSequence::class);
    }
}
