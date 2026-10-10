<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('gstin')->nullable();
            $table->string('pan')->nullable();

            // Billing Address
            $table->text('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_state_code', 2)->nullable();
            $table->string('billing_country')->default('India');

            // Shipping Address
            $table->text('shipping_address')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_state_code', 2)->nullable();
            $table->string('shipping_country')->default('India');

            $table->boolean('is_export')->default(false);

            // Bank & AD Details for EDF
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('bank_city')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ad_code')->nullable();
            $table->text('ad_name_address')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
