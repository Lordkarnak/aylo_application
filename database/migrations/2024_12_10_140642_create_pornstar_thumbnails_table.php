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
        Schema::create('pornstar_thumbnails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pornstar_id')->constrained();
            $table->integer('height')->nullable()->default(null);
            $table->integer('width')->nullable()->default(null);
            $table->enum('type', ['pc', 'mobile', 'tablet']);
            $table->tinyInteger('cached')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pornstar_thumbnails');
    }
};
