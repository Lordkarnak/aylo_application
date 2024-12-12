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
        Schema::create('pornstars', function (Blueprint $table) {
            // base data
            $table->id();
            $table->string("name", 400);
            $table->string("license");
            $table->smallInteger("wl_status");
            $table->text("link");

            // attributes
            $table->string("hair_color", 40);
            $table->string("ethnicity", 40);
            $table->string("tattoos", 40);
            $table->string("piercings", 40);
            $table->string("breast_size", 40);
            $table->string("breast_type", 5);
            $table->string("gender", 20);
            $table->string("orientation", 40);
            $table->unsignedInteger("age");

            // stats
            $table->mediumInteger("subscriptions");
            $table->mediumInteger("monthly_searches");
            $table->mediumInteger("views");
            $table->mediumInteger("videos_count");
            $table->mediumInteger("premium_videos_count");
            $table->mediumInteger("white_label_video_count");
            $table->mediumInteger("rank");
            $table->mediumInteger("rank_premium");
            $table->mediumInteger("rank_wl");

            // stamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pornstars');
    }
};
