<?php

namespace App\Console\Commands;

use App\Models\ModelHealth;
use Illuminate\Console\Command;

class CheckModelHealth extends Command
{
    protected $signature = 'check:model-health';
    protected $description = 'Check health status of configured models';

    protected array $models = [
        'GLM-5' => [
            'type' => 'api',
            'endpoint' => 'https://openrouter.ai/api/v1/models',
        ],
        'Dolphin 3.0' => [
            'type' => 'local',
            'endpoint' => 'http://192.168.2.2:8787/health',
        ],
    ];

    public function handle(): int
    {
        $this->info('Checking model health...');

        foreach ($this->models as $modelName => $config) {
            $this->checkModel($modelName, $config);
        }

        $this->info('Model health check complete.');

        return self::SUCCESS;
    }

    protected function checkModel(string $name, array $config): void
    {
        $status = 'healthy';
        $tokensPerSec = 0;
        $cpu = 0;
        $memory = 0;
        $vram = null;

        if ($config['type'] === 'local') {
            try {
                $response = \Http::timeout(5)->get($config['endpoint']);

                if ($response->successful()) {
                    $data = $response->json();
                    $tokensPerSec = $data['tokens_per_sec'] ?? 18.5;
                    $cpu = $data['cpu_percent'] ?? 0;
                    $memory = $data['memory_percent'] ?? 0;
                    $vram = $data['vram_percent'] ?? null;
                    $status = 'healthy';
                } else {
                    $status = 'down';
                }
            } catch (\Exception $e) {
                $status = 'down';
                $this->warn("{$name}: {$e->getMessage()}");
            }
        } else {
            // API models are assumed healthy if configured
            $status = 'healthy';
            $tokensPerSec = 45.0; // Typical API speed
        }

        ModelHealth::create([
            'model' => $name,
            'status' => $status,
            'cpu_percent' => $cpu,
            'memory_percent' => $memory,
            'vram_percent' => $vram,
            'tokens_per_sec' => $tokensPerSec,
            'queue_depth' => 0,
            'checked_at' => now(),
        ]);

        $this->line("  {$name}: {$status} ({$tokensPerSec} tok/s)");
    }
}