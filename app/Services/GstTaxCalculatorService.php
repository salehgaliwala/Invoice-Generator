<?php

namespace App\Services;

use App\Models\Company;

class GstTaxCalculatorService
{
    /**
     * Calculate tax details for line items and invoice header.
     */
    public static function calculate(
        Company $company,
        ?string $customerGstin,
        string $posStateCode,
        string $transactionType,
        array $items
    ): array {
        $hasCompanyGstin = ! empty(trim($company->gstin ?? ''));
        $companyStateCode = $company->state_code;

        $subtotal = 0.0;
        $totalDiscount = 0.0;
        $taxableSubtotal = 0.0;
        $totalCgst = 0.0;
        $totalSgst = 0.0;
        $totalIgst = 0.0;

        $processedItems = [];

        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $discount = (float) ($item['discount_amount'] ?? 0);
            $gstRate = (float) ($item['gst_rate'] ?? 0);

            $itemSubtotal = round($qty * $unitPrice, 2);
            $itemTaxable = max(0, round($itemSubtotal - $discount, 2));

            $cgstRate = 0.0;
            $cgstAmount = 0.0;
            $sgstRate = 0.0;
            $sgstAmount = 0.0;
            $igstRate = 0.0;
            $igstAmount = 0.0;

            if ($transactionType === 'export_lut') {
                // Zero-rated without payment of tax
                $cgstRate = 0; $sgstRate = 0; $igstRate = 0;
            } elseif ($transactionType === 'export_igst') {
                // Export on payment of IGST
                $igstRate = $gstRate;
                $igstAmount = round(($itemTaxable * $igstRate) / 100, 2);
            } else {
                // Domestic transaction
                if (! $hasCompanyGstin) {
                    // Rule 5: No GST applied if company setup lacks GSTIN
                    $cgstRate = 0; $sgstRate = 0; $igstRate = 0;
                } else {
                    $isSameState = ($companyStateCode === $posStateCode);
                    if ($isSameState) {
                        $cgstRate = round($gstRate / 2, 2);
                        $sgstRate = round($gstRate / 2, 2);
                        $cgstAmount = round(($itemTaxable * $cgstRate) / 100, 2);
                        $sgstAmount = round(($itemTaxable * $sgstRate) / 100, 2);
                    } else {
                        $igstRate = $gstRate;
                        $igstAmount = round(($itemTaxable * $igstRate) / 100, 2);
                    }
                }
            }

            $itemTotal = round($itemTaxable + $cgstAmount + $sgstAmount + $igstAmount, 2);

            $subtotal += $itemSubtotal;
            $totalDiscount += $discount;
            $taxableSubtotal += $itemTaxable;
            $totalCgst += $cgstAmount;
            $totalSgst += $sgstAmount;
            $totalIgst += $igstAmount;

            $processedItems[] = array_merge($item, [
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'discount_amount' => $discount,
                'taxable_amount' => $itemTaxable,
                'gst_rate' => $gstRate,
                'cgst_rate' => $cgstRate,
                'cgst_amount' => $cgstAmount,
                'sgst_rate' => $sgstRate,
                'sgst_amount' => $sgstAmount,
                'igst_rate' => $igstRate,
                'igst_amount' => $igstAmount,
                'total_amount' => $itemTotal,
            ]);
        }

        $totalTax = round($totalCgst + $totalSgst + $totalIgst, 2);
        $rawTotal = round($taxableSubtotal + $totalTax, 2);
        $roundedTotal = round($rawTotal);
        $roundOff = round($roundedTotal - $rawTotal, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($totalDiscount, 2),
            'taxable_amount' => round($taxableSubtotal, 2),
            'cgst_amount' => round($totalCgst, 2),
            'sgst_amount' => round($totalSgst, 2),
            'igst_amount' => round($totalIgst, 2),
            'total_tax_amount' => round($totalTax, 2),
            'round_off' => $roundOff,
            'total_amount' => round($roundedTotal, 2),
            'items' => $processedItems,
        ];
    }
}
