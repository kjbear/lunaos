<?php

namespace App\Livewire;

use Livewire\Component;

class Standup extends Component
{
    public ?string $selectedDate = null;
    public array $standups = [];
    public array $currentStandup = [];
    public bool $autoGenerate = true;
    public array $teamStatus = [];

    public function mount(): void
    {
        $this->selectedDate = date('Y-m-d');
        $this->loadStandups();
        $this->loadTeamStatus();
    }

    public function loadStandups(): void
    {
        // For now, generate a standup for today
        $this->currentStandup = $this->generateTodayStandup();
    }

    public function loadTeamStatus(): void
    {
        $dbPath = '/Users/kobear/.openclaw/workspace/projects.db';
        
        // Get task counts by agent
        $this->teamStatus = [
            'Dave' => ['completed' => 0, 'in_progress' => 0],
            'Maya' => ['completed' => 0, 'in_progress' => 0],
            'Chen' => ['completed' => 0, 'in_progress' => 0],
            'Sam' => ['completed' => 0, 'in_progress' => 0],
            'Alex' => ['completed' => 0, 'in_progress' => 0],
        ];

        if (file_exists($dbPath)) {
            $pdo = new \PDO('sqlite:' . $dbPath);
            $stmt = $pdo->query("SELECT assigned_to, status, COUNT(*) as count FROM tasks GROUP BY assigned_to, status");
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($results as $row) {
                $agent = $row['assigned_to'] ?? 'Unknown';
                if (!isset($this->teamStatus[$agent])) {
                    $this->teamStatus[$agent] = ['completed' => 0, 'in_progress' => 0];
                }
                
                if ($row['status'] === 'done') {
                    $this->teamStatus[$agent]['completed'] = (int)$row['count'];
                } elseif ($row['status'] === 'in_progress') {
                    $this->teamStatus[$agent]['in_progress'] = (int)$row['count'];
                }
            }
        }
    }

    public function generateTodayStandup(): array
    {
        $dbPath = '/Users/kobear/.openclaw/workspace/projects.db';
        
        $totalTasks = 0;
        $completedToday = 0;
        $inProgress = 0;
        $blocked = 0;

        if (file_exists($dbPath)) {
            $pdo = new \PDO('sqlite:' . $dbPath);
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM tasks");
            $totalTasks = (int)$stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'done'");
            $completedToday = (int)$stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'in_progress'");
            $inProgress = (int)$stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'blocked'");
            $blocked = (int)$stmt->fetchColumn();
        }

        return [
            'date' => $this->selectedDate,
            'time' => date('H:i'),
            'summary' => "Team completed {$completedToday} tasks. {$inProgress} tasks in progress. " . 
                        ($blocked > 0 ? "{$blocked} tasks blocked." : "No blockers reported."),
            'total_tasks' => $totalTasks,
            'completed' => $completedToday,
            'in_progress' => $inProgress,
            'blocked' => $blocked,
            'next_actions' => $inProgress > 0 
                ? "Continue work on {$inProgress} in-progress tasks."
                : "No active tasks. Ready for new assignments.",
        ];
    }

    public function refreshStandup(): void
    {
        $this->loadTeamStatus();
        $this->currentStandup = $this->generateTodayStandup();
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->loadStandups();
    }

    public function render()
    {
        return view('livewire.standup');
    }
}
