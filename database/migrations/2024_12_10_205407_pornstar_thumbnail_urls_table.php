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
        Schema::create('pornstar_thumbnail_urls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pornstar_id')->constrained();
            $table->foreignId('pornstar_thumbnail_id')->constrained();
            $table->text('url');
            $table->tinyInteger('cached')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pornstar_thumbnail_urls');
    }
};
