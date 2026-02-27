<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sqlite-activity')->create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('agent_id');
            $table->string('agent_name');
            $table->string('action'); // spawned, completed, failed, spawned_child
            $table->string('task')->nullable();
            $table->string('status'); // running, done, failed
            $table->integer('tokens_used')->default(0);
            $table->integer('runtime_ms')->default(0);
            $table->decimal('cost', 8, 6)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('created_at');
            $table->index('agent_id');
        });
    }

    public function down(): void
    {
        Schema::connection('sqlite-activity')->dropIfExists('activity_logs');
    }
};