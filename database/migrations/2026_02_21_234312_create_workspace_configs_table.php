<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->nullable()->constrained()->onDelete('cascade');
            $table->longText('soul_md')->nullable();
            $table->longText('identity_md')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();

            $table->index('agent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_configs');
    }
};