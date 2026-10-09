<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Edf;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\User;
use App\Services\GstTaxCalculatorService;
use App\Services\InvoiceNumberGeneratorService;
use App\Services\NumberToWordsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin User
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Create Company 1: Apex Global Traders Private Limited (Maharashtra)
        $company1 = Company::create([
            'legal_name' => 'Apex Global Traders Private Limited',
            'trade_name' => 'Apex Exporters',
            'gstin' => '27AAACA1234A1Z5',
            'pan' => 'AAACA1234A',
            'iec' => '0312345678',
            'registered_address' => 'Plot No. 42, MIDC Industrial Area, Andheri East, Mumbai - 400093',
            'state' => 'Maharashtra',
            'state_code' => '27',
            'authorized_signatory_name' => 'Rajesh Sharma',
            'authorized_signatory_designation' => 'Managing Director',
            'bank_name' => 'State Bank of India',
            'bank_branch' => 'CAG Branch, Fort, Mumbai',
            'bank_account_number' => '39820192831',
            'bank_ifsc' => 'SBIN0009988',
            'bank_ad_code' => '0210001',
            'invoice_prefix' => 'INV',
        ]);

        // 3. Create Company 2: Bharat Tech Solutions Pvt Ltd (Delhi)
        $company2 = Company::create([
            'legal_name' => 'Bharat Tech Solutions Pvt Ltd',
            'trade_name' => 'BharatTech',
            'gstin' => '07BBBBB5678B1Z2',
            'pan' => 'BBBBB5678B',
            'iec' => '0798765432',
            'registered_address' => '102, Connaught Place, New Delhi - 110001',
            'state' => 'Delhi',
            'state_code' => '07',
            'authorized_signatory_name' => 'Anita Verma',
            'authorized_signatory_designation' => 'Chief Executive Officer',
            'bank_name' => 'HDFC Bank',
            'bank_branch' => 'Connaught Place Branch, New Delhi',
            'bank_account_number' => '50200012345678',
            'bank_ifsc' => 'HDFC0000001',
            'bank_ad_code' => '0330002',
            'invoice_prefix' => 'BTS',
        ]);

        // Attach user to companies
        $user->companies()->attach([
            $company1->id => ['role' => 'admin'],
            $company2->id => ['role' => 'admin'],
        ]);

        // 4. Create Customers for Company 1
        $domesticCustomer = Customer::create([
            'company_id' => $company1->id,
            'name' => 'Reliance Retail Logistics Ltd',
            'email' => 'billing@relianceretail.com',
            'phone' => '+91 22 6789 0000',
            'gstin' => '27AAACR9999R1ZK',
            'pan' => 'AAACR9999R',
            'billing_address' => 'Reliance Corporate Park, Thane Belapur Road, Navi Mumbai',
            'billing_city' => 'Navi Mumbai',
            'billing_state' => 'Maharashtra',
            'billing_state_code' => '27',
            'billing_country' => 'India',
            'is_export' => false,
        ]);

        $interstateCustomer = Customer::create([
            'company_id' => $company1->id,
            'name' => 'Bangalore Machinery Corp',
            'email' => 'accounts@bangalore machinery.com',
            'phone' => '+91 80 2345 6789',
            'gstin' => '29AAACB1111B1Z3',
            'pan' => 'AAACB1111B',
            'billing_address' => '5th Block, Koramangala, Bengaluru',
            'billing_city' => 'Bengaluru',
            'billing_state' => 'Karnataka',
            'billing_state_code' => '29',
            'billing_country' => 'India',
            'is_export' => false,
        ]);

        $exportCustomer = Customer::create([
            'company_id' => $company1->id,
            'name' => 'Acme Global LLC',
            'email' => 'import@acmeglobal.com',
            'phone' => '+1 212 555 0199',
            'billing_address' => '350 Fifth Avenue, Suite 4000, New York, NY 10118',
            'billing_city' => 'New York',
            'billing_state' => 'New York',
            'billing_state_code' => '96',
            'billing_country' => 'United States',
            'is_export' => true,
        ]);

        // 5. Create Products for Company 1
        $product1 = Product::create([
            'company_id' => $company1->id,
            'name' => 'Industrial Hydraulic Valve 50mm',
            'sku' => 'IHV-50',
            'hsn_sac_code' => '84818090',
            'uom' => 'PCS',
            'unit_price' => 12500.00,
            'gst_rate' => 18.00,
        ]);

        $product2 = Product::create([
            'company_id' => $company1->id,
            'name' => 'Precision Stainless Steel Fitting',
            'sku' => 'PSSF-10',
            'hsn_sac_code' => '73072200',
            'uom' => 'KGS',
            'unit_price' => 450.00,
            'gst_rate' => 18.00,
        ]);

        // 6. Create Domestic Invoice (Intra-state)
        $generator = new InvoiceNumberGeneratorService();
        $invNum1 = $generator->generateNextNumber($company1);
        $fy1 = $generator->getFinancialYear(now());

        $calc1 = GstTaxCalculatorService::calculate(
            $company1,
            $domesticCustomer->gstin,
            $domesticCustomer->billing_state_code,
            'domestic',
            [[
                'quantity' => 10,
                'unit_price' => $product1->unit_price,
                'discount_amount' => 5000.00,
                'gst_rate' => $product1->gst_rate,
            ]]
        );

        $invoice1 = Invoice::create([
            'company_id' => $company1->id,
            'customer_id' => $domesticCustomer->id,
            'invoice_number' => $invNum1,
            'financial_year' => $fy1,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'place_of_supply_state' => $domesticCustomer->billing_state,
            'place_of_supply_state_code' => $domesticCustomer->billing_state_code,
            'transaction_type' => 'domestic',
            'currency' => 'INR',
            'exchange_rate' => 1.0000,
            'subtotal' => $calc1['subtotal'],
            'discount_amount' => $calc1['discount_amount'],
            'taxable_amount' => $calc1['taxable_amount'],
            'cgst_amount' => $calc1['cgst_amount'],
            'sgst_amount' => $calc1['sgst_amount'],
            'igst_amount' => $calc1['igst_amount'],
            'total_tax_amount' => $calc1['total_tax_amount'],
            'round_off' => $calc1['round_off'],
            'total_amount' => $calc1['total_amount'],
            'amount_in_words' => NumberToWordsService::convert($calc1['total_amount'], 'INR'),
            'status' => 'issued',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice1->id,
            'product_id' => $product1->id,
            'item_description' => $product1->name,
            'hsn_sac_code' => $product1->hsn_sac_code,
            'quantity' => 10,
            'uom' => $product1->uom,
            'unit_price' => $product1->unit_price,
            'discount_amount' => 5000.00,
            'taxable_amount' => $calc1['items'][0]['taxable_amount'],
            'gst_rate' => $product1->gst_rate,
            'cgst_rate' => $calc1['items'][0]['cgst_rate'],
            'cgst_amount' => $calc1['items'][0]['cgst_amount'],
            'sgst_rate' => $calc1['items'][0]['sgst_rate'],
            'sgst_amount' => $calc1['items'][0]['sgst_amount'],
            'igst_rate' => 0,
            'igst_amount' => 0,
            'total_amount' => $calc1['items'][0]['total_amount'],
        ]);

        // 7. Create Export Invoice & EDF (LUT)
        $invNum2 = $generator->generateNextNumber($company1);

        $calc2 = GstTaxCalculatorService::calculate(
            $company1,
            null,
            $exportCustomer->billing_state_code,
            'export_lut',
            [[
                'quantity' => 50,
                'unit_price' => 500.00, // $500 per unit in USD
                'discount_amount' => 0.00,
                'gst_rate' => 18.00,
            ]]
        );

        $invoice2 = Invoice::create([
            'company_id' => $company1->id,
            'customer_id' => $exportCustomer->id,
            'invoice_number' => $invNum2,
            'financial_year' => $fy1,
            'invoice_date' => now(),
            'due_date' => now()->addDays(45),
            'place_of_supply_state' => 'OTHER TERRITORY',
            'place_of_supply_state_code' => '96',
            'transaction_type' => 'export_lut',
            'lut_number' => 'LUT/2024-25/001',
            'lut_date' => now()->startOfYear(),
            'currency' => 'USD',
            'exchange_rate' => 83.5000,
            'subtotal' => $calc2['subtotal'],
            'discount_amount' => $calc2['discount_amount'],
            'taxable_amount' => $calc2['taxable_amount'],
            'cgst_amount' => 0,
            'sgst_amount' => 0,
            'igst_amount' => 0,
            'total_tax_amount' => 0,
            'round_off' => $calc2['round_off'],
            'total_amount' => $calc2['total_amount'],
            'amount_in_words' => NumberToWordsService::convert($calc2['total_amount'], 'USD'),
            'status' => 'issued',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice2->id,
            'product_id' => $product1->id,
            'item_description' => $product1->name . ' (Export Specs)',
            'hsn_sac_code' => $product1->hsn_sac_code,
            'quantity' => 50,
            'uom' => 'PCS',
            'unit_price' => 500.00,
            'discount_amount' => 0,
            'taxable_amount' => 25000.00,
            'gst_rate' => 18.00,
            'cgst_rate' => 0,
            'cgst_amount' => 0,
            'sgst_rate' => 0,
            'sgst_amount' => 0,
            'igst_rate' => 0,
            'igst_amount' => 0,
            'total_amount' => 25000.00,
        ]);

        // Create associated EDF
        Edf::create([
            'company_id' => $company1->id,
            'invoice_id' => $invoice2->id,
            'edf_number' => 'EDF-' . $invNum2,
            'iec_number' => $company1->iec,
            'ad_code' => $company1->bank_ad_code,
            'port_of_export' => 'Nava Sheva (INNSA1)',
            'shipping_bill_number' => 'SB7891234',
            'shipping_bill_date' => now(),
            'cha_name' => 'Jeena & Company CHA',
            'cha_license_number' => 'CHA112233',
            'vessel_flight_no' => 'MAERSK SEALAND V.204',
            'port_of_loading' => 'Nava Sheva',
            'port_of_discharge' => 'New York Port',
            'nature_of_contract' => 'FOB',
            'currency_of_realization' => 'USD',
            'exchange_rate' => 83.5000,
            'invoice_value_fcy' => 25000.00,
            'total_realizable_value_fcy' => 25000.00,
            'total_realizable_value_inr' => round(25000.00 * 83.5000, 2),
            'remarks' => 'Export under LUT without tax payment.',
        ]);
    }
}
