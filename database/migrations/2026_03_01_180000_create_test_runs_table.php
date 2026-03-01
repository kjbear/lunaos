<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_runs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('run_at')->useCurrent();
            $table->string('status'); // passed, failed, error
            $table->integer('total_tests')->default(0);
            $table->integer('passed')->default(0);
            $table->integer('failed')->default(0);
            $table->integer('skipped')->default(0);
            $table->float('coverage', 5, 2)->nullable();
            $table->integer('duration_ms')->default(0);
            $table->text('output')->nullable();
            $table->json('results')->nullable(); // detailed results per test
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_runs');
    }
};
