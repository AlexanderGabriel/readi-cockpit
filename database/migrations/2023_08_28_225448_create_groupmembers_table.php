<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Group;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('groupmembers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("email");
            $table->foreignIdFor(Group::class);
            $table->boolean("toBeInNextCloud")->default(1);
            $table->boolean("toBeInMailinglist")->default(1);
            $table->boolean("waitingForJoin")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groupmembers');
    }
};
