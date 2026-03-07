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
        Schema::table('projects', function (Blueprint $table) {
            // Add architecture_type
            $table->string('architecture_type')->after('description')->nullable()
                  ->comment('monolith, microservices, serverless, etc');
            
            // Add technologies JSON field
            $table->json('technologies')->after('architecture_type')->nullable()
                  ->comment('["Laravel", "Vue", "MySQL"]');
            
            // Add project_manager_id foreign key
            $table->uuid('project_manager_id')->after('technologies')->nullable();
            $table->foreign('project_manager_id')->references('id')->on('agents')->onDelete('set null');
            
            // Add percent_complete
            $table->decimal('percent_complete', 5, 2)->after('progress')->default(0)
                  ->comment('Auto-calculated from task completion');
            
            // Add index
            $table->index('project_manager_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('project_manager_id');
            $table->dropForeign(['project_manager_id']);
            $table->dropColumn(['architecture_type', 'technologies', 'project_manager_id', 'percent_complete']);
        });
    }
};
