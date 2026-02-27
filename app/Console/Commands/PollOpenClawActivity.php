<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ActivityLog;
use App\Services\ToolCallParserService;
use Illuminate\Support\Facades\DB;

class PollOpenClawActivity extends Command
{
    protected $signature = 'lunaos:poll-openclaw';
    protected $description = 'Poll OpenClaw for recent session activity and tool calls';

    public function handle(): int
    {
        $this->info('Parsing tool calls from OpenClaw sessions...');

        try {
            // Use the tool call parser for granular activity
            $parser = new ToolCallParserService();
            $ingested = $parser->parseToolCalls(30);

            $this->info("Ingested {$ingested} new tool calls");
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error parsing OpenClaw sessions: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}