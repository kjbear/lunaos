#!/usr/bin/env php
<?php

/**
 * Jordan (Project Manager) Agent Test Script
 * 
 * This script demonstrates Jordan's coordination capabilities:
 * 1. Shows current task state (blocked, unassigned, in-progress)
 * 2. Simulates Jordan's polling cycle
 * 3. Displays decisions made (reassignments, escalations, prioritizations)
 * 4. Logs all activity for visibility
 * 
 * Run: php jordan-test.php
 */

define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Agents\JordanAgentWorker;
use App\Models\Task;
use App\Models\Agent;
use App\Models\AgentActivity;

echo "📋 Jordan (PM) Agent Test\n";
echo str_repeat("=", 60) . "\n\n";

// Show current state
echo "📊 CURRENT TASK STATE\n";
echo str_repeat("-", 60) . "\n";

$blockedTasks = Task::where('status', 'blocked')->get();
echo "\n🚧 Blocked Tasks ({$blockedTasks->count()}):\n";
foreach ($blockedTasks as $task) {
    echo "  #{$task->id} [{$task->priority}] {$task->title}\n";
    echo "     → {$task->assigned_to} at step: {$task->step}\n";
    echo "     → {$task->description}\n\n";
}

$unassignedTasks = Task::whereNull('assigned_to')->where('status', 'pending')->get();
echo "\n📋 Unassigned Tasks ({$unassignedTasks->count()}):\n";
foreach ($unassignedTasks as $task) {
    echo "  #{$task->id} [{$task->priority}] {$task->title}\n";
    echo "     → Unassigned, step: {$task->step}\n";
    echo "     → {$task->description}\n\n";
}

$inProgressTasks = Task::where('status', 'in_progress')->get();
echo "\n⚡ In Progress Tasks ({$inProgressTasks->count()}):\n";
foreach ($inProgressTasks as $task) {
    echo "  #{$task->id} [{$task->priority}] {$task->title}\n";
    echo "     → {$task->assigned_to} at step: {$task->step}\n\n";
}

// Show agent team
echo "\n\n🤖 AGENT TEAM\n";
echo str_repeat("-", 60) . "\n";
$agents = Agent::all();
foreach ($agents as $agent) {
    $status = $agent->is_online ? '🟢' : '🔴';
    $type = ucfirst($agent->type ?? 'unknown');
    echo "{$status} {$agent->name} ({$type}) - {$agent->title}\n";
    echo "   Model: {$agent->model} @ {$agent->provider}\n";
    echo "   Capabilities: " . implode(', ', json_decode($agent->capabilities ?? '[]', true) ?? []) . "\n\n";
}

// Simulate one Jordan polling cycle
echo "\n\n🔄 SIMULATING JORDAN POLLING CYCLE\n";
echo str_repeat("-", 60) . "\n\n";

$activities = [];

// Handle blocked tasks
echo "🚧 Processing Blocked Tasks...\n";
foreach ($blockedTasks as $task) {
    echo "  Analyzing #{$task->id}: {$task->title}\n";
    
    // Simulate decision-making (without AI for this test)
    $decision = rand(0, 1) === 0 ? 'reassign' : 'escalate';
    
    if ($decision === 'reassign') {
        // Pick a different agent
        $otherAgents = ['dave', 'sam', 'chen', 'jordan'];
        $newAssignee = collect($otherAgents)->first(fn($a) => $a !== $task->assigned_to);
        
        $task->update([
            'assigned_to' => $newAssignee,
            'status' => 'pending',
            'updated_at' => now(),
        ]);
        
        AgentActivity::create([
            'task_id' => $task->id,
            'agent_name' => 'jordan',
            'action' => 'reassigned',
            'artifacts' => [
                'from' => $task->assigned_to,
                'to' => $newAssignee,
                'reason' => 'Blocked task reassigned to fresh perspective'
            ],
        ]);
        
        echo "    ✅ Reassigned from {$task->assigned_to} → {$newAssignee}\n";
        $activities[] = "Reassigned #{$task->id} to {$newAssignee}";
        
    } else {
        // Escalate
        AgentActivity::create([
            'task_id' => $task->id,
            'agent_name' => 'jordan',
            'action' => 'escalated',
            'artifacts' => [
                'reason' => 'Blocked for multiple iterations, needs human intervention',
                'blocked_since' => $task->updated_at
            ],
        ]);
        
        echo "    ⚠️  Escalated to Kyle (human) for decision\n";
        $activities[] = "Escalated #{$task->id}";
    }
}

// Handle unassigned tasks
echo "\n📋 Prioritizing Unassigned Tasks...\n";
$sorted = $unassignedTasks->sortByDesc('priority')->sortBy('created_at');

foreach ($sorted as $task) {
    echo "  Assigning #{$task->id}: {$task->title}\n";
    
    // Simple assignment logic based on task type
    $assignee = match($task->task_type) {
        'feature', 'refactor', 'bugfix' => 'dave',
        'testing' => 'sam',
        'devops', 'performance' => 'chen',
        'documentation' => 'dave',
        default => 'dave',
    };
    
    $task->update([
        'assigned_to' => $assignee,
        'status' => 'pending',
        'updated_at' => now(),
    ]);
    
    AgentActivity::create([
        'task_id' => $task->id,
        'agent_name' => 'jordan',
        'action' => 'assigned_by_jordan',
        'artifacts' => [
            'assignee' => $assignee,
            'priority' => $task->priority,
            'reasoning' => "Assigned based on task type: {$task->task_type}"
        ],
    ]);
    
    echo "    ✅ Assigned to {$assignee} (priority: {$task->priority})\n";
    $activities[] = "Assigned #{$task->id} to {$assignee}";
}

// Summary
echo "\n\n✅ JORDAN CYCLE COMPLETE\n";
echo str_repeat("-", 60) . "\n";
echo "Decisions made: " . count($activities) . "\n";
foreach ($activities as $i => $activity) {
    echo "  " . ($i + 1) . ". {$activity}\n";
}

echo "\n\n📊 UPDATED TASK STATE\n";
echo str_repeat("-", 60) . "\n";
$newBlocked = Task::where('status', 'blocked')->count();
$newUnassigned = Task::whereNull('assigned_to')->where('status', 'pending')->count();
echo "Blocked tasks: {$newBlocked} (was {$blockedTasks->count()})\n";
echo "Unassigned tasks: {$newUnassigned} (was {$unassignedTasks->count()})\n";

echo "\n\n💡 VIEW ACTIVITY LOG\n";
echo str_repeat("-", 60) . "\n";
echo "Recent Jordan activities:\n";
$recentActivities = AgentActivity::where('agent_name', 'jordan')
    ->latest()
    ->limit(10)
    ->get();
foreach ($recentActivities as $activity) {
    echo "  {$activity->created_at->format('H:i:s')} - {$activity->action} (Task #{$activity->task_id})\n";
}

echo "\n\n🌐 VIEW IN BROWSER: http://lunaos.test/activity\n";
echo str_repeat("=", 60) . "\n";
echo "✅ Test complete!\n\n";
