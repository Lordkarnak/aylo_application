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
            $table->string("name", 400)->nullable()->default(null);
            $table->string("license")->nullable()->default(null);
            $table->smallInteger("wl_status")->nullable()->default(null);
            $table->text("link")->nullable()->default(null);

            // attributes
            $table->string("hair_color", 40)->nullable()->default(null);
            $table->string("ethnicity", 40)->nullable()->default(null);
            $table->string("tattoos", 40)->nullable()->default(null);
            $table->string("piercings", 40)->nullable()->default(null);
            $table->string("breast_size", 40)->nullable()->default(null);
            $table->string("breast_type", 5)->nullable()->default(null);
            $table->string("gender", 20)->nullable()->default(null);
            $table->string("orientation", 40)->nullable()->default(null);
            $table->unsignedInteger("age")->nullable()->default(null);

            // stats
            $table->mediumInteger("subscriptions")->nullable()->default(null);
            $table->mediumInteger("monthly_searches")->nullable()->default(null);
            $table->mediumInteger("views")->nullable()->default(null);
            $table->mediumInteger("videos_count")->nullable()->default(null);
            $table->mediumInteger("premium_videos_count")->nullable()->default(null);
            $table->mediumInteger("white_label_video_count")->nullable()->default(null);
            $table->mediumInteger("rank")->nullable()->default(null);
            $table->mediumInteger("rank_premium")->nullable()->default(null);
            $table->mediumInteger("rank_wl")->nullable()->default(null);

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
