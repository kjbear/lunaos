<?php

namespace App\Livewire\Agents;

use App\Models\Agent;
use App\Agents\Strategies\StrategyRegistry;
use Livewire\Component;

class AgentForm extends Component
{
    public ?Agent $agent = null;
    
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
    
    public array $skillMetadata = [
        'triggers' => [],
        'constraints' => [
            'must_do' => [],
            'must_not' => [],
        ],
    ];
    
    public array $workflowConfig = [
        'next_step' => '',
        'next_assignee' => '',
    ];
    
    public function mount(?Agent $agent = null): void
    {
        if ($agent) {
            $this->agent = $agent;
            $this->name = $agent->name;
            $this->role = $agent->role ?? '';
            $this->type = $agent->type ?? 'worker';
            $this->emoji = $agent->emoji ?? '🤖';
            $this->model = $agent->model ?? 'qwen3-coder:latest';
            $this->provider = $agent->provider ?? 'ollama';
            $this->system_prompt = $agent->system_prompt ?? '';
            $this->strategy_class = $agent->model_settings['strategy_class'] ?? $agent->strategy_class ?? '';
            $this->step_filter = $agent->model_settings['step_filter'] ?? $agent->step_filter ?? '';
            $this->skill_doc_path = $agent->skill_doc_path ?? '';
            $this->is_online = $agent->is_online ?? false;
            $this->runtime_location = $agent->runtime_location ?? 'php';
            
            // Merge model settings with defaults
            $this->modelSettings = array_merge($this->modelSettings, $agent->model_settings ?? []);
            
            // Skill metadata
            if (!empty($agent->skill_metadata)) {
                $this->skillMetadata = array_merge($this->skillMetadata, $agent->skill_metadata);
            }
            
            // Workflow config
            if (!empty($agent->workflow_config)) {
                $this->workflowConfig = array_merge($this->workflowConfig, $agent->workflow_config);
            }
        }
    }
    
    public function updatedStrategyClass(string $value): void
    {
        // Auto-populate step filter based on strategy
        $strategySteps = StrategyRegistry::get($value)->getWorkflowSteps();
        $this->step_filter = implode(',', $strategySteps);
    }
    
    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|unique:agents,name,' . ($this->agent?->id ?? 'NULL'),
            'role' => 'required|string',
            'type' => 'required|in:worker,board,executive',
            'strategy_class' => 'required|in:' . implode(',', StrategyRegistry::keys()),
            'step_filter' => 'nullable|string',
            'skill_doc_path' => 'nullable|string',
            'model' => 'required|string',
            'provider' => 'required|string',
        ]);
        
        $data = [
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
            'model_settings' => array_merge(
                ['temperature' => 0.3, 'max_tokens' => 4096],
                $this->modelSettings
            ),
            'skill_metadata' => $this->skillMetadata,
            'workflow_config' => $this->workflowConfig,
        ];
        
        if ($this->agent) {
            $this->agent->update($data);
            $this->dispatch('agent-updated');
        } else {
            Agent::create($data);
            $this->dispatch('agent-created');
        }
    }
    
    public function render()
    {
        return view('livewire.agents.agent-form', [
            'strategies' => StrategyRegistry::all(),
        ]);
    }
}
