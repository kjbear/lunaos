<?php

namespace App\Jobs;

use App\Models\BoardSession;
use App\Services\BoardOrchestrator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Process Board Debate - Queue Job
 * 
 * Processes the executive board debate asynchronously.
 * Runs in background to prevent HTTP timeout.
 */
class ProcessBoardDebate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $sessionId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $session = BoardSession::find($this->sessionId);
        
        if (!$session) {
            Log::error('ProcessBoardDebate: Session not found', ['session_id' => $this->sessionId]);
            return;
        }

        Log::info('ProcessBoardDebate: Starting', [
            'session_id' => $this->sessionId,
            'question' => substr($session->question, 0, 100),
        ]);

        $session->update(['status' => 'debating']);

        try {
            $orchestrator = app(BoardOrchestrator::class);
            $orchestrator->runSession($session->id, $session->question, $session->context ?: null);

            Log::info('ProcessBoardDebate: Complete', [
                'session_id' => $this->sessionId,
                'status' => $session->fresh()->status,
            ]);

        } catch (\Exception $e) {
            Log::error('ProcessBoardDebate: Failed', [
                'session_id' => $this->sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $session->update([
                'status' => 'failed',
                'final_decision' => 'Processing failed: ' . $e->getMessage(),
            ]);

            // Re-throw to mark job as failed
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessBoardDebate: Job failed', [
            'session_id' => $this->sessionId,
            'error' => $exception->getMessage(),
        ]);
    }
}
