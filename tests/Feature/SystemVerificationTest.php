<?php

namespace Tests\Feature;

use App\Filament\Resources\CompanyResource\Pages\CreateCompany;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Edf;
use App\Models\Invoice;
use App\Models\User;
use App\Services\GstTaxCalculatorService;
use App\Services\InvoiceNumberGeneratorService;
use App\Services\NumberToWordsService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SystemVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_creation_attaches_company_to_user_tenants(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $companyData = [
            'legal_name' => 'New Tenant Corp Pvt Ltd',
            'trade_name' => 'NewTenant',
            'gstin' => '27AAACN1234N1Z1',
            'pan' => 'AAACN1234N',
            'iec' => '0100001111',
            'registered_address' => 'Sample Address',
            'state' => 'Maharashtra',
            'state_code' => '27',
            'authorized_signatory_name' => 'John Doe',
            'bank_name' => 'ICICI Bank',
            'bank_branch' => 'Main Branch',
            'bank_account_number' => '1122334455',
            'bank_ifsc' => 'ICIC0001122',
            'invoice_prefix' => 'NTC',
        ];

        Livewire::test(CreateCompany::class)
            ->fillForm($companyData)
            ->call('create')
            ->assertHasNoFormErrors();

        $company = Company::where('legal_name', 'New Tenant Corp Pvt Ltd')->first();
        $this->assertNotNull($company);

        $user->refresh();
        $this->assertTrue($user->getTenants(Filament::getPanel())->contains($company));
    }

    public function test_invoice_number_generator_is_thread_safe_and_gst_rule_46_compliant(): void
    {
        $company = Company::factory()->create([
            'invoice_prefix' => 'INV',
        ]);

        $generator = new InvoiceNumberGeneratorService();
        $fy = $generator->getFinancialYear(now());

        $num1 = $generator->generateNextNumber($company);
        $num2 = $generator->generateNextNumber($company);

        $this->assertEquals("INV/{$fy}/0001", $num1);
        $this->assertEquals("INV/{$fy}/0002", $num2);
        $this->assertLessThanOrEqual(16, strlen($num1));
        $this->assertLessThanOrEqual(16, strlen($num2));
    }

    public function test_gst_tax_calculator_for_intrastate_interstate_and_export_lut(): void
    {
        $companyWithGstin = Company::factory()->create([
            'gstin' => '27AAACA1234A1Z5',
            'state_code' => '27',
        ]);

        // 1. Intra-state: CGST (9%) + SGST (9%)
        $intraCalc = GstTaxCalculatorService::calculate(
            $companyWithGstin,
            '27AAACR9999R1ZK',
            '27',
            'domestic',
            [[
                'quantity' => 2,
                'unit_price' => 1000.00,
                'discount_amount' => 0,
                'gst_rate' => 18.00,
            ]]
        );

        $this->assertEquals(2000.00, $intraCalc['taxable_amount']);
        $this->assertEquals(180.00, $intraCalc['cgst_amount']);
        $this->assertEquals(180.00, $intraCalc['sgst_amount']);
        $this->assertEquals(0.00, $intraCalc['igst_amount']);
        $this->assertEquals(2360.00, $intraCalc['total_amount']);

        // 2. Inter-state: IGST (18%)
        $interCalc = GstTaxCalculatorService::calculate(
            $companyWithGstin,
            '29AAACB1111B1Z3',
            '29',
            'domestic',
            [[
                'quantity' => 2,
                'unit_price' => 1000.00,
                'discount_amount' => 0,
                'gst_rate' => 18.00,
            ]]
        );

        $this->assertEquals(2000.00, $interCalc['taxable_amount']);
        $this->assertEquals(0.00, $interCalc['cgst_amount']);
        $this->assertEquals(0.00, $interCalc['sgst_amount']);
        $this->assertEquals(360.00, $interCalc['igst_amount']);
        $this->assertEquals(2360.00, $interCalc['total_amount']);

        // 3. Export under LUT: 0% Tax
        $exportCalc = GstTaxCalculatorService::calculate(
            $companyWithGstin,
            null,
            '96',
            'export_lut',
            [[
                'quantity' => 2,
                'unit_price' => 1000.00,
                'discount_amount' => 0,
                'gst_rate' => 18.00,
            ]]
        );

        $this->assertEquals(2000.00, $exportCalc['taxable_amount']);
        $this->assertEquals(0.00, $exportCalc['total_tax_amount']);
        $this->assertEquals(2000.00, $exportCalc['total_amount']);

        // 4. Rule 5: No GST applied if company setup lacks GSTIN
        $companyWithoutGstin = Company::factory()->create([
            'gstin' => null,
            'state_code' => '27',
        ]);

        $noGstinCalc = GstTaxCalculatorService::calculate(
            $companyWithoutGstin,
            '27AAACR9999R1ZK',
            '27',
            'domestic',
            [[
                'quantity' => 2,
                'unit_price' => 1000.00,
                'discount_amount' => 0,
                'gst_rate' => 18.00,
            ]]
        );

        $this->assertEquals(0.00, $noGstinCalc['total_tax_amount']);
        $this->assertEquals(2000.00, $noGstinCalc['total_amount']);
    }

    public function test_amount_in_words_conversion(): void
    {
        $wordsINR = NumberToWordsService::convert(123456.78, 'INR');
        $this->assertEquals('One Lakh Twenty Three Thousand Four Hundred Fifty Six Rupees and Seventy Eight Paise Only', $wordsINR);

        $wordsUSD = NumberToWordsService::convert(25000.00, 'USD');
        $this->assertEquals('Twenty Five Thousand USD Only', $wordsUSD);
    }

    public function test_pdf_rendering_routes(): void
    {
        $company = Company::factory()->create([
            'legal_name' => 'Test Company Pvt Ltd',
            'registered_address' => 'Test Street',
            'state' => 'Maharashtra',
            'state_code' => '27',
            'authorized_signatory_name' => 'Signatory',
            'bank_name' => 'Bank',
            'bank_branch' => 'Branch',
            'bank_account_number' => '123',
            'bank_ifsc' => 'IFSC',
        ]);

        $user = User::factory()->create();
        $user->companies()->attach($company->id);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'name' => 'Test Customer',
            'billing_address' => 'Address',
            'billing_state' => 'Maharashtra',
            'billing_state_code' => '27',
        ]);

        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV/24-25/0001',
            'financial_year' => '24-25',
            'invoice_date' => now(),
            'place_of_supply_state' => 'Maharashtra',
            'place_of_supply_state_code' => '27',
            'transaction_type' => 'export_lut',
            'total_amount' => 5000.00,
        ]);

        $edf = Edf::factory()->create([
            'company_id' => $company->id,
            'invoice_id' => $invoice->id,
            'edf_number' => 'EDF-INV/24-25/0001',
            'iec_number' => '0123456789',
            'ad_code' => '0210001',
            'port_of_export' => 'Nava Sheva',
        ]);

        $responseInvoice = $this->actingAs($user)->get(route('invoices.pdf', $invoice));
        $responseInvoice->assertStatus(200);

        $responseEdf = $this->actingAs($user)->get(route('edfs.pdf', $edf));
        $responseEdf->assertStatus(200);
    }
}
