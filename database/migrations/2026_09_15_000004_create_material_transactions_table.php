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
        Schema::create('material_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no', 40)->unique();

            // Primary Site Context
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials')->restrictOnDelete();

            // Movement Direction
            // 'in' = supplier delivery/purchase; 'out' = write-off/scrap;
            // 'transfer_out' = dispatched to another site; 'transfer_in' = received from another site;
            // 'adjustment' = inventory reconciliation count; 'damaged' = damaged on site; 'lost' = lost equipment
            $table->enum('direction', ['in', 'out', 'transfer_in', 'transfer_out', 'adjustment', 'damaged', 'lost'])->index();
            $table->decimal('quantity', 12, 2)->comment('Always positive in table; direction defines ledger sign');

            // Inter-Site Linkages
            $table->foreignId('from_site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('to_site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->unsignedBigInteger('linked_transaction_id')->nullable()->comment('Links corresponding transfer_in with transfer_out');

            // Metadata & References
            $table->string('ref_no', 100)->nullable()->index()->comment('Waybill / SIV / Delivery Note / Pad #');
            $table->date('transaction_date')->index();
            $table->text('notes')->nullable();

            // Workflow & Approval
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected'])->default('approved')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            // Accountability & Audit Trail
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Legacy Excel Import Traceability
            $table->string('source_sheet', 100)->nullable()->index();
            $table->unsignedInteger('source_row')->nullable();

            $table->timestamps();

            // Composite indexes for ledger running balance aggregation
            $table->index(['site_id', 'material_id', 'transaction_date', 'status'], 'idx_site_material_date_ledger');
            $table->index(['transaction_date', 'direction'], 'idx_date_direction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_transactions');
    }
};
