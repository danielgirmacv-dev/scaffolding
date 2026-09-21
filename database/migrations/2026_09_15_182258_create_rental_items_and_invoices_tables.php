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
        // Add multi-source and flexible rental support to rentals table
        Schema::table('rentals', function (Blueprint $table) {
            if (! Schema::hasColumn('rentals', 'rental_source')) {
                $table->string('rental_source', 30)->default('central_store')->after('site_id');
            }
            if (! Schema::hasColumn('rentals', 'external_vendor_name')) {
                $table->string('external_vendor_name', 150)->nullable()->after('rental_source');
            }
            if (Schema::hasColumn('rentals', 'material_id')) {
                $table->foreignId('material_id')->nullable()->change();
            }
        });

        // 1. rental_items: rental_id (FK), material_id (FK), quantity, unit_day_rate, eeig_discount_pct, effective_rate_per_day
        Schema::create('rental_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained('rentals')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials')->restrictOnDelete();
            $table->decimal('quantity', 12, 2)->default(1.00);
            $table->decimal('unit_day_rate', 12, 4)->default(0.0000);
            $table->decimal('eeig_discount_pct', 5, 2)->default(0.00);
            $table->decimal('effective_rate_per_day', 12, 4)->default(0.0000)
                ->comment('Calculated as: unit_day_rate * (1 - eeig_discount_pct / 100)');
            $table->timestamps();

            $table->index(['rental_id', 'material_id'], 'idx_rental_items_rental_material');
        });

        // 2. invoices: rental_id (FK), period_start, period_end, subtotal, discount_amount, vat_amount (15%), total_amount, payment_status
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 50)->unique();
            $table->foreignId('rental_id')->constrained('rentals')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedSmallInteger('rental_days')->default(0);
            $table->decimal('subtotal', 14, 2)->default(0.00);
            $table->decimal('discount_amount', 14, 2)->default(0.00);
            $table->decimal('vat_amount', 14, 2)->default(0.00)->comment('15% VAT');
            $table->decimal('total_amount', 14, 2)->default(0.00);
            $table->enum('payment_status', ['draft', 'issued', 'paid'])->default('draft')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['rental_id', 'period_start', 'period_end'], 'idx_invoice_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('rental_items');

        Schema::table('rentals', function (Blueprint $table) {
            if (Schema::hasColumn('rentals', 'external_vendor_name')) {
                $table->dropColumn('external_vendor_name');
            }
            if (Schema::hasColumn('rentals', 'rental_source')) {
                $table->dropColumn('rental_source');
            }
        });
    }
};
