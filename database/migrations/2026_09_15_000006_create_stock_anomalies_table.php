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
        Schema::create('stock_anomalies', function (Blueprint $table) {
            $table->id();
            $table->string('source_sheet', 100)->index();
            $table->unsignedInteger('source_row')->nullable();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->foreignId('material_transaction_id')->nullable()->constrained('material_transactions')->nullOnDelete();
            $table->decimal('calculated_negative_balance', 12, 2)->default(0.00);
            $table->string('error_type', 50)->default('NEGATIVE_BALANCE')->index();
            $table->text('message');
            $table->json('raw_payload')->nullable();
            $table->enum('status', ['open', 'investigating', 'resolved', 'ignored'])->default('open')->index();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_anomalies');
    }
};
