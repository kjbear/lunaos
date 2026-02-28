<?php

namespace App\Livewire\Agents;

use App\Models\Agent;
use App\Agents\Strategies\StrategyRegistry;
use Livewire\Component;

class AgentEdit extends Component
{
    public Agent $agent;
    
    // Form fields
    public string $name = '';
    public string $role = '';
    public string $type = 'worker';
    public string $emoji = '🤖';
    public string $model = '';
    public string $provider = 'ollama';
    public string $system_prompt = '';
    public string $strategy_class = '';
    public string $step_filter = '';
    public string $skill_doc_path = '';
    public bool $is_online = false;
    public string $runtime_location = 'php';
    public ?int $agentId = null;
    
    public array $modelSettings = [];
    public array $skillMetadata = [];
    public array $workflowConfig = [];
    
    public function mount(int $id): void
    {
        $this->agentId = $id;
        $this->agent = Agent::findOrFail($id);
        
        // Debug logging
        \Log::info('AgentEdit mount', [
            'id' => $id,
            'agent_name' => $this->agent->name,
            'agent_role' => $this->agent->role,
        ]);
        
        // Populate form fields from agent
        $this->name = (string) $this->agent->name;
        $this->role = (string) ($this->agent->role ?? '');
        $this->type = (string) ($this->agent->type ?? 'worker');
        $this->emoji = (string) ($this->agent->emoji ?? '🤖');
        $this->model = (string) ($this->agent->model ?? '');
        $this->provider = (string) ($this->agent->provider ?? 'ollama');
        $this->system_prompt = (string) ($this->agent->system_prompt ?? '');
        $this->strategy_class = (string) ($this->agent->strategy_class ?? '');
        $this->step_filter = (string) ($this->agent->step_filter ?? '');
        $this->skill_doc_path = (string) ($this->agent->skill_doc_path ?? '');
        $this->is_online = (bool) $this->agent->is_online;
        $this->runtime_location = (string) ($this->agent->runtime_location ?? 'php');
        
        // Debug: Log what we're setting
        \Log::info('AgentEdit properties set', [
            'name' => $this->name,
            'role' => $this->role,
            'strategy_class' => $this->strategy_class,
            'step_filter' => $this->step_filter,
        ]);
        
        // Cast JSON fields properly
        $this->modelSettings = is_array($this->agent->model_settings) ? $this->agent->model_settings : [];
        $this->skillMetadata = is_array($this->agent->skill_metadata) ? $this->agent->skill_metadata : [];
        $this->workflowConfig = is_array($this->agent->workflow_config) ? $this->agent->workflow_config : [];
        
        // Force re-render after mount
        $this->dispatch('agent-loaded', [
            'name' => $this->name,
            'emoji' => $this->emoji,
            'role' => $this->role,
        ])->self();
    }
    
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
    
    public function hydrate(): void
    {
        // Ensure agent is loaded on every hydration
        if ($this->agentId && !$this->agent?->id) {
            $this->agent = Agent::find($this->agentId);
            \Log::info('AgentEdit hydrated', ['agent' => $this->agent?->name]);
        }
    }
    
    public function updated(string $field, mixed $value): void
    {
        \Log::info('AgentEdit field updated', ['field' => $field, 'value' => $value]);
    }
    
    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|unique:agents,name,' . $this->agent->id,
            'role' => 'required|string',
            'type' => 'required|in:worker,board,executive',
            'strategy_class' => 'required|in:' . implode(',', StrategyRegistry::keys()),
            'model' => 'required|string',
            'provider' => 'required|string',
        ]);
        
        $this->agent->update([
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
            'model_settings' => array_merge(['temperature' => 0.3, 'max_tokens' => 4096, 'poll_interval' => 30], $this->modelSettings),
            'skill_metadata' => $this->skillMetadata,
            'workflow_config' => $this->workflowConfig,
        ]);
        
        session()->flash('success', "Agent '{$this->name}' updated successfully!");
        
        redirect()->route('agents.index');
    }
    
    public function delete(): void
    {
        if (in_array($this->agent->name, ['dave', 'sam', 'chen'])) {
            session()->flash('error', 'Cannot delete core agents (dave, sam, chen).');
            return;
        }
        
        $name = $this->agent->name;
        $this->agent->delete();
        
        session()->flash('success', "Agent '{$name}' deleted successfully!");
        
        redirect()->route('agents.index');
    }
    
    public function render()
    {
        return view('livewire.agents.agent-edit', [
            'strategies' => StrategyRegistry::all(),
        ]);
    }
}
