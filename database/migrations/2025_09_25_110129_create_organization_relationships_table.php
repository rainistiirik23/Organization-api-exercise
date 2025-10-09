<?php

use App\Models\Organization;
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
        Schema::create('organization_relationships', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class, 'parent_id')->constrained('organizations', 'id')->onDelete('cascade');;
            $table->foreignIdFor(Organization::class, 'daughter_id')->constrained('organizations', 'id')->onDelete('cascade');
            $table->unique(['parent_id', 'daughter_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_relationships');
    }
};
