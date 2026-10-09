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
            $table->text('billing_address');
            $table->string('billing_city')->nullable();
            $table->string('billing_state');
            $table->string('billing_state_code', 2);
            $table->string('billing_country')->default('India');

            $table->text('shipping_address')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_state_code', 2)->nullable();
            $table->string('shipping_country')->nullable();

            $table->boolean('is_export')->default(false);
            $table->timestamps();

            $table->index(['company_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
