<?php

namespace App\Livewire;

use App\Models\Task;
use App\Models\Agent;
use App\Models\AgentActivity;
use Livewire\Component;
use Livewire\Attributes\On;

/**
 * TaskExecutive Component - Strategic/Summary view for tasks
 * 
 * High-level overview with metrics, trends, and strategic insights.
 * Perfect for executives or managers who need the big picture.
 */
class TaskExecutive extends Component
{
    public string $selectedPeriod = '7d'; // 24h, 7d, 30d, all
    public array $metrics = [];
    
    public function mount()
    {
        $this->loadMetrics();
    }
    
    #[On('task-updated')]
    public function refreshMetrics()
    {
        $this->loadMetrics();
    }
    
    /**
     * Load all metrics
     */
    public function loadMetrics(): void
    {
        $this->metrics = [
            'overview' => $this->getOverviewMetrics(),
            'byAgent' => $this->getAgentMetrics(),
            'byStep' => $this->getStepMetrics(),
            'trends' => $this->getTrendMetrics(),
            'bottlenecks' => $this->getBottleneckMetrics(),
        ];
    }
    
    /**
     * Get overview metrics
     */
    protected function getOverviewMetrics(): array
    {
        $total = Task::count();
        $completed = Task::where('status', 'complete')->count();
        $inProgress = Task::where('status', 'in_progress')->count();
        $pending = Task::where('status', 'pending')->count();
        $failed = Task::where('status', 'failed')->count();
        $blocked = Task::where('status', 'blocked')->count();
        
        $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
        $avgCycleTime = $this->calculateAverageCycleTime();
        $todaysCompletions = Task::where('status', 'complete')->whereDate('completed_at', today())->count();
        
        return [
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'pending' => $pending,
            'failed' => $failed,
            'blocked' => $blocked,
            'completion_rate' => $completionRate,
            'avg_cycle_time' => $avgCycleTime,
            'today_completions' => $todaysCompletions,
        ];
    }
    
    /**
     * Get metrics by agent
     */
    protected function getAgentMetrics(): array
    {
        $agents = ['dave', 'sam', 'chen', 'security'];
        $metrics = [];
        
        foreach ($agents as $agent) {
            $total = Task::where('assigned_to', $agent)->count();
            $completed = Task::where('assigned_to', $agent)->where('status', 'complete')->count();
            $inProgress = Task::where('assigned_to', $agent)->where('status', 'in_progress')->count();
            $failed = Task::where('assigned_to', $agent)->where('status', 'failed')->count();
            
            $metrics[$agent] = [
                'total' => $total,
                'completed' => $completed,
                'in_progress' => $inProgress,
                'failed' => $failed,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            ];
        }
        
        return $metrics;
    }
    
    /**
     * Get metrics by workflow step
     */
    protected function getStepMetrics(): array
    {
        $steps = ['develop', 'qa', 'security', 'staging', 'production'];
        $metrics = [];
        
        foreach ($steps as $step) {
            $count = Task::where('step', $step)->whereIn('status', ['pending', 'in_progress'])->count();
            $avgAge = $this->calculateAverageStepAge($step);
            
            $metrics[$step] = [
                'count' => $count,
                'avg_age_hours' => round($avgAge, 1),
                'label' => match($step) {
                    'develop' => 'Develop',
                    'qa' => 'QA',
                    'security' => 'Security',
                    'staging' => 'Staging',
                    'production' => 'Production',
                },
                'icon' => match($step) {
                    'develop' => '🔧',
                    'qa' => '🧪',
                    'security' => '🔒',
                    'staging' => '🚀',
                    'production' => '✅',
                },
            ];
        }
        
        return $metrics;
    }
    
    /**
     * Get trend metrics (last 7 days)
     */
    protected function getTrendMetrics(): array
    {
        $trends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $completed = Task::where('status', 'complete')
                ->whereDate('completed_at', $date)
                ->count();
            $trends[] = [
                'date' => $date,
                'label' => now()->copy()->subDays($i)->format('M j'),
                'completed' => $completed,
            ];
        }
        
        return array_reverse($trends);
    }
    
    /**
     * Get bottleneck metrics
     */
    protected function getBottleneckMetrics(): array
    {
        $bottlenecks = [];
        
        $steps = ['develop', 'qa', 'security', 'staging', 'production'];
        foreach ($steps as $step) {
            $oldTasks = Task::inStep($step)
                ->where('created_at', '<', now()->subHours(48))
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();
            
            if ($oldTasks > 0) {
                $bottlenecks[$step] = [
                    'count' => $oldTasks,
                    'severity' => $oldTasks >= 5 ? 'high' : ($oldTasks >= 2 ? 'medium' : 'low'),
                ];
            }
        }
        
        return $bottlenecks;
    }
    
    /**
     * Calculate average cycle time (in hours)
     */
    protected function calculateAverageCycleTime(): float
    {
        $tasks = Task::where('status', 'complete')
            ->whereNotNull('completed_at')
            ->limit(50)
            ->get();
        
        if ($tasks->isEmpty()) {
            return 0;
        }
        
        $totalHours = 0;
        foreach ($tasks as $task) {
            $hours = $task->created_at->diffInHours($task->completed_at);
            $totalHours += $hours;
        }
        
        return round($totalHours / $tasks->count(), 1);
    }
    
    /**
     * Calculate average age of tasks in a step
     */
    protected function calculateAverageStepAge(string $step): float
    {
        $tasks = Task::inStep($step)
            ->whereIn('status', ['pending', 'in_progress'])
            ->get();
        
        if ($tasks->isEmpty()) {
            return 0;
        }
        
        $totalHours = 0;
        foreach ($tasks as $task) {
            $hours = $task->created_at->diffInHours(now());
            $totalHours += $hours;
        }
        
        return $totalHours / $tasks->count();
    }
    
    /**
     * Get severity badge class
     */
    public function getSeverityClass(string $severity): string
    {
        return match($severity) {
            'high' => 'bg-red-500/20 text-red-400 border-red-500/30',
            'medium' => 'bg-orange-500/20 text-orange-400 border-orange-500/30',
            'low' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
            default => 'bg-slate-500/20 text-slate-400',
        };
    }
    
    public function render()
    {
        return view('livewire.task-executive', [
            'metrics' => $this->metrics,
        ])->layout('components.layouts.app', ['title' => 'Executive Dashboard']);
    }
}
