<?php

namespace App\Services;

use App\Models\Company;
use App\Models\InvoiceSequence;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InvoiceNumberGeneratorService
{
    /**
     * Calculate Financial Year string from a given date.
     * Example: 2024-04-15 -> FY24-25; 2025-03-10 -> FY24-25.
     */
    public function getFinancialYear(Carbon|string $date): string
    {
        $carbonDate = is_string($date) ? Carbon::parse($date) : $date;
        $year = $carbonDate->year;
        $month = $carbonDate->month;

        if ($month >= 4) {
            $startYear = $year;
            $endYear = $year + 1;
        } else {
            $startYear = $year - 1;
            $endYear = $year;
        }

        return sprintf('%02d-%02d', $startYear % 100, $endYear % 100);
    }

    /**
     * Generate next sequential invoice number for a company thread-safely.
     */
    public function generateNextNumber(Company $company, Carbon|string|null $date = null): string
    {
        $date = $date ? (is_string($date) ? Carbon::parse($date) : $date) : Carbon::now();
        $fy = $this->getFinancialYear($date);

        return DB::transaction(function () use ($company, $fy) {
            /** @var InvoiceSequence $sequence */
            $sequence = InvoiceSequence::where('company_id', $company->id)
                ->where('financial_year', $fy)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                $sequence = InvoiceSequence::create([
                    'company_id' => $company->id,
                    'financial_year' => $fy,
                    'last_number' => 0,
                ]);
            }

            $nextNumber = $sequence->last_number + 1;
            $sequence->last_number = $nextNumber;
            $sequence->save();

            $prefix = $company->invoice_prefix ?: 'INV';

            // Format format: e.g. FY24-25/INV/0001 (15 chars)
            $formattedNumber = sprintf('%s/%s/%04d', $prefix, $fy, $nextNumber);

            // Ensure GST Rule 46 compliance (max 16 characters)
            if (strlen($formattedNumber) > 16) {
                $formattedNumber = sprintf('%s/%s/%d', $prefix, $fy, $nextNumber);
            }

            if (strlen($formattedNumber) > 16) {
                throw new InvalidArgumentException("Generated invoice number '{$formattedNumber}' exceeds the maximum 16 characters permitted by GST Rule 46.");
            }

            return $formattedNumber;
        } );
    }
}
