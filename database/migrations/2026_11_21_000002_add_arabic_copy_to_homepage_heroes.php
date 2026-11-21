<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('homepage_heroes', function (Blueprint $table) {
            $table->string('headline_ar')->nullable();
            $table->text('subtext_ar')->nullable();
            $table->string('button_label_ar')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('homepage_heroes', function (Blueprint $table) {
            $table->dropColumn(['headline_ar', 'subtext_ar', 'button_label_ar']);
        });
    }
};
