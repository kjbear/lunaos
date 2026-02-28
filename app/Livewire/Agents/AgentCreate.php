<?php

namespace App\Livewire\Agents;

use App\Models\Agent;
use App\Agents\Strategies\StrategyRegistry;
use Livewire\Component;

class AgentCreate extends Component
{
    // Form fields
    public string $name = '';
    public string $role = '';
    public string $type = 'worker';
    public string $emoji = '🤖';
    public string $model = 'qwen3-coder:latest';
    public string $provider = 'ollama';
    public string $system_prompt = '';
    public string $strategy_class = '';
    public string $step_filter = '';
    public string $skill_doc_path = '';
    public bool $is_online = false;
    public string $runtime_location = 'php';
    
    public array $modelSettings = [
        'temperature' => 0.3,
        'max_tokens' => 4096,
        'poll_interval' => 30,
    ];
    public array $skillMetadata = [];
    public array $workflowConfig = [];
    
    public function updatedStrategyClass($value)
    {
        if (!empty($value)) {
            $strategy = StrategyRegistry::get($value);
            if ($strategy) {
                $steps = $strategy->getWorkflowSteps();
                $this->step_filter = implode(',', $steps);
            }
        }
    }
    
    public function save()
    {
        \Log::info('🔵 SAVE METHOD CALLED: ' . $this->name);
        
        $this->validate([
            'name' => 'required|string|unique:agents,name',
            'role' => 'required|string',
        ]);
        
        \Log::info('🟢 Creating agent: ' . $this->name);
        
        Agent::create([
            'name' => $this->name,
            'role' => $this->role,
            'type' => $this->type,
            'emoji' => $this->emoji,
            'model' => $this->model,
            'provider' => $this->provider,
            'system_prompt' => $this->system_prompt,
            'strategy_class' => $this->strategy_class,
            'step_filter' => $this->step_filter,
            'skill_doc_path' => $this->skill_doc_path,
            'is_online' => $this->is_online,
            'runtime_location' => $this->runtime_location,
            'model_settings' => $this->modelSettings,
            'skill_metadata' => $this->skillMetadata,
            'workflow_config' => $this->workflowConfig,
        ]);
        
        \Log::info('🟡 Redirecting to /agents');
        
        return redirect()->to('/agents');
    }
    
    public function render()
    {
        return view('livewire.agents.agent-create', [
            'strategies' => StrategyRegistry::all(),
        ]);
    }
}
