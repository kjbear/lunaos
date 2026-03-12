#!/bin/bash
# Quick agent restore script - use when Dave/agents disappear
# Usage: ./scripts/restore-agents.sh

cd ~/.openclaw/workspace/lunaos

echo "🔧 Restoring agents to database..."

php artisan tinker --execute="
use App\Models\Agent;
use App\Models\TeamMember;
use Illuminate\Support\Str;

\$agents = [
  ['name' => 'dave', 'strategy' => 'develop', 'title' => 'Senior Developer', 'emoji' => '🔧', 'model' => 'glm-5'],
  ['name' => 'sam', 'strategy' => 'qa', 'title' => 'QA Engineer', 'emoji' => '🧪', 'model' => 'qwen3-coder-next:cloud'],
  ['name' => 'chen', 'strategy' => 'deploy', 'title' => 'DevOps Engineer', 'emoji' => '⚙️', 'model' => 'qwen3-coder-next:cloud'],
];

foreach(\$agents as \$data) {
  // Agents table
  Agent::firstOrCreate(
    ['name' => \$data['name']],
    [
      'role' => 'worker',
      'type' => 'worker',
      'emoji' => \$data['emoji'],
      'strategy_class' => \$data['strategy'],
      'step_filter' => \$data['strategy'],
      'model' => \$data['model'],
      'provider' => 'ollama',
      'status' => 'offline',
      'system_prompt' => \"You are {\$data['name']}, {\$data['title']} AI.\",
      'runtime_location' => 'php',
    ]
  );
  
  // Team members table
  TeamMember::firstOrCreate(
    ['name' => \$data['name']],
    [
      'id' => (string) Str::uuid(),
      'name' => \$data['name'],
      'type' => 'workers',
      'role' => 'worker',
      'category' => 'worker',
      'emoji' => \$data['emoji'],
      'title' => \$data['title'],
      'model' => \$data['model'],
      'provider' => 'ollama',
      'ai_model' => \$data['model'],
      'system_prompt' => \"You are {\$data['name']}, {\$data['title']} AI.\",
      'status' => 'offline',
    ]
  );
  
  echo \"✅ {\$data['name']} restored\" . PHP_EOL;
}

echo PHP_EOL . 'Final counts:' . PHP_EOL;
echo '  Agents: ' . Agent::count() . PHP_EOL;
echo '  Team Members: ' . TeamMember::count() . PHP_EOL;
"

echo "✅ Done!"