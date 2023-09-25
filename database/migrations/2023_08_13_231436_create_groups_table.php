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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("name")->unique();
            $table->string("description")->nullable();
            $table->string("keycloakGroup")->nullable();
            $table->string("keycloakAdminGroup")->nullable();
            $table->boolean("moderated")->default(0);
            $table->boolean("has_mailinglist")->default(0);
            $table->string("mailingListURL")->nullable();
            $table->string("mailingListAdmin")->nullable();
            $table->string("mailingListPassword")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
