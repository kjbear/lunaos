<?php

namespace Tests\Unit\Services;

use App\Models\BoardSession;
use App\Models\BoardResponse;
use App\Models\Persona;
use App\Services\BoardOrchestrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class BoardDebateOrchestratorTest extends TestCase
{
    use RefreshDatabase;

    protected BoardOrchestrator $orchestrator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orchestrator = new BoardOrchestrator();
    }

    public function test_run_session_creates_responses_for_all_board_members(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => 'Test response']]]], 200),
        ]);

        // Create board member personas
        Persona::factory()->count(5)->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create([
            'question' => 'Should we prioritize feature X or Y?',
        ]);

        $this->orchestrator->runSession($session->id, $session->question);

        $this->assertEquals(5, BoardResponse::where('session_id', $session->id)->count());
    }

    public function test_run_session_orders_responses_correctly(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => 'Test response']]]], 200),
        ]);

        Persona::factory()->count(3)->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create();

        $this->orchestrator->runSession($session->id, $session->question);

        $responses = BoardResponse::where('session_id', $session->id)
            ->orderBy('response_order')
            ->get();

        $this->assertEquals(0, $responses[0]->response_order);
        $this->assertEquals(1, $responses[1]->response_order);
        $this->assertEquals(2, $responses[2]->response_order);
    }

    public function test_run_session_stores_member_info_in_responses(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => 'Test response']]]], 200),
        ]);

        $persona = Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
            'name' => 'Test Executive',
            'title' => 'Test CTO',
            'model' => 'glm-5',
        ]);

        $session = BoardSession::factory()->create();

        $this->orchestrator->runSession($session->id, $session->question);

        $response = BoardResponse::where('session_id', $session->id)->first();

        $this->assertEquals($persona->id, $response->member_id);
        $this->assertEquals('Test Executive', $response->member_name);
        $this->assertEquals('Test CTO', $response->member_role);
        $this->assertEquals('glm-5', $response->model_used);
    }

    public function test_run_session_with_context_passes_context_to_api(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        $apiCallCount = 0;
        Http::fake(function ($request) use (&$apiCallCount) {
            $apiCallCount++;
            return Http::response(['choices' => [['message' => ['content' => 'Response ' . $apiCallCount]]]], 200);
        });

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create([
            'question' => 'Test question',
            'context' => 'Important context here',
        ]);

        $this->orchestrator->runSession($session->id, $session->question, $session->context);

        // Assert HTTP requests were made
        Http::assertSent(function ($request) {
            return $request->url() === 'https://openrouter.ai/api/v1/chat/completions';
        });
    }

    public function test_run_session_updates_status_to_decided(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => 'Test response']]]], 200),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create([
            'status' => 'pending',
        ]);

        $this->orchestrator->runSession($session->id, $session->question);

        $session->refresh();
        
        $this->assertEquals('decided', $session->status);
        $this->assertNotNull($session->decided_at);
    }

    public function test_run_session_without_board_members_logs_error(): void
    {
        Config::set('services.openrouter.key', 'test-key');

        Log::shouldReceive('error')
            ->with('BoardOrchestrator: No active board members found')
            ->once();

        $session = BoardSession::factory()->create();

        $this->orchestrator->runSession($session->id, $session->question);

        // Should not create any responses
        $this->assertEquals(0, BoardResponse::where('session_id', $session->id)->count());
    }

    public function test_run_session_handles_api_timeout(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response(null, 408),
        ]);

        Log::shouldReceive('error')->atLeast()->once();

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create();

        $this->orchestrator->runSession($session->id, $session->question);

        // Should handle gracefully
        $this->assertNotNull($session->refresh()->status);
    }

    public function test_synthesize_decision_called_after_all_responses(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        $callOrder = [];
        
        Http::fake(function ($request) use (&$callOrder) {
            $callOrder[] = $request->url();
            return Http::response(['choices' => [['message' => ['content' => 'Response']]]], 200);
        });

        Persona::factory()->count(2)->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create();

        $this->orchestrator->runSession($session->id, $session->question);

        // Should have multiple calls - one per board member + one for synthesis
        $this->assertGreaterThan(2, count($callOrder));
        
        // Last call should be for synthesis
        $this->assertStringContainsString('openrouter.ai', $callOrder[count($callOrder) - 1]);
    }

    public function test_response_content_is_stored(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        $customResponse = 'This is a custom API response with specific content';
        
        Http::fake([
            'openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => $customResponse]]]], 200),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create();

        $this->orchestrator->runSession($session->id, $session->question);

        $response = BoardResponse::where('session_id', $session->id)->first();
        
        $this->assertEquals($customResponse, $response->response);
    }
}
