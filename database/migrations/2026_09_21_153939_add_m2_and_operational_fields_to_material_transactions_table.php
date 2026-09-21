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
        Schema::table('material_transactions', function (Blueprint $table) {
            $table->decimal('m2_coverage', 12, 2)->nullable()->after('quantity')
                ->comment('Square metre (M2) or SET coverage of scaffolding area');
            $table->string('maintenance_status', 30)->default('none')->after('direction')->index()
                ->comment('none, in_maintenance, cleared, scrapped');
            $table->string('production_stage', 30)->default('none')->after('maintenance_status')->index()
                ->comment('none, on_process, finished');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_transactions', function (Blueprint $table) {
            $table->dropColumn(['m2_coverage', 'maintenance_status', 'production_stage']);
        });
    }
};
