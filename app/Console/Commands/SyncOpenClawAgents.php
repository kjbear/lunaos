<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Services\OpenClawService;
use Illuminate\Console\Command;

class SyncOpenClawAgents extends Command
{
    protected $signature = 'sync:openclaw-agents';
    protected $description = 'Sync agents from OpenClaw to LunaOS database';

    protected OpenClawService $openClawService;

    public function __construct(OpenClawService $openClawService)
    {
        parent::__construct();
        $this->openClawService = $openClawService;
    }

    public function handle(): int
    {
        $this->info('Syncing agents from OpenClaw...');

        $agents = $this->openClawService->getAgents();

        if (empty($agents)) {
            $this->warn('No agents found or OpenClaw is not reachable.');

            return self::SUCCESS;
        }

        $synced = 0;
        foreach ($agents as $agentData) {
            $agent = Agent::updateOrCreate(
                ['name' => $agentData['name'] ?? $agentData['id']],
                [
                    'role' => $agentData['role'] ?? 'worker',
                    'model' => $agentData['model'] ?? null,
                    'status' => $agentData['status'] ?? 'offline',
                    'parent_id' => $agentData['parent_id'] ?? null,
                ]
            );

            if ($agent->wasRecentlyCreated || $agent->wasChanged()) {
                $synced++;
            }
        }

        $this->info("Synced {$synced} agents.");

        return self::SUCCESS;
    }
}