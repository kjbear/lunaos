<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Standup;
use App\Models\StandupDeliverable;
use App\Models\StandupActionItem;
use App\Models\AgentUpdate;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class StandupRecorder extends Component
{
    // View state
    public string $view = 'archive'; // 'archive', 'meeting', 'new'
    public ?int $selectedStandupId = null;
    
    // Meeting form
    public string $date = '';
    public string $team = 'LunaOS Team';
    public string $facilitator = 'Luna';
    public string $title = '';
    
    // Agent updates (transcript cards)
    public array $agentUpdates = [];
    
    // Action items (extracted from standup)
    public array $actionItems = [];
    
    // Data
    public $recentStandups = [];
    public $selectedStandup = null;
    
    // Agent color palette
    private array $agentColors = [
        'Luna' => '#7c3aed',
        'Subagent-A' => '#10b981',
        'Subagent-B' => '#f59e0b',
        'Subagent-C' => '#ef4444',
        'Default' => '#6b7280',
    ];
    
    // Agent roles
    private array $agentRoles = [
        'Luna' => 'PM & Coordinator',
        'Subagent-A' => 'Backend Developer',
        'Subagent-B' => 'Frontend Developer',
        'Subagent-C' => 'QA Engineer',
    ];

    protected $rules = [
        'date' => 'required|date',
        'team' => 'required|string|max:100',
        'facilitator' => 'nullable|string|max:100',
        'title' => 'nullable|string|max:255',
        'agentUpdates' => 'array',
        'agentUpdates.*.agent_name' => 'required|string|max:100',
        'agentUpdates.*.done_yesterday' => 'nullable|string',
        'agentUpdates.*.doing_today' => 'nullable|string',
        'agentUpdates.*.blockers' => 'nullable|string',
        'actionItems' => 'array',
        'actionItems.*.title' => 'required|string|max:255',
        'actionItems.*.assigned_to' => 'nullable|string|max:100',
    ];

    public function mount(): void
    {
        $this->date = Carbon::now()->format('Y-m-d');
        
        // Try to load recent standups, but gracefully handle missing table
        try {
            $this->loadRecentStandups();
        } catch (\Illuminate\Database\QueryException $e) {
            // Table doesn't exist yet - use sample data
            $this->recentStandups = collect([
                (object)[
                    'id' => 1,
                    'team' => 'LunaOS Team',
                    'date' => Carbon::now(),
                    'facilitator' => 'Luna',
                    'agentUpdates' => collect([]),
                ],
            ]);
        }
        
        // View standup from query parameter
        if (request()->query('standup')) {
            $this->viewStandup((int) request()->query('standup'));
        }
    }

    public function loadRecentStandups(): void
    {
        try {
            $this->recentStandups = Standup::with(['agentUpdates', 'deliverables', 'actionItems'])
                ->recent(30)
                ->get();
        } catch (\Illuminate\Database\QueryException $e) {
            // Table doesn't exist - sample data
            $this->recentStandups = collect([
                (object)[
                    'id' => 1,
                    'team' => 'LunaOS Team',
                    'date' => Carbon::now(),
                    'facilitator' => 'Luna',
                    'agentUpdates' => collect([]),
                ],
            ]);
        }
    }

    /**
     * Show archive view
     */
    public function showArchive(): void
    {
        $this->view = 'archive';
        $this->selectedStandupId = null;
        $this->selectedStandup = null;
    }

    /**
     * View an existing standup
     */
    public function viewStandup(int $id): void
    {
        try {
            $this->selectedStandup = Standup::with(['agentUpdates', 'deliverables', 'actionItems'])
                ->findOrFail($id);
        } catch (\Illuminate\Database\QueryException $e) {
            // Table doesn't exist - show error
            session()->flash('error', 'Standup records not available (database not initialized)');
            $this->view = 'archive';
            return;
        }
        $this->selectedStandupId = $id;
        $this->view = 'meeting';
    }

    /**
     * Start a new standup
     */
    public function newStandup(): void
    {
        $this->resetForm();
        $this->date = Carbon::now()->format('Y-m-d');
        $this->title = $this->team . ' Standup - ' . Carbon::now()->format('M j, Y');
        $this->generateAgentUpdates();
        $this->view = 'new';
    }

    /**
     * Generate agent updates from activity logs
     */
    public function generateAgentUpdates(): void
    {
        $targetDate = Carbon::parse($this->date);
        $previousDate = Carbon::parse($this->date)->subDay();
        
        // Get active agents from activity logs
        $agents = ActivityLog::whereDate('created_at', '>=', $previousDate)
            ->select('agent', DB::raw('COUNT(*) as count'))
            ->groupBy('agent')
            ->pluck('agent')
            ->toArray();
        
        // Always include Luna
        if (!in_array('Luna', $agents)) {
            $agents[] = 'Luna';
        }
        
        $this->agentUpdates = [];
        $this->actionItems = [];
        $order = 0;
        
        foreach ($agents as $agent) {
            // Get yesterday's activity
            $yesterdayActivity = ActivityLog::where('agent', $agent)
                ->whereDate('created_at', $previousDate)
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Get today's activity
            $todayActivity = ActivityLog::where('agent', $agent)
                ->whereDate('created_at', $targetDate)
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Check for blockers (failed activities)
            $failedActivities = $yesterdayActivity->merge($todayActivity)
                ->where('status', 'failed');
            
            $blockers = $failedActivities->isNotEmpty() 
                ? $this->formatBlockers($failedActivities) 
                : 'None';
            
            $doingToday = $this->inferTodayWork($todayActivity, $yesterdayActivity);
            
            $this->agentUpdates[] = [
                'agent_name' => $agent,
                'agent_role' => $this->agentRoles[$agent] ?? 'Team Member',
                'agent_color' => $this->agentColors[$agent] ?? $this->agentColors['Default'],
                'done_yesterday' => $this->summarizeActivity($yesterdayActivity),
                'doing_today' => $doingToday,
                'blockers' => $blockers,
                'order' => $order++,
            ];
            
            // Auto-generate action items from blockers
            if ($blockers !== 'None') {
                foreach ($failedActivities as $failed) {
                    $this->actionItems[] = [
                        'title' => "Resolve blocker: {$failed->action_name}",
                        'assigned_to' => $agent,
                        'source' => 'blocker',
                        'completed' => false,
                    ];
                }
            }
            
            // Generate action items from "doing today" if actionable
            if ($doingToday && $doingToday !== 'Awaiting tasks' && !str_starts_with($doingToday, 'Follow up:')) {
                $this->actionItems[] = [
                    'title' => $doingToday,
                    'assigned_to' => $agent,
                    'source' => 'planned',
                    'completed' => false,
                ];
            }
        }
    }

    /**
     * Summarize activity for "done yesterday"
     */
    private function summarizeActivity($activities): string
    {
        if ($activities->isEmpty()) {
            return 'No activity recorded';
        }
        
        $byType = $activities->groupBy('action_type');
        $summary = [];
        
        foreach ($byType as $type => $items) {
            $count = $items->count();
            $latestName = $items->first()->action_name;
            $summary[] = "{$count}x {$type}" . ($count === 1 ? " ({$latestName})" : '');
        }
        
        return implode(', ', $summary);
    }

    /**
     * Infer today's work from activity
     */
    private function inferTodayWork($todayActivity, $yesterdayActivity): string
    {
        if ($todayActivity->isEmpty()) {
            // Check for pending tasks from yesterday
            $pending = $yesterdayActivity->where('status', 'pending');
            if ($pending->isNotEmpty()) {
                return 'Follow up: ' . $pending->first()->action_name;
            }
            return 'Awaiting tasks';
        }
        
        $highImpact = $todayActivity->where('impact', 'high');
        if ($highImpact->isNotEmpty()) {
            return $highImpact->first()->action_name;
        }
        
        return $todayActivity->first()->action_name;
    }

    /**
     * Format blockers from failed activities
     */
    private function formatBlockers($failedActivities): string
    {
        return $failedActivities->map(fn($a) => $a->action_name)->join('; ');
    }

    /**
     * Add an agent to the standup
     */
    public function addAgent(string $name): void
    {
        $this->agentUpdates[] = [
            'agent_name' => $name,
            'agent_role' => $this->agentRoles[$name] ?? 'Team Member',
            'agent_color' => $this->agentColors[$name] ?? $this->agentColors['Default'],
            'done_yesterday' => '',
            'doing_today' => '',
            'blockers' => 'None',
            'order' => count($this->agentUpdates),
        ];
    }

    /**
     * Remove an agent from the standup
     */
    public function removeAgent(int $index): void
    {
        unset($this->agentUpdates[$index]);
        $this->agentUpdates = array_values($this->agentUpdates);
    }

    /**
     * Add an action item
     */
    public function addActionItem(): void
    {
        $this->actionItems[] = [
            'title' => '',
            'assigned_to' => '',
            'source' => 'manual',
            'completed' => false,
        ];
    }

    /**
     * Remove an action item
     */
    public function removeActionItem(int $index): void
    {
        unset($this->actionItems[$index]);
        $this->actionItems = array_values($this->actionItems);
    }

    /**
     * Toggle action item completion
     */
    public function toggleActionItem(int $index): void
    {
        $this->actionItems[$index]['completed'] = !$this->actionItems[$index]['completed'];
    }

    /**
     * Save the standup
     */
    public function save(): void
    {
        $this->validate();

        $standup = Standup::create([
            'date' => $this->date,
            'team' => $this->team,
            'facilitator' => $this->facilitator ?: 'Luna',
            'transcript' => $this->buildTranscript(),
            'status' => Standup::STATUS_COMPLETED,
        ]);

        foreach ($this->agentUpdates as $index => $update) {
            AgentUpdate::create([
                'standup_id' => $standup->id,
                'agent_name' => $update['agent_name'],
                'agent_role' => $update['agent_role'] ?? 'Team Member',
                'agent_color' => $update['agent_color'] ?? '#7c3aed',
                'done_yesterday' => $update['done_yesterday'] ?? null,
                'doing_today' => $update['doing_today'] ?? null,
                'blockers' => $update['blockers'] ?? null,
                'order' => $index,
            ]);
        }

        // Save action items
        foreach ($this->actionItems as $item) {
            if (!empty($item['title'])) {
                StandupActionItem::create([
                    'standup_id' => $standup->id,
                    'title' => $item['title'],
                    'assigned_to' => $item['assigned_to'] ?? null,
                    'completed' => $item['completed'] ?? false,
                ]);
            }
        }

        $this->loadRecentStandups();
        $this->viewStandup($standup->id);
        
        $this->dispatch('toast-success', message: 'Standup saved successfully!');
    }

    /**
     * Build transcript from agent updates
     */
    private function buildTranscript(): string
    {
        $lines = ["# {$this->team} Standup - " . Carbon::parse($this->date)->format('l, F j, Y')];
        $lines[] = "Facilitator: {$this->facilitator}";
        $lines[] = "";
        
        foreach ($this->agentUpdates as $update) {
            $lines[] = "## {$update['agent_name']} ({$update['agent_role']})";
            if (!empty($update['done_yesterday'])) {
                $lines[] = "Done: {$update['done_yesterday']}";
            }
            if (!empty($update['doing_today'])) {
                $lines[] = "Next: {$update['doing_today']}";
            }
            if (!empty($update['blockers']) && $update['blockers'] !== 'None') {
                $lines[] = "Blockers: {$update['blockers']}";
            }
            $lines[] = "";
        }
        
        if (!empty($this->actionItems)) {
            $lines[] = "## Action Items";
            foreach ($this->actionItems as $item) {
                $status = ($item['completed'] ?? false) ? '✓' : '○';
                $assigned = !empty($item['assigned_to']) ? " (@{$item['assigned_to']})" : '';
                $lines[] = "- {$status} {$item['title']}{$assigned}";
            }
        }
        
        return implode("\n", $lines);
    }

    /**
     * Delete a standup
     */
    public function deleteStandup(int $id): void
    {
        Standup::destroy($id);
        $this->loadRecentStandups();
        $this->showArchive();
        
        $this->dispatch('toast-info', message: 'Standup deleted');
    }

    private function resetForm(): void
    {
        $this->agentUpdates = [];
        $this->actionItems = [];
        $this->title = '';
        $this->selectedStandupId = null;
        $this->selectedStandup = null;
    }

    public function render()
    {
        return view('livewire.standup-recorder');
    }
}