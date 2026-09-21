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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique()->comment('e.g. EPU, MOJ II, GLP, CS');
            $table->string('name', 150);
            $table->string('client', 150)->nullable();
            $table->string('location', 255)->nullable();
            $table->boolean('is_central_store')->default(false)->index();
            $table->enum('status', ['active', 'completed', 'suspended'])->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
