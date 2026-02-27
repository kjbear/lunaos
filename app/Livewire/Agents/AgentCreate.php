<?php

namespace App\Livewire\Agents;

use App\Models\Agent;
use App\Agents\Strategies\StrategyRegistry;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Create Agent')]
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
    
    public function updatedStrategyClass(string $value): void
    {
        if ($value) {
            $strategy = StrategyRegistry::get($value);
            if ($strategy) {
                $steps = $strategy->getWorkflowSteps();
                $this->step_filter = implode(',', $steps);
            }
        }
    }
    
    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|unique:agents,name',
            'role' => 'required|string',
            'type' => 'required|in:worker,board,executive',
            'strategy_class' => 'required|in:' . implode(',', StrategyRegistry::keys()),
            'model' => 'required|string',
            'provider' => 'required|string',
        ]);
        
        $agent = Agent::create([
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
        
        session()->flash('success', "Agent '{$this->name}' created successfully!");
        
        $this->redirect(route('agents.edit', $agent->id), navigate: true);
    }
    
    public function render()
    {
        return view('livewire.agents.agent-create', [
            'strategies' => StrategyRegistry::all(),
        ]);
    }
}
