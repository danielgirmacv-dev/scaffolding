<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('item_code', 50)->nullable()->unique();
            $table->string('name', 150)->index()->comment('e.g. Clamp, H-Frame, Top Jack, Inner Pipe');
            $table->string('category', 100)->default('Scaffolding')->index();
            $table->string('unit_of_measure', 30)->default('Pcs');

            // Rates & Costing
            $table->decimal('market_rate_per_day', 12, 4)->default(0.0000)->comment('Standard daily market rental rate');
            $table->decimal('eeig_discount_percent', 5, 2)->default(25.00)->comment('Default EEIG discount e.g. 25.00%');
            $table->decimal('depreciation_rate_per_day', 12, 4)->default(0.0000)->comment('Depreciation deduction/day if applicable');
            $table->decimal('replacement_cost', 12, 2)->default(0.00)->comment('Unit cost for lost/damaged write-offs');

            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
