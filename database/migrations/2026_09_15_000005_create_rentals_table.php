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
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->string('rental_no', 40)->unique();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials')->restrictOnDelete();

            $table->string('billing_period', 7)->index()->comment('YYYY-MM format e.g. 2026-09');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->unsignedSmallInteger('days_used')->default(0);
            $table->decimal('quantity_on_rent', 12, 2)->default(0.00);

            // Price Snapshots (immutable historical record)
            $table->decimal('market_rate_snapshot', 12, 4)->default(0.0000);
            $table->decimal('discount_percent_snapshot', 5, 2)->default(25.00);
            $table->decimal('depreciation_rate_snapshot', 12, 4)->default(0.0000);
            $table->decimal('effective_daily_rate', 12, 4)->default(0.0000)->comment('(Market * (1 - Disc%)) - Depr');

            // Financial Costing
            $table->decimal('subtotal_cost', 14, 2)->default(0.00);
            $table->decimal('vat_percent', 5, 2)->default(15.00);
            $table->decimal('vat_amount', 14, 2)->default(0.00);
            $table->decimal('grand_total_cost', 14, 2)->default(0.00);

            $table->enum('status', ['draft', 'approved', 'invoiced', 'closed'])->default('draft')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['site_id', 'billing_period'], 'idx_site_billing_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
