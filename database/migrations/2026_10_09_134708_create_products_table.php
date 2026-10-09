<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('hsn_sac_code');
            $table->text('description')->nullable();
            $table->string('uom')->default('PCS');
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('gst_rate', 5, 2)->default(18.00);
            $table->timestamps();

            $table->index(['company_id', 'hsn_sac_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
