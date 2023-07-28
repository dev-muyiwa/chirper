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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string('full_name');
            $table->string('handle')->unique();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->longText("bio")->nullable();
            $table->string("avatar")->default("https://i.pravatar.cc/300");
            $table->string("banner")->default("https://picsum.photos/400/250");
            $table->string("location")->nullable();
            $table->date("dob")->nullable();
            $table->unsignedMediumInteger("chirp_count")->default(0);
            $table->unsignedTinyInteger("notifications_count")->default(0);
            $table->unsignedMediumInteger("followers_count")->default(0);
            $table->unsignedMediumInteger("followings_count")->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string("google_id")->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
