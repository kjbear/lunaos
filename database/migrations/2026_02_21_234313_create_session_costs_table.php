<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_costs', function (Blueprint $table) {
            $table->id();
            $table->string('model', 100);
            $table->unsignedBigInteger('tokens_input')->default(0);
            $table->unsignedBigInteger('tokens_output')->default(0);
            $table->decimal('cost', 10, 6)->default(0);
            $table->string('session_key')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['model', 'created_at']);
            $table->index('session_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_costs');
    }
};