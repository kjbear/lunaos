<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('agent', 100);
            $table->string('action_type', 50);
            $table->string('action_name', 100);
            $table->json('context')->nullable();
            $table->string('impact', 50)->default('low');
            $table->enum('status', ['success', 'failed', 'pending'])->default('success');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['agent', 'created_at']);
            $table->index('action_type');
        });

        // FTS5 virtual table for full-text search
        DB::statement('
            CREATE VIRTUAL TABLE activity_logs_fts USING fts5(
                agent,
                action_type,
                action_name,
                context,
                content="activity_logs",
                content_rowid="id"
            )
        ');

        // Triggers to keep FTS in sync
        DB::statement('
            CREATE TRIGGER activity_logs_ai AFTER INSERT ON activity_logs BEGIN
                INSERT INTO activity_logs_fts(rowid, agent, action_type, action_name, context)
                VALUES (new.id, new.agent, new.action_type, new.action_name, json_extract(new.context, "$"));
            END
        ');

        DB::statement('
            CREATE TRIGGER activity_logs_ad AFTER DELETE ON activity_logs BEGIN
                INSERT INTO activity_logs_fts(activity_logs_fts, rowid, agent, action_type, action_name, context)
                VALUES ("delete", old.id, old.agent, old.action_type, old.action_name, json_extract(old.context, "$"));
            END
        ');

        DB::statement('
            CREATE TRIGGER activity_logs_au AFTER UPDATE ON activity_logs BEGIN
                INSERT INTO activity_logs_fts(activity_logs_fts, rowid, agent, action_type, action_name, context)
                VALUES ("delete", old.id, old.agent, old.action_type, old.action_name, json_extract(old.context, "$"));
                INSERT INTO activity_logs_fts(rowid, agent, action_type, action_name, context)
                VALUES (new.id, new.agent, new.action_type, new.action_name, json_extract(new.context, "$"));
            END
        ');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS activity_logs_au');
        DB::statement('DROP TRIGGER IF EXISTS activity_logs_ad');
        DB::statement('DROP TRIGGER IF EXISTS activity_logs_ai');
        DB::statement('DROP TABLE IF EXISTS activity_logs_fts');
        Schema::dropIfExists('activity_logs');
    }
};