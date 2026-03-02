<?php

namespace Tests\Unit\Services;

use App\Models\BoardSession;
use App\Services\BoardOrchestrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BoardDecisionConsolidatorTest extends TestCase
{
    use RefreshDatabase;

    protected BoardOrchestrator $orchestrator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orchestrator = new BoardOrchestrator();
    }

    public function test_decision_contains_recommendation(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => "RECOMMENDATION: We should proceed with the launch.\n\nRISKS:\n- Market competition\n\nBENEFITS:\n- Revenue growth"
                    ]
                ]]
            ], 200),
        ]);

        $session = BoardSession::factory()->create([
            'question' => 'Should we launch the product?',
        ]);

        // Create a mock persona for the board member
        \App\Models\Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $this->orchestrator->runSession($session->id, $session->question);

        $session->refresh();
        
        $this->assertNotNull($session->final_decision);
        $this->assertStringContainsString('proceed', strtolower($session->final_decision));
    }

    public function test_decision_includes_risks_and_benefits(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => "RECOMMENDATION: Yes.\n\nRISKS:\n- Technical debt\n- Resource constraints\n\nBENEFITS:\n- Market share\n- Customer satisfaction"
                    ]
                ]]
            ], 200),
        ]);

        \App\Models\Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create();

        $this->orchestrator->runSession($session->id, $session->question);

        $session->refresh();
        
        $this->assertNotNull($session->risks_benefits);
        $this->assertStringContainsString('Technical debt', $session->risks_benefits);
        $this->assertStringContainsString('Market share', $session->risks_benefits);
    }

    public function test_decision_has_reasoning(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        $detailedResponse = "RECOMMENDATION: We should prioritize feature X over Y because it aligns better with our strategic goals and has higher ROI potential.\n\nRISKS:\n- Short-term revenue impact\n\nBENEFITS:\n- Long-term growth";

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => $detailedResponse]]]
            ], 200),
        ]);

        \App\Models\Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create([
            'question' => 'Should we prioritize feature X or Y?',
        ]);

        $this->orchestrator->runSession($session->id, $session->question);

        $session->refresh();
        
        $this->assertStringContainsString('prioritize', strtolower($session->final_decision));
        $this->assertStringContainsString('ROI', $session->final_decision);
    }

    public function test_decision_includes_confidence_indicators(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        $response = "RECOMMENDATION: Strong recommendation to proceed. High confidence in success.\n\nRISKS:\n- Minor risks\n\nBENEFITS:\n- Major benefits";

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => $response]]]
            ], 200),
        ]);

        \App\Models\Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create();

        $this->orchestrator->runSession($session->id, $session->question);

        $session->refresh();
        
        $this->assertStringContainsString('confidence', strtolower($session->final_decision));
    }

    public function test_decision_handles_api_failure_gracefully(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([], 500),
        ]);

        \App\Models\Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create();

        $this->orchestrator->runSession($session->id, $session->question);

        $session->refresh();
        
        // Should have a fallback decision message
        $this->assertNotNull($session->final_decision);
    }

    public function test_decision_format_is_structured(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        $structuredResponse = "RECOMMENDATION: Launch the feature.\n\nRISKS:\n- Risk 1\n- Risk 2\n\nBENEFITS:\n- Benefit 1\n- Benefit 2";

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => $structuredResponse]]]
            ], 200),
        ]);

        \App\Models\Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create();

        $this->orchestrator->runSession($session->id, $session->question);

        $session->refresh();
        
        // Verify the decision maintains structure
        $this->assertNotEmpty($session->final_decision);
        $this->assertNotEmpty($session->risks_benefits);
    }

    public function test_decision_based_on_multiple_responses(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        $callCount = 0;
        Http::fake(function ($request) use (&$callCount) {
            $callCount++;
            return Http::response([
                'choices' => [['message' => ['content' => 'Response ' . $callCount]]]
            ], 200);
        });

        // Create multiple board members
        \App\Models\Persona::factory()->count(3)->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create([
            'question' => 'Strategic decision question',
        ]);

        $this->orchestrator->runSession($session->id, $session->question);

        $session->refresh();
        
        // Decision should be based on all responses
        $this->assertEquals('decided', $session->status);
        $this->assertNotNull($session->final_decision);
        
        // Should have called API for each member + synthesis
        $this->assertGreaterThanOrEqual(4, $callCount);
    }

    public function test_decision_timestamp_is_set(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => 'Decision made']]
            ]], 200),
        ]);

        \App\Models\Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create();

        $beforeDecision = now();
        
        $this->orchestrator->runSession($session->id, $session->question);
        
        $session->refresh();

        $this->assertNotNull($session->decided_at);
        $this->assertTrue($session->decided_at->gte($beforeDecision));
    }

    public function test_empty_decision_response_is_handled(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => '']]
            ]], 200),
        ]);

        \App\Models\Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create();

        $this->orchestrator->runSession($session->id, $session->question);

        $session->refresh();
        
        // Should handle empty response gracefully
        $this->assertEquals('decided', $session->status);
    }
}
