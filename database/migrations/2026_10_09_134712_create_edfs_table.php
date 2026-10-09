<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edfs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('edf_number')->nullable();
            $table->string('iec_number');
            $table->string('ad_code');
            $table->string('port_of_export');

            $table->string('shipping_bill_number')->nullable();
            $table->date('shipping_bill_date')->nullable();

            $table->string('cha_name')->nullable();
            $table->string('cha_license_number')->nullable();

            $table->string('vessel_flight_no')->nullable();
            $table->string('port_of_loading')->nullable();
            $table->string('port_of_discharge')->nullable();

            $table->string('nature_of_contract', 10)->default('FOB'); // FOB, CIF, CFR, C&F
            $table->string('currency_of_realization', 3)->default('USD');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            $table->decimal('invoice_value_fcy', 15, 2)->default(0.00);
            $table->decimal('total_realizable_value_fcy', 15, 2)->default(0.00);
            $table->decimal('total_realizable_value_inr', 15, 2)->default(0.00);

            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edfs');
    }
};
