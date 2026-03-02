<?php

namespace Tests\Feature;

use App\Models\BoardSession;
use App\Models\BoardResponse;
use App\Models\Persona;
use App\Services\BoardOrchestrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BoardDecisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_decision_is_generated_after_debate(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => 'RECOMMENDATION: We should proceed.\n\nRISKS:\n- Risk 1\n\nBENEFITS:\n- Benefit 1']]
            ]], 200),
        ]);

        Persona::factory()->count(5)->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create([
            'question' => 'Should we launch the product?',
        ]);

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question);

        $session->refresh();

        $this->assertNotNull($session->final_decision);
        $this->assertNotEmpty($session->final_decision);
    }

    public function test_decision_includes_reasoning(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        $response = "RECOMMENDATION: Launch the product now because market conditions are favorable and we have first-mover advantage.\n\nRISKS:\n- Competition\n\nBENEFITS:\n- Market share";

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => $response]]
            ]], 200),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create();

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question);

        $session->refresh();

        $this->assertStringContainsString('launch', strtolower($session->final_decision));
        $this->assertStringContainsString('market', strtolower($session->final_decision));
    }

    public function test_decision_includes_confidence_level(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        $response = "RECOMMENDATION: Strong recommendation to proceed. High confidence in success based on data.\n\nRISKS:\n- Minor\n\nBENEFITS:\n- Major";

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => $response]]
            ]], 200),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create();

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question);

        $session->refresh();

        $this->assertStringContainsString('confidence', strtolower($session->final_decision));
    }

    public function test_decision_has_risks_and_benefits_section(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        $response = "RECOMMENDATION: Yes.\n\nRISKS:\n- Technical challenges\n- Budget overruns\n\nBENEFITS:\n- Revenue growth\n- Customer satisfaction";

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => $response]]
            ]], 200),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create();

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question);

        $session->refresh();

        $this->assertNotNull($session->risks_benefits);
        $this->assertStringContainsString('Technical challenges', $session->risks_benefits);
        $this->assertStringContainsString('Revenue growth', $session->risks_benefits);
    }

    public function test_decision_output_format_is_structured(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        $structuredResponse = "RECOMMENDATION: Proceed with Feature X.\n\nRISKS:\n- Development time\n- Resource allocation\n\nBENEFITS:\n- User demand\n- Competitive advantage";

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => $structuredResponse]]
            ]], 200),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create([
            'question' => 'Feature prioritization',
        ]);

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question);

        $session->refresh();

        $this->assertStringContainsString('Feature X', $session->final_decision);
        $this->assertNotNull($session->risks_benefits);
        $this->assertStringContainsString('Development time', $session->risks_benefits);
    }

    public function test_decision_is_actionable(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        $response = "RECOMMENDATION: Invest $500K in marketing for Q2 launch with focus on digital channels.\n\nRISKS:\n- ROI uncertainty\n\nBENEFITS:\n- Brand awareness";

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => $response]]
            ]], 200),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create();

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question);

        $session->refresh();

        $this->assertStringContainsString('$500K', $session->final_decision);
        $this->assertStringContainsString('Q2', $session->final_decision);
    }

    public function test_decision_timestamp_is_recorded(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => 'RECOMMENDATION: Yes']]
            ]], 200),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create();

        $beforeDecision = now();

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question);

        $session->refresh();

        $this->assertNotNull($session->decided_at);
        $this->assertTrue($session->decided_at->gte($beforeDecision));
    }

    public function test_decision_status_updates_to_decided(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => 'Decision']]
            ]], 200),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->pending()->create();

        $this->assertEquals('pending', $session->status);

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question);

        $session->refresh();

        $this->assertEquals('decided', $session->status);
    }

    public function test_decision_handles_no_api_key_gracefully(): void
    {
        Config::set('services.openrouter.key', null);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create();

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question);

        $session->refresh();

        // Should still mark as decided with fallback message
        $this->assertEquals('decided', $session->status);
        $this->assertNotNull($session->final_decision);
    }

    public function test_multiple_decisions_can_be_retrieved(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => 'RECOMMENDATION: Decision']]
            ]], 200),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        BoardSession::factory()->count(3)->create([
            'question' => 'Test question',
        ]);

        $sessions = BoardSession::all();
        
        $sessions->each(function ($session) {
            $orchestrator = new BoardOrchestrator();
            $orchestrator->runSession($session->id, $session->question);
        });

        $decidedSessions = BoardSession::decided()->get();
        
        $this->assertEquals(3, $decidedSessions->count());
        
        $decidedSessions->each(function ($session) {
            $this->assertNotNull($session->final_decision);
        });
    }

    public function test_decision_retains_question_context(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => 'RECOMMENDATION: Yes for this specific question']]
            ]], 200),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create([
            'question' => 'Unique question about product strategy',
            'context' => 'Specific context about our situation',
        ]);

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question, $session->context);

        $session->refresh();

        $this->assertEquals('Unique question about product strategy', $session->question);
        $this->assertNotNull($session->final_decision);
    }
}
