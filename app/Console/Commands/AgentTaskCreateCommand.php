<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Agent Task Create Command
 * 
 * Create a test task for agent workers to execute.
 * 
 * Usage:
 *   php artisan agent:task "Add hello world route"
 *   php artisan agent:task "Create migration for orders table" --assign=dave
 *   php artisan agent:task "Run tests for feature X" --assign=sam --step=qa
 *   php artisan agent:task --test  # Create a predefined test task
 */
class AgentTaskCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'agent:task {description? : Task description}
                            {--assign=dave : Agent to assign (dave, sam, chen)}
                            {--step=develop : Workflow step (develop, qa, staging, production)}
                            {--priority=medium : Priority (low, medium, high, critical)}
                            {--test : Create a predefined test task}
                            {--list : List recent tasks}';
    
    /**
     * The console command description.
     */
    protected $description = 'Create a test task for agent workers';
    
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('list')) {
            return $this->listTasks();
        }
        
        if ($this->option('test')) {
            return $this->createTestTask();
        }
        
        $description = $this->argument('description');
        
        if (!$description) {
            $description = $this->ask('Enter task description');
        }
        
        return $this->createTask($description);
    }
    
    /**
     * Create a task
     */
    protected function createTask(string $description): int
    {
        $assign = $this->option('assign');
        $step = $this->option('step');
        $priority = $this->option('priority');
        
        $task = Task::create([
            'title' => $description,
            'description' => $this->buildDetailedDescription($description, $step),
            'assigned_to' => $assign,
            'step' => $step,
            'status' => 'pending',
            'priority' => $priority,
            'task_type' => $this->determineTaskType($step),
        ]);
        
        $this->info("✅ Task #{$task->id} created successfully!");
        $this->line('');
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $task->id],
                ['Title', $task->title],
                ['Assigned To', $task->assigned_to],
                ['Step', $task->step],
                ['Status', $task->status],
                ['Priority', $task->priority],
            ]
        );
        
        $this->line('');
        $this->line("Run: php artisan agent:run {$assign} --once");
        
        return 0;
    }
    
    /**
     * Create a predefined test task
     */
    protected function createTestTask(): int
    {
        $testTasks = [
            'migration' => [
                'title' => 'Create test migration for products table',
                'description' => <<<'DESC'
Create a database migration for a `products` table with the following columns:

- id (bigIncrements)
- name (string, not null)
- description (text, nullable)
- price (decimal 10,2, not null)
- sku (string, unique)
- is_active (boolean, default true)
- timestamps

Include proper indexes and foreign key constraints to a future `categories` table.

Return the migration file in database/migrations/.
DESC,
                'step' => 'develop',
                'assign' => 'dave',
            ],
            'route' => [
                'title' => 'Add hello world test route',
                'description' => <<<'DESC'
Add a simple test route to routes/web.php:

GET /hello-test -> Returns "Hello from Dave! This is a test."

Include a controller method and proper docblocks.
DESC,
                'step' => 'develop',
                'assign' => 'dave',
            ],
            'model' => [
                'title' => 'Create Product model',
                'description' => <<<'DESC'
Create a Product model in app/Models/Product.php with:

- Fillable attributes: name, description, price, sku, is_active
- Casts: is_active => boolean, price => decimal:2
- Relationship: belongsTo(Category::class)
- Scope: active() for is_active = true
- Proper docblocks and strict types
DESC,
                'step' => 'develop',
                'assign' => 'dave',
            ],
        ];
        
        $choice = $this->choice('Which test task?', array_keys($testTasks), 0);
        $testTask = $testTasks[$choice];
        
        $task = Task::create([
            'title' => $testTask['title'],
            'description' => $testTask['description'],
            'assigned_to' => $testTask['assign'],
            'step' => $testTask['step'],
            'status' => 'pending',
            'priority' => 'medium',
            'task_type' => 'feature',
        ]);
        
        $this->info("✅ Test task #{$task->id} created!");
        $this->line('');
        $this->line("Task: {$task->title}");
        $this->line('');
        $this->line("Run: php artisan agent:run {$task->assigned_to} --once");
        
        return 0;
    }
    
    /**
     * List recent tasks
     */
    protected function listTasks(): int
    {
        $tasks = Task::orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'title', 'assigned_to', 'step', 'status', 'priority', 'created_at']);
        
        if ($tasks->isEmpty()) {
            $this->info('No tasks found.');
            return 0;
        }
        
        $this->info('Recent Tasks:');
        $this->line('');
        
        $rows = $tasks->map(fn ($t) => [
            $t->id,
            Str::limit($t->title, 40),
            $t->assigned_to,
            $t->step,
            $t->status,
            $t->priority,
            $t->created_at->diffForHumans(),
        ])->toArray();
        
        $this->table(
            ['ID', 'Title', 'Assigned', 'Step', 'Status', 'Priority', 'Created'],
            $rows
        );
        
        return 0;
    }
    
    /**
     * Build detailed description based on step
     */
    protected function buildDetailedDescription(string $description, string $step): string
    {
        $prefix = match ($step) {
            'develop' => "Development Task:\n\n",
            'qa' => "QA Task:\n\n",
            'staging' => "Deployment Task:\n\n",
            'production' => "Production Deployment:\n\n",
            default => '',
        };
        
        return $prefix . $description;
    }
    
    /**
     * Determine task type based on step
     */
    protected function determineTaskType(string $step): string
    {
        return match ($step) {
            'qa' => 'test',
            'staging', 'production' => 'deployment',
            default => 'feature',
        };
    }
}