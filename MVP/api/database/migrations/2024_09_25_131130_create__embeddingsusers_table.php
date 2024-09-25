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
        Schema::create('embeddingsusers', function (Blueprint $table) {
            $table->uuid("uuid")->primary();
            $table->uuid('embeddingusers_collections_id');
            $table->uuid("custom_id")->nullable();
            $table->json("cmetadata");
            $table->text("document");
            $table->timestamps();
        });

        DB::statement("ALTER TABLE embeddingsusers ADD COLUMN embedding vector;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('embeddingsusers');
    }
};
