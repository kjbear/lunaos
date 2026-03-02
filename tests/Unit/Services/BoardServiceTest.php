<?php

namespace Tests\Unit\Services;

use App\Models\BoardSession;
use App\Models\BoardResponse;
use App\Services\BoardOrchestrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class BoardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BoardOrchestrator $orchestrator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orchestrator = new BoardOrchestrator();
    }

    public function test_it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(BoardOrchestrator::class, $this->orchestrator);
    }

    public function test_is_configured_returns_false_without_api_key(): void
    {
        Config::set('services.openrouter.key', null);
        
        $orchestrator = new BoardOrchestrator();
        
        $this->assertFalse($orchestrator->isConfigured());
    }

    public function test_is_configured_returns_true_with_api_key(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        $orchestrator = new BoardOrchestrator();
        
        $this->assertTrue($orchestrator->isConfigured());
    }

    public function test_model_map_contains_expected_models(): void
    {
        $reflection = new \ReflectionClass($this->orchestrator);
        $property = $reflection->getProperty('modelMap');
        $property->setAccessible(true);
        $modelMap = $property->getValue($this->orchestrator);

        $this->assertArrayHasKey('glm-5', $modelMap);
        $this->assertArrayHasKey('haiku', $modelMap);
        $this->assertArrayHasKey('dolphin', $modelMap);
        $this->assertEquals('z-ai/glm-5', $modelMap['glm-5']);
    }

    public function test_run_session_creates_board_session_with_correct_status(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => 'Test response']]]], 200),
        ]);

        $session = BoardSession::factory()->create([
            'status' => 'pending',
            'question' => 'Test question?',
        ]);

        $this->orchestrator->runSession($session->id, $session->question);

        $session->refresh();
        
        $this->assertEquals('decided', $session->status);
        $this->assertNotNull($session->final_decision);
        $this->assertNotNull($session->decided_at);
    }

    public function test_run_session_handles_api_failure_gracefully(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([], 500),
        ]);

        $session = BoardSession::factory()->create([
            'status' => 'pending',
            'question' => 'Test question?',
        ]);

        Log::shouldReceive('error')->atLeast()->once();

        $this->orchestrator->runSession($session->id, $session->question);

        $session->refresh();
        
        // Session should be marked as decided even on failure
        $this->assertEquals('decided', $session->status);
    }

    public function test_build_system_prompt_contains_member_info(): void
    {
        $reflection = new \ReflectionClass($this->orchestrator);
        $method = $reflection->getMethod('buildSystemPrompt');
        $method->setAccessible(true);

        $member = (object) [
            'name' => 'Test Member',
            'title' => 'Test Title',
            'inspiration' => 'Test Inspiration',
        ];

        $prompt = $method->invoke($this->orchestrator, $member);

        $this->assertStringContainsString('Test Member', $prompt);
        $this->assertStringContainsString('Test Title', $prompt);
        $this->assertStringContainsString('Test Inspiration', $prompt);
    }

    public function test_build_user_prompt_contains_question_and_context(): void
    {
        $reflection = new \ReflectionClass($this->orchestrator);
        $method = $reflection->getMethod('buildUserPrompt');
        $method->setAccessible(true);

        $member = (object) ['title' => 'CEO'];
        $question = 'What should we do?';
        $context = 'Some context information';

        $prompt = $method->invoke($this->orchestrator, $member, $question, $context);

        $this->assertStringContainsString($question, $prompt);
        $this->assertStringContainsString($context, $prompt);
        $this->assertStringContainsString('BOARD MEETING', $prompt);
    }

    public function test_build_user_prompt_handles_null_context(): void
    {
        $reflection = new \ReflectionClass($this->orchestrator);
        $method = $reflection->getMethod('buildUserPrompt');
        $method->setAccessible(true);

        $member = (object) ['title' => 'CEO'];
        $question = 'What should we do?';

        $prompt = $method->invoke($this->orchestrator, $member, $question, null);

        $this->assertStringContainsString($question, $prompt);
        $this->assertStringNotContainsString('Context:', $prompt);
    }

    public function test_parse_decision_extracts_recommendation(): void
    {
        $reflection = new \ReflectionClass($this->orchestrator);
        $method = $reflection->getMethod('parseDecision');
        $method->setAccessible(true);

        $content = "RECOMMENDATION: We should proceed with caution.\n\nRISKS:\n- Market risk\n\nBENEFITS:\n- Revenue growth";

        $result = $method->invoke($this->orchestrator, $content);

        $this->assertArrayHasKey('recommendation', $result);
        $this->assertStringContainsString('proceed with caution', $result['recommendation']);
    }

    public function test_parse_decision_extracts_risks_and_benefits(): void
    {
        $reflection = new \ReflectionClass($this->orchestrator);
        $method = $reflection->getMethod('parseDecision');
        $method->setAccessible(true);

        $content = "RECOMMENDATION: Do it.\n\nRISKS:\n- Risk 1\n- Risk 2\n\nBENEFITS:\n- Benefit 1";

        $result = $method->invoke($this->orchestrator, $content);

        $this->assertArrayHasKey('risks_benefits', $result);
        $this->assertStringContainsString('Risk 1', $result['risks_benefits']);
        $this->assertStringContainsString('Benefit 1', $result['risks_benefits']);
    }

    public function test_parse_decision_handles_malformed_content(): void
    {
        $reflection = new \ReflectionClass($this->orchestrator);
        $method = $reflection->getMethod('parseDecision');
        $method->setAccessible(true);

        $content = "Just some random text without proper formatting";

        $result = $method->invoke($this->orchestrator, $content);

        $this->assertArrayHasKey('recommendation', $result);
        $this->assertNotEmpty($result['recommendation']);
    }
}
