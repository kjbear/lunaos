<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docs', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('section')->default('general');
            $table->longText('content');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('section');
        });

        // FTS5 virtual table for full-text search on docs
        DB::statement('
            CREATE VIRTUAL TABLE docs_fts USING fts5(
                title,
                content,
                content="docs",
                content_rowid="id"
            )
        ');

        // Triggers to keep FTS in sync
        DB::statement('
            CREATE TRIGGER docs_ai AFTER INSERT ON docs BEGIN
                INSERT INTO docs_fts(rowid, title, content)
                VALUES (new.id, new.title, new.content);
            END
        ');

        DB::statement('
            CREATE TRIGGER docs_ad AFTER DELETE ON docs BEGIN
                INSERT INTO docs_fts(docs_fts, rowid, title, content)
                VALUES ("delete", old.id, old.title, old.content);
            END
        ');

        DB::statement('
            CREATE TRIGGER docs_au AFTER UPDATE ON docs BEGIN
                INSERT INTO docs_fts(docs_fts, rowid, title, content)
                VALUES ("delete", old.id, old.title, old.content);
                INSERT INTO docs_fts(rowid, title, content)
                VALUES (new.id, new.title, new.content);
            END
        ');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS docs_au');
        DB::statement('DROP TRIGGER IF EXISTS docs_ad');
        DB::statement('DROP TRIGGER IF EXISTS docs_ai');
        DB::statement('DROP TABLE IF EXISTS docs_fts');
        Schema::dropIfExists('docs');
    }
};