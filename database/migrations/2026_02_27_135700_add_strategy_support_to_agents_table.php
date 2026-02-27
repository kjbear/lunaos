<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            // Strategy class name (e.g., 'develop', 'qa', 'deploy')
            if (!Schema::hasColumn('agents', 'strategy_class')) {
                $table->string('strategy_class')->nullable()->after('type');
            }
            
            // Workflow step filter (comma-separated: 'develop' or 'qa' or 'staging,production')
            if (!Schema::hasColumn('agents', 'step_filter')) {
                $table->string('step_filter')->nullable()->after('strategy_class');
            }
            
            // Strategy-specific workflow configuration
            if (!Schema::hasColumn('agents', 'workflow_config')) {
                $table->json('workflow_config')->nullable()->after('step_filter');
            }
        });
        
        // Update existing agents with strategy mappings
        DB::table('agents')->where('name', 'dave')->update([
            'strategy_class' => 'develop',
            'step_filter' => 'develop',
            'workflow_config' => json_encode([
                'next_step' => 'qa',
                'next_assignee' => 'sam',
            ]),
        ]);
        
        DB::table('agents')->where('name', 'sam')->update([
            'strategy_class' => 'qa',
            'step_filter' => 'qa',
            'workflow_config' => json_encode([
                'next_step' => 'security',
                'next_assignee' => 'security',
            ]),
        ]);
        
        DB::table('agents')->where('name', 'chen')->update([
            'strategy_class' => 'deploy',
            'step_filter' => 'staging,production',
            'workflow_config' => json_encode([
                'health_check_url' => '/health',
                'rollback_enabled' => true,
            ]),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            if (Schema::hasColumn('agents', 'workflow_config')) {
                $table->dropColumn('workflow_config');
            }
            if (Schema::hasColumn('agents', 'step_filter')) {
                $table->dropColumn('step_filter');
            }
            if (Schema::hasColumn('agents', 'strategy_class')) {
                $table->dropColumn('strategy_class');
            }
        });
    }
};
