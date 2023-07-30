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
        Schema::create('posts', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string("title");
            $table->json("media")->nullable();
            $table->boolean("is_draft")->default(false);
            $table->unsignedTinyInteger("comments_count")->default(0);
            $table->unsignedTinyInteger("likes_count")->default(0);
            $table->unsignedSmallInteger("view_count")->default(0);

            $table->foreignUuid('user_id')->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamp("created_at")->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
