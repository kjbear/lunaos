<?php

namespace App\Livewire\Team;

use App\Models\TeamMember;
use Livewire\Component;

class MemberAiConfig extends Component
{
    public ?TeamMember $member = null;
    
    // Model Settings
    public string $model = 'gpt-4-turbo';
    public float $temperature = 0.7;
    public int $maxTokens = 4096;
    public float $topP = 1.0;
    public float $frequencyPenalty = 0.0;
    public float $presencePenalty = 0.0;
    
    // Persona & Prompt
    public string $personaName = '';
    public string $personaDescription = '';
    public string $systemPrompt = '';
    public string $responseStyle = 'balanced';
    public string $specialInstructions = '';
    
    // Capabilities
    public array $skills = [];
    public array $capabilities = [];
    public array $specializations = [];
    
    // Operations
    public bool $isAvailable = true;
    public int $capacity = 80;
    public int $maxConcurrentTasks = 5;
    public bool $autoAssign = false;
    public string $priorityLevel = 'normal';
    
    // Custom Metadata
    public string $customMetadata = '{}';
    
    // Available options
    public array $availableModels = [
        'claude-3-opus' => 'Claude 3 Opus',
        'claude-3-sonnet' => 'Claude 3 Sonnet',
        'claude-3-haiku' => 'Claude 3 Haiku',
        'gpt-4-turbo' => 'GPT-4 Turbo',
        'gpt-4' => 'GPT-4',
        'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
        'gemini-pro' => 'Gemini Pro',
        'mistral-large' => 'Mistral Large',
        'mistral-medium' => 'Mistral Medium',
    ];
    
    public array $responseStyles = [
        'concise' => 'Concise - Brief, to-the-point responses',
        'balanced' => 'Balanced - Moderate detail level',
        'detailed' => 'Detailed - Comprehensive explanations',
        'creative' => 'Creative - More varied, imaginative responses',
    ];
    
    public array $priorityLevels = [
        'low' => 'Low Priority',
        'normal' => 'Normal Priority',
        'high' => 'High Priority',
        'critical' => 'Critical Priority',
    ];
    
    public array $availableCapabilities = [
        'code-generation' => 'Code Generation',
        'code-review' => 'Code Review',
        'debugging' => 'Debugging',
        'documentation' => 'Documentation',
        'testing' => 'Testing',
        'research' => 'Research',
        'analysis' => 'Analysis',
        'writing' => 'Writing',
        'translation' => 'Translation',
        'summarization' => 'Summarization',
        'planning' => 'Planning',
        'refactoring' => 'Refactoring',
    ];
    
    protected $rules = [
        'model' => 'required|string',
        'temperature' => 'required|numeric|min:0|max:2',
        'maxTokens' => 'required|integer|min:1|max:128000',
        'topP' => 'required|numeric|min:0|max:1',
        'frequencyPenalty' => 'required|numeric|min:-2|max:2',
        'presencePenalty' => 'required|numeric|min:-2|max:2',
        'personaName' => 'nullable|string|max:255',
        'personaDescription' => 'nullable|string',
        'systemPrompt' => 'nullable|string',
        'responseStyle' => 'required|string',
        'specialInstructions' => 'nullable|string',
        'isAvailable' => 'boolean',
        'capacity' => 'required|integer|min:0|max:100',
        'maxConcurrentTasks' => 'required|integer|min:1|max:100',
        'autoAssign' => 'boolean',
        'priorityLevel' => 'required|string',
        'customMetadata' => 'nullable|json',
    ];
    
    public function mount(?string $memberId = null): void
    {
        if ($memberId) {
            $this->member = TeamMember::find($memberId);
            $this->loadConfig();
        }
    }
    
    public function loadConfig(): void
    {
        if (!$this->member) {
            return;
        }
        
        // Load from metadata/settings if available
        $config = $this->member->metadata_json['ai_config'] ?? [];
        
        // Model Settings
        $this->model = $config['model'] ?? $this->member->model ?? 'gpt-4-turbo';
        $this->temperature = $config['temperature'] ?? 0.7;
        $this->maxTokens = $config['max_tokens'] ?? 4096;
        $this->topP = $config['top_p'] ?? 1.0;
        $this->frequencyPenalty = $config['frequency_penalty'] ?? 0.0;
        $this->presencePenalty = $config['presence_penalty'] ?? 0.0;
        
        // Persona & Prompt
        $this->personaName = $config['persona_name'] ?? $this->member?->title ?? '';
        $this->personaDescription = $config['persona_description'] ?? '';
        $this->systemPrompt = $config['system_prompt'] ?? '';
        $this->responseStyle = $config['response_style'] ?? 'balanced';
        $this->specialInstructions = $config['special_instructions'] ?? '';
        
        // Capabilities
        $this->skills = $config['skills'] ?? [];
        $this->capabilities = $config['capabilities'] ?? [];
        $this->specializations = $config['specializations'] ?? [];
        
        // Operations
        $this->isAvailable = $config['is_available'] ?? true;
        $this->capacity = $config['capacity'] ?? 80;
        $this->maxConcurrentTasks = $config['max_concurrent_tasks'] ?? 5;
        $this->autoAssign = $config['auto_assign'] ?? false;
        $this->priorityLevel = $config['priority_level'] ?? 'normal';
        
        // Custom Metadata
        $this->customMetadata = json_encode($config['custom_metadata'] ?? new \stdClass(), JSON_PRETTY_PRINT);
    }
    
    public function save(): void
    {
        $this->validate();
        
        if (!$this->member) {
            $this->dispatch('toast-error', message: 'No member selected');
            return;
        }
        
        // Build config array
        $config = [
            'model' => $this->model,
            'temperature' => (float) $this->temperature,
            'max_tokens' => (int) $this->maxTokens,
            'top_p' => (float) $this->topP,
            'frequency_penalty' => (float) $this->frequencyPenalty,
            'presence_penalty' => (float) $this->presencePenalty,
            'persona_name' => $this->personaName,
            'persona_description' => $this->personaDescription,
            'system_prompt' => $this->systemPrompt,
            'response_style' => $this->responseStyle,
            'special_instructions' => $this->specialInstructions,
            'skills' => $this->skills,
            'capabilities' => $this->capabilities,
            'specializations' => $this->specializations,
            'is_available' => $this->isAvailable,
            'capacity' => (int) $this->capacity,
            'max_concurrent_tasks' => (int) $this->maxConcurrentTasks,
            'auto_assign' => $this->autoAssign,
            'priority_level' => $this->priorityLevel,
            'custom_metadata' => json_decode($this->customMetadata, true) ?? new \stdClass(),
        ];
        
        // Update member metadata
        $metadata = $this->member->metadata_json ?? [];
        $metadata['ai_config'] = $config;
        $this->member->metadata_json = $metadata;
        $this->member->save();
        
        $this->dispatch('toast-success', message: 'AI Configuration saved successfully!');
    }
    
    public function resetToDefaults(): void
    {
        $this->model = 'gpt-4-turbo';
        $this->temperature = 0.7;
        $this->maxTokens = 4096;
        $this->topP = 1.0;
        $this->frequencyPenalty = 0.0;
        $this->presencePenalty = 0.0;
        $this->personaName = $this->member?->title ?? '';
        $this->personaDescription = '';
        $this->systemPrompt = '';
        $this->responseStyle = 'balanced';
        $this->specialInstructions = '';
        $this->skills = [];
        $this->capabilities = [];
        $this->specializations = [];
        $this->isAvailable = true;
        $this->capacity = 80;
        $this->maxConcurrentTasks = 5;
        $this->autoAssign = false;
        $this->priorityLevel = 'normal';
        $this->customMetadata = '{}';
        
        $this->dispatch('toast-info', message: 'Form reset to defaults');
    }
    
    public function toggleCapability(string $capability): void
    {
        $index = array_search($capability, $this->capabilities);
        if ($index !== false) {
            unset($this->capabilities[$index]);
            $this->capabilities = array_values($this->capabilities);
        } else {
            $this->capabilities[] = $capability;
        }
    }
    
    public function render()
    {
        return view('livewire.team.member-ai-config');
    }
}