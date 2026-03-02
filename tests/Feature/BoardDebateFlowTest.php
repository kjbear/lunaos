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

class BoardDebateFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_debate_cycle_with_five_personas(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => 'Board member response']]
            ]], 200),
        ]);

        // Create 5 distinct board members
        $personas = [
            ['name' => 'Steven', 'title' => 'CEO'],
            ['name' => 'Gwynne', 'title' => 'COO'],
            ['name' => 'Werner', 'title' => 'CTO'],
            ['name' => 'Warren', 'title' => 'CFO'],
            ['name' => 'Bozoma', 'title' => 'CMO'],
        ];

        foreach ($personas as $persona) {
            Persona::factory()->create([
                'role' => 'board_member',
                'status' => 'active',
                'name' => $persona['name'],
                'title' => $persona['title'],
            ]);
        }

        $session = BoardSession::factory()->create([
            'question' => 'Should we prioritize feature X or Y?',
        ]);

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question);

        $session->refresh();

        // Verify all 5 personas responded
        $this->assertEquals(5, $session->responses()->count());

        // Verify each persona has a response
        $responseNames = $session->responses->pluck('member_name')->toArray();
        foreach ($personas as $persona) {
            $this->assertContains($persona['name'], $responseNames);
        }
    }

    public function test_debate_responses_are_ordered(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => 'Response']]
            ]], 200),
        ]);

        Persona::factory()->count(5)->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create();

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question);

        $responses = $session->responses()->orderBy('response_order')->get();

        // Verify order starts at 0 and increments
        foreach ($responses as $index => $response) {
            $this->assertEquals($index, $response->response_order);
        }
    }

    public function test_transcript_captures_full_debate(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        $callCount = 0;
        Http::fake(function ($request) use (&$callCount) {
            $callCount++;
            return Http::response([
                'choices' => [['message' => ['content' => "Response #{$callCount} from board member"]]
            ]], 200);
        });

        Persona::factory()->count(5)->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create([
            'question' => 'Critical business decision',
            'context' => 'Important context for the decision',
        ]);

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question, $session->context);

        // Verify transcript has all responses
        $responses = BoardResponse::where('session_id', $session->id)->get();
        
        $this->assertEquals(5, $responses->count());
        
        // Verify each response has content
        $responses->each(function ($response) {
            $this->assertNotEmpty($response->response);
            $this->assertStringContainsString('Response', $response->response);
        });
    }

    public function test_debate_with_typical_business_question(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => 'Strategic perspective on the business question']]
            ]], 200),
        ]);

        Persona::factory()->count(5)->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create([
            'question' => 'Should we prioritize feature X or Y for Q2 roadmap?',
            'context' => 'Feature X has higher user demand but Feature Y is strategically important.',
        ]);

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question, $session->context);

        $session->refresh();

        $this->assertEquals('decided', $session->status);
        $this->assertEquals(5, $session->responses()->count());
        $this->assertNotNull($session->final_decision);
    }

    public function test_each_persona_provides_unique_perspective(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => 'Perspective']]
            ]], 200),
        ]);

        $personas = [
            ['name' => 'Steven', 'title' => 'CEO'],
            ['name' => 'Gwynne', 'title' => 'COO'],
            ['name' => 'Werner', 'title' => 'CTO'],
            ['name' => 'Warren', 'title' => 'CFO'],
            ['name' => 'Bozoma', 'title' => 'CMO'],
        ];

        foreach ($personas as $persona) {
            Persona::factory()->create([
                'role' => 'board_member',
                'status' => 'active',
                'name' => $persona['name'],
                'title' => $persona['title'],
            ]);
        }

        $session = BoardSession::factory()->create([
            'question' => 'Market expansion strategy',
        ]);

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question);

        $responses = $session->responses;

        // Verify each response has the correct role
        $roleResponseMap = $responses->keyBy('member_role');
        
        $this->assertArrayHasKey('CEO', $roleResponseMap);
        $this->assertArrayHasKey('COO', $roleResponseMap);
        $this->assertArrayHasKey('CTO', $roleResponseMap);
        $this->assertArrayHasKey('CFO', $roleResponseMap);
        $this->assertArrayHasKey('CMO', $roleResponseMap);
    }

    public function test_debate_handles_agent_timeout(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response(null, 408),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create();

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question);

        // Should handle gracefully
        $session->refresh();
        $this->assertEquals('decided', $session->status);
    }

    public function test_debate_handles_failed_responses(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([], 500),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create();

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question);

        $session->refresh();
        
        // Session should still complete
        $this->assertEquals('decided', $session->status);
    }

    public function test_debate_synthesizes_final_decision(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => 'RECOMMENDATION: Proceed with caution.\n\nRISKS:\n- Market risk\n\nBENEFITS:\n- Growth potential']]
            ]], 200),
        ]);

        Persona::factory()->count(5)->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $session = BoardSession::factory()->create([
            'question' => 'Strategic decision',
        ]);

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question);

        $session->refresh();

        $this->assertNotNull($session->final_decision);
        $this->assertStringContainsString('Proceed', $session->final_decision);
        $this->assertNotNull($session->risks_benefits);
    }

    public function test_response_includes_model_information(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => 'AI response']]
            ]], 200),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
            'model' => 'glm-5',
        ]);

        $session = BoardSession::factory()->create();

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question);

        $response = $session->responses->first();
        
        $this->assertEquals('glm-5', $response->model_used);
    }

    public function test_debate_preserves_member_roles(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => 'Response']]
            ]], 200),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
            'name' => 'Test CEO',
            'title' => 'Chief Executive Officer',
        ]);

        $session = BoardSession::factory()->create();

        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question);

        $response = $session->responses->first();
        
        $this->assertEquals('Test CEO', $response->member_name);
        $this->assertEquals('Chief Executive Officer', $response->member_role);
    }
}
