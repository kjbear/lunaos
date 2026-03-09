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
        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->boolean('is_archived')->default(false)->after('metadata');
            $table->timestamp('archived_at')->nullable()->after('is_archived');
            
            // Index for filtering by archived status
            $table->index(['is_archived', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->dropIndex(['is_archived', 'updated_at']);
            $table->dropColumn(['is_archived', 'archived_at']);
        });
    }
};