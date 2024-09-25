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
        Schema::create('embeddingusers_collections', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('name');
            $table->jsonb('cmetadata')->nullable(); // Permettre NULL pour cmetadata
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('embeddingusers_collections');
    }
};
