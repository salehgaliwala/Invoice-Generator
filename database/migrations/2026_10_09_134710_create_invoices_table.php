<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('invoice_number', 16);
            $table->string('financial_year', 10);
            $table->date('invoice_date');
            $table->date('due_date')->nullable();

            $table->string('place_of_supply_state');
            $table->string('place_of_supply_state_code', 2);

            $table->string('transaction_type')->default('domestic'); // domestic, export_lut, export_igst
            $table->string('lut_number')->nullable();
            $table->date('lut_date')->nullable();

            $table->string('currency', 3)->default('INR');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);

            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('taxable_amount', 15, 2)->default(0.00);
            $table->decimal('cgst_amount', 15, 2)->default(0.00);
            $table->decimal('sgst_amount', 15, 2)->default(0.00);
            $table->decimal('igst_amount', 15, 2)->default(0.00);
            $table->decimal('total_tax_amount', 15, 2)->default(0.00);
            $table->decimal('round_off', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);

            $table->text('amount_in_words')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->string('status')->default('issued'); // draft, issued, paid, cancelled

            $table->timestamps();

            $table->unique(['company_id', 'invoice_number']);
            $table->index(['company_id', 'invoice_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
