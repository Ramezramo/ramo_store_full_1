<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vendor_applications', function (Blueprint $table) {
            $table->unsignedInteger('submission_count')->default(0);
        });
        DB::table('vendor_applications')->whereNotNull('submitted_at')->update(['submission_count' => 1]);
        Schema::create('vendor_rejection_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('label', 200);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('vendor_application_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('vendor_applications')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('submission_number');
            $table->string('decision', 20);
            $table->text('reason')->nullable();
            $table->timestamps();
        });
        Schema::create('user_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('vendor_applications')->nullOnDelete();
            $table->text('reason');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'resolved_at']);
        });
        $now = now();
        DB::table('vendor_rejection_reasons')->insert(array_map(fn ($label) => [
            'label' => $label, 'active' => true, 'created_at' => $now, 'updated_at' => $now,
        ], ['ID photo is unclear', 'Information does not match the ID', 'Incomplete application', 'Other verification concern']));
    }

    public function down(): void
    {
        Schema::dropIfExists('user_flags');
        Schema::dropIfExists('vendor_application_reviews');
        Schema::dropIfExists('vendor_rejection_reasons');
        Schema::table('vendor_applications', fn (Blueprint $table) => $table->dropColumn('submission_count'));
    }
};
