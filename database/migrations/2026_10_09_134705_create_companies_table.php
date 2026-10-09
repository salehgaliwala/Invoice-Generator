<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('legal_name');
            $table->string('trade_name')->nullable();
            $table->string('gstin')->nullable()->index();
            $table->string('pan')->nullable();
            $table->string('iec')->nullable();
            $table->text('registered_address');
            $table->string('state');
            $table->string('state_code', 2);
            $table->string('authorized_signatory_name');
            $table->string('authorized_signatory_designation')->nullable();
            $table->string('bank_name');
            $table->string('bank_branch');
            $table->string('bank_account_number');
            $table->string('bank_ifsc');
            $table->string('bank_ad_code')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('invoice_prefix')->default('INV');
            $table->string('invoice_suffix')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
