<?php

namespace App\Services;

use App\Events\SubagentActivity;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class ActivityLogger
{
    /**
     * Log an activity and broadcast it
     */
    public static function log(
        string $agentId,
        string $action,
        array $data = []
    ): ActivityLog {
        $agentName = self::getAgentName($agentId);
        
        $log = ActivityLog::create([
            'agent_id' => $agentId,
            'agent_name' => $agentName,
            'action' => $action,
            'task' => $data['task'] ?? '',
            'status' => $data['status'] ?? 'running',
            'tokens_used' => $data['tokens'] ?? 0,
            'runtime_ms' => $data['runtime'] ?? 0,
            'cost' => $data['cost'] ?? 0,
            'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
        ]);
        
        // Broadcast to WebSocket
        broadcast(new SubagentActivity(
            $agentId,
            $agentName,
            $action,
            $data['task'] ?? '',
            $data['status'] ?? 'running',
            $data['tokens'] ?? 0,
            $data['runtime'] ?? 0
        ));
        
        return $log;
    }
    
    private static function getAgentName(string $id): string
    {
        $names = [
            'main' => 'Luna',
            'pm' => 'Jordan',
            'dave' => 'Dave',
            'maya' => 'Maya',
            'chen' => 'Chen',
            'sam' => 'Sam',
            'alex' => 'Alex',
        ];
        
        return $names[$id] ?? ucfirst($id);
    }
    
    public static function getRecent(int $limit = 50): array
    {
        return ActivityLog::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
    
    public static function getStatsByAgent(int $days = 7): array
    {
        $stats = DB::connection('sqlite-activity')
            ->table('activity_logs')
            ->select('agent_id', 'agent_name', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('agent_id', 'agent_name')
            ->get()
            ->toArray();
        
        return $stats;
    }
}