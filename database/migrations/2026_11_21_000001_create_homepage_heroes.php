<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('homepage_heroes', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->boolean('enabled')->default(false);
            $table->string('media_type', 5)->default('image');
            $table->string('media_path')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('headline')->nullable();
            $table->text('subtext')->nullable();
            $table->string('button_label')->nullable();
            $table->text('button_link')->nullable();
            $table->timestamps();
        });
        DB::table('homepage_heroes')->insert(['id' => 1]);
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_heroes');
    }
};
